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
  `<iframe sandbox="allow-scripts">` fed via `srcdoc`, so the frame has an
  *opaque origin*. No same-origin DOM access, no cookies, no storage, no
  top-level navigation. Canvas, keyboard, and touch all work — games don't
  notice the cage.
- **Content-Security-Policy** (set on the runtime page, inherited by the
  srcdoc iframe): `connect-src 'none'` blocks fetch/XHR/WebSocket
  exfiltration, no remote scripts can be loaded, images are limited to
  `data:`/`blob:`.
- **Header hygiene** (`docker/apache.conf`): `X-Content-Type-Options`,
  `X-Frame-Options: DENY`, `Referrer-Policy`, `Permissions-Policy`
  (camera/mic/geolocation denied), directory listings off.
- **Session cookie hardening**: the PHP session (used only to bypass
  Varnish caching) is set `HttpOnly` + `SameSite=Lax` on every page that
  starts one (`index.php`, `encode.php`, `about.php`, `examples.php`).
- **API limits** (`src/runtime/api.php`): 64 KB body cap (413 on overflow),
  30 requests/min per IP (429 with `Retry-After`), generic error messages
  (details go to the server log).
- **Self-hosted QR rendering** (`src/runtime/qr.php`): QR images are
  generated in-process by a vendored `chillerlan/php-qrcode` — no third-party
  QR service ever sees the game payload. The endpoint only renders QR3K game
  URLs and refuses colour combinations too low-contrast to scan.

## The remaining step: an isolated, cookie-less origin

The sandbox and CSP contain the *game*, but the runtime page itself still
lives on `www.vincentbruijn.nl`. Serving it from a dedicated origin that
hosts nothing else (and holds no cookies of value) removes the last shared
fate: even a future runtime bug would then compromise only the toy domain.

### Migration guide

1. **Pick an origin** that will never host anything else, e.g.
   `play.vincentbruijn.nl` (or a separate throwaway domain). Point DNS at
   the same server and add a vhost/subdirectory serving `src/runtime/`.

2. **Point the encoders at it.** The generated game URL is a single
   constant, `RUNTIME_URL`, defined in both encoders:

   - `src/encoding/php/Encoder.php`
   - `src/encoding/node/encoder.js`

   Change it to the new origin in both. The QR image endpoint travels with
   the runtime — `qr.php` is served next to `index.php`, and the Node
   encoder derives its QR URL from `RUNTIME_URL` — so repointing
   `RUNTIME_URL` also repoints QR generation. (A worthwhile follow-up is to
   make `RUNTIME_URL` configurable via an environment variable so this
   becomes a deploy-time setting rather than a code edit.)

3. **Keep the encoder UI where it is** (optional). `encode.php` and
   `api.php` can stay on the main site; only the *runtime* (`index.php`,
   `xor.js`, `style.css`, `about.php`, `examples.php`, `qr.php`) needs to
   live on the play origin. `api.php` already sends permissive CORS headers,
   so cross-origin calls from the encoder UI keep working.

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
