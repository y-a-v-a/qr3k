# Deployment Setup

This repository includes a GitHub Actions workflow for deploying QR3K to a remote host via SCP.

## Required GitHub Secrets

You need to configure the following secrets in your GitHub repository settings (`Settings` → `Secrets and variables` → `Actions` → `New repository secret`):

### SSH_PRIVATE_KEY
Your SSH private key for authentication to the remote host.

**Format:** PEM format, starts with `-----BEGIN OPENSSH PRIVATE KEY-----`

**How to generate:**
```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/qr3k_deploy
# Copy the private key content
cat ~/.ssh/qr3k_deploy
# Add the public key to the remote server's ~/.ssh/authorized_keys
cat ~/.ssh/qr3k_deploy.pub
```

### REMOTE_HOST
The hostname or IP address of your remote server.

**Example:** `vincentbruijn.nl` or `192.168.1.100`

### REMOTE_USER
The SSH username for connecting to the remote server.

**Example:** `www-data` or `deploy` or your username

### REMOTE_PATH
The destination path on the remote server where files should be deployed.

**Example:** `/var/www/qr3k/` or `/home/user/public_html/qr3k/`

**Note:** Make sure this path exists and the user has write permissions.

## Deployment Workflow

The workflow deploys the contents of `src/runtime/` to the remote host.

### Trigger Conditions

1. **Automatic:** Pushes to the `main` branch
2. **Manual:** Via GitHub Actions UI (`Actions` tab → `Deploy to Remote Host` → `Run workflow`)

### What Gets Deployed

All files from `src/runtime/`:
- `index.php` - Main runtime loader
- `xor.js` - XOR encoding/decoding utilities
- `encode.php` - Web encoder interface
- `style.css` - Brutalist styling
- `about.php` - About page
- `api.php` - JSON API endpoint
- Any other runtime files

### Testing the Deployment

After setting up secrets:

1. Push to `main` branch or trigger manually
2. Check the Actions tab for workflow status
3. Verify files on remote server:
   ```bash
   ssh user@host "ls -la /path/to/qr3k/"
   ```

## Security Notes

- Never commit SSH private keys to the repository
- Use deploy-specific SSH keys (not your personal keys)
- Restrict the deploy key to only necessary permissions on the server
- Consider using `authorized_keys` options to limit commands:
  ```
  command="internal-sftp",no-port-forwarding,no-X11-forwarding,no-agent-forwarding,no-pty ssh-ed25519 AAAA...
  ```

## Troubleshooting

### Permission Denied
- Verify SSH key is correct in secrets
- Check that public key is in remote server's `~/.ssh/authorized_keys`
- Ensure remote user has write access to destination path

### Host Key Verification Failed
- The workflow automatically adds host keys with `ssh-keyscan`
- If issues persist, verify the hostname is correct

### Files Not Deploying
- Check workflow logs in GitHub Actions tab
- Verify REMOTE_PATH exists on server
- Ensure REMOTE_PATH has proper write permissions
