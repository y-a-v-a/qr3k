---
name: upgrade-qr-vendor
description: Upgrade the vendored chillerlan/php-qrcode library (src/encoding/vendor/, no Composer) to a new release and validate it end-to-end. Use when the user wants to update, upgrade, or bump the QR code library or its settings-container dependency, or asks to re-verify the vendored QR stack.
---

# Upgrade Vendored QR Library

The QR image endpoint (`src/runtime/qr.php`) uses chillerlan/php-qrcode vendored
under `src/encoding/vendor/` — pinned release snapshots wired by a hand-written
PSR-4 autoloader, **no Composer**. Current pins live in `src/encoding/vendor/VERSIONS`.

## Workflow

### 1. Pick the target release

Check https://github.com/chillerlan/php-qrcode/releases for tags. Constraints:

- The runtime container is **PHP 8.2** (`Dockerfile`, `php:8.2-apache`). Check the
  tag's `composer.json` `require.php` — e.g. the 5.0.x line supports `^7.4 || ^8.0`,
  but `main` already requires 8.4. `upgrade.sh` re-checks this and aborts on mismatch.
- Note the required `chillerlan/php-settings-container` range in the same
  `composer.json`; pass a matching settings tag if the current one falls outside it.

### 2. Run the upgrade

```bash
.claude/skills/upgrade-qr-vendor/scripts/upgrade.sh <php-qrcode-tag> [settings-container-tag]
# e.g.
.claude/skills/upgrade-qr-vendor/scripts/upgrade.sh 5.0.4
.claude/skills/upgrade-qr-vendor/scripts/upgrade.sh 5.1.0 3.3.0
```

The script downloads the release tarball(s), replaces `vendor/*/src/`, prunes the
unused QR reader (`Decoder/`, `Detector/`), refreshes LICENSE/NOTICE files, and
regenerates `VENDOR/VERSIONS`. It never touches `autoload.php` — if a new major
changes the namespace layout, update the prefix map there by hand.

### 3. Review the diff for API breaks

`qr.php` depends on these library symbols — grep the diff if the major version changed:

- `QROptions` keys: `eccLevel`, `outputType`, `outputBase64`, `moduleValues`,
  `drawLightModules`, `quietzoneSize`, `scale`
- `QROutputInterface::MARKUP_SVG` / `GDIMAGE_PNG` / `DEFAULT_MODULE_VALUES`
- `QRMatrix::IS_DARK`
- `(new QRCode($options))->render($data)`

### 4. Validate

Dev container must be running (`npm run dev:detached`, port 8081):

```bash
.claude/skills/upgrade-qr-vendor/scripts/verify.sh [base-url]   # default http://localhost:8081
```

This checks, in order:

1. `php -l` on every vendored file inside the container
2. `qr.php` serves SVG with the right content type
3. Security guards still hold (foreign URLs → 400, low-contrast colors → 400)
4. **Round-trip proof**: renders PNGs (default colors, custom colors, and a
   near-limit 2,887-byte URL) and machine-decodes each with jsQR, asserting the
   decoded data is byte-identical to the input
5. `npm test` still passes

All checks must pass before committing. Requires `docker`, `node`/`npm`,
`python3`, `curl`.

### 5. Commit

Commit `src/encoding/vendor/` changes with a message naming old → new versions.
If `qr.php` needed adjustments for the new version, commit those together with it.

## Rollback

The vendor directory is fully tracked in git — `git checkout <last-good-commit> -- src/encoding/vendor/` restores the previous pin.
