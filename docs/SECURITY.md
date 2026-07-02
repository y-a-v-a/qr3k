# QR3K Security Model

QR3K's whole point is "scan a QR code, run a stranger's code." That can't be
made *safe* — it can only be made *contained*. This document describes what
the runtime does to contain it, and the one remaining step that requires an
infrastructure change: serving games from an isolated, cookie-less origin.

## Threat model

A QR3K URL (`?z=` or `?x=`) carries arbitrary HTML/JavaScript that the
runtime decodes and executes. Anyone can mint such a URL — that's the game.
The threats worth caring about are therefore not "can the game run code"
(yes, by design) but:

1. **Origin abuse** — a malicious game reading cookies, local storage, or
   DOM belonging to other things hosted on the same domain, or using the
   trusted domain name for phishing.
2. **Exfiltration** — a game phoning home with whatever it can gather.
3. **Service abuse** — using the public encoder API as a free
   compression/QR service or overloading it.

## What the runtime already does

- **Sandboxed execution** (`src/runtime/index.php`): decoded games run in an
  `<iframe sandbox="allow-scripts">` with an *opaque origin*. No
  same-origin DOM access, no cookies, no storage, no top-level navigation.
  Canvas, keyboard, and touch all work — games don't notice the cage.
- **Content-Security-Policy** (inherited by the srcdoc iframe):
  `connect-src 'none'` blocks fetch/XHR/WebSocket exfiltration, no remote
  scripts can be loaded, images are limited to `data:`/`blob:`.
- **Header hygiene** (`docker/apache.conf`): `X-Content-Type-Options`,
  `X-Frame-Options: DENY`, `Referrer-Policy`, `Permissions-Policy`
  (camera/mic/geolocation denied), directory listings off.
- **Session cookie hardening**: the PHP session (used only to bypass
  Varnish caching) is `HttpOnly` + `SameSite=Lax`.
- **API limits** (`src/runtime/api.php`): 64 KB body cap, 30 requests/min
  per IP, generic error messages (details go to the server log).

## The remaining step: an isolated, cookie-less origin

The sandbox and CSP contain the *game*, but the runtime page itself still
lives on `www.vincentbruijn.nl`. Serving it from a dedicated origin that
hosts nothing else (and holds no cookies of value) removes the last shared
fate: even a future runtime bug would then compromise only the toy domain.

### Migration guide

1. **Pick an origin** that will never host anything else, e.g.
   `play.vincentbruijn.nl` (or a separate throwaway domain). Point DNS at
   the same server and add a vhost/subdirectory serving `src/runtime/`.

2. **Point the encoders at it.** Generated game and QR URLs are
   configurable via environment variables — no code changes needed:

   ```bash
   QR3K_RUNTIME_URL=https://play.vincentbruijn.nl/
   QR3K_QR_IMAGE_URL=https://cdn.vincentbruijn.nl/qr/img.php?q=   # unchanged
   ```

   Set these wherever PHP runs (Apache `SetEnv`, Docker `environment:`,
   or the shell for the Node tools). `docker-compose.yml` passes both
   through already.

3. **Keep the encoder UI where it is** (optional). `encode.php` and
   `api.php` can stay on the main site; only the *runtime* (`index.php`,
   `xor.js`, `style.css`, `about.php`) needs to live on the play origin.
   CORS on `api.php` already allows cross-origin calls.

4. **Don't break old QR codes.** QR codes in the wild point at
   `www.vincentbruijn.nl/qr3k/`. Keep serving the runtime there too, or
   301-redirect `?z=`/`?x=` URLs to the new origin (the payload is in the
   query string, so a redirect preserves it).

5. **Keep the play origin boring.** No login, no analytics cookies, no
   other apps. The origin's entire value should be: it can run a 3 KB game.

## Reporting

Found a hole in the cage? Open an issue or mail the address in
`package.json` — proof-of-concept QR codes welcome, ideally ones that are
also fun to play.
