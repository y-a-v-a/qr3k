# QR3K Docker Guide

This guide covers running QR3K using Docker for both development and production.

## Quick Start

### Development Mode
```bash
# Start development server with live reload
npm run dev

# Access at http://localhost:8080
# - Game loader: http://localhost:8080/
# - Web encoder: http://localhost:8080/encode.php

# Stop the server
npm run stop
```

### Production Mode
```bash
# Build production image
npm run build:prod

# Run production container
docker run -p 8080:80 qr3k:production
```

## Available Scripts

### Development
- `npm run dev` - Start development server (foreground)
- `npm run dev:detached` - Start development server (background)
- `npm run stop` - Stop all containers
- `npm run logs` - View container logs
- `npm run shell` - Access container shell

### Build & Deploy
- `npm run build` - Build development image
- `npm run build:prod` - Build production image
- `npm run deploy` - Export production image as tar.gz
- `npm run clean` - Remove all containers and images

## Container Architecture

### Base Image
- **Base**: PHP 8.2 with Apache
- **Extensions**: Session support enabled
- **Modules**: Rewrite, headers, expires enabled
- **Port**: 80 (mapped to 8080 on host)

### Development vs Production

#### Development Mode
- Uses volume mounting: `./src/runtime:/var/www/html`
- Live file changes reflected immediately
- No file copying during build
- Faster iteration cycle

#### Production Mode  
- Files copied into image during build
- Self-contained image with all files
- Optimized for deployment
- No external dependencies

## Volume Mapping

The development setup maps your local `src/runtime/` directory directly into the container:

```yaml
volumes:
  - ./src/runtime:/var/www/html
```

**Mapped Files:**
- `index.php` → Game loader
- `encode.php` → Web encoder tool
- `xor.js` → Encoding utilities

Any changes to these files are immediately visible in the running container.

## Apache Configuration

### Custom Configuration (`docker/apache.conf`)
- **Security Headers**: XSS protection, content-type sniffing prevention
- **CORS Headers**: Allow cross-origin requests for QR functionality
- **Cache Control**: 
  - Static files (JS/CSS): 1 month cache
  - PHP files: No cache (game content changes frequently)
- **Rewrite Engine**: Enabled for URL rewriting

### Directory Structure in Container
```
/var/www/html/
├── index.php      # Game loader (default page)
├── encode.php     # Web encoder tool  
└── xor.js         # XOR encoding utilities
```

## Environment Variables

The container uses standard Apache environment variables:
- `APACHE_RUN_USER=www-data`
- `APACHE_RUN_GROUP=www-data`

## Port Configuration

- **Container Port**: 80
- **Host Port**: 8080 (configurable in docker-compose.yml)
- **Access**: http://localhost:8080

To use a different port, modify `docker-compose.yml`:
```yaml
ports:
  - "3000:80"  # Access via http://localhost:3000
```

## File Permissions

### Development
Files maintain your local user permissions since they're mounted as volumes.

### Production
Files are owned by `www-data:www-data` with `755` permissions for security.

## Health Checks

The container includes basic health monitoring via Apache access logs:
```bash
# View live logs
npm run logs

# Check container status
docker-compose ps
```

## Troubleshooting

### Common Issues

#### Port Already in Use
```bash
# Check what's using port 8080
lsof -i :8080

# Use different port in docker-compose.yml
ports:
  - "8081:80"
```

#### File Permission Issues
```bash
# Access container shell
npm run shell

# Check file permissions
ls -la /var/www/html/

# Fix permissions if needed (in container)
chown -R www-data:www-data /var/www/html/
```

#### Volume Mount Not Working
```bash
# Verify volume mapping
docker-compose config

# Check if files exist locally
ls -la src/runtime/

# Restart with fresh build
npm run stop && npm run dev
```

#### PHP Session Issues
```bash
# Check PHP configuration in container
npm run shell
php -i | grep session

# Verify session directory exists and is writable
ls -la /tmp/
```

### Debugging

#### View Apache Error Logs
```bash
npm run shell
tail -f /var/log/apache2/error.log
```

#### Test PHP Configuration
```bash
npm run shell
php -v
php -m | grep session
```

#### Network Debugging
```bash
# Test container connectivity
curl http://localhost:8080/

# Test specific endpoints
curl http://localhost:8080/encode.php
curl "http://localhost:8080/?x=test"
```

## Production Deployment

### Build Production Image
```bash
npm run build:prod
```

### Export for Deployment
```bash
npm run deploy
# Creates: qr3k-production.tar.gz
```

### Load on Production Server
```bash
# Transfer the tar.gz file to production server
scp qr3k-production.tar.gz user@server:~/

# On production server
docker load < qr3k-production.tar.gz
docker run -d -p 80:80 --name qr3k qr3k:production
```

### Docker Compose for Production
```yaml
# docker-compose.prod.yml
version: '3.8'
services:
  qr3k:
    image: qr3k:production
    ports:
      - "80:80"
    restart: always
    container_name: qr3k-prod
```

```bash
docker-compose -f docker-compose.prod.yml up -d
```

## Security Considerations

### Development
- Container runs with standard permissions
- Volume mounts may expose local files
- Use only for development environments

### Production
- Files owned by www-data (non-root)
- Security headers enabled
- No volume mounts (self-contained)
- Consider using secrets for sensitive configuration

### Network Security
- Container only exposes port 80
- No SSH or shell access in production
- All communication over HTTP (consider HTTPS proxy)

## Performance Optimization

### Image Size
- Uses official PHP Apache image
- Minimal additional packages
- Multi-stage build separates dev/prod

### Runtime Performance
- Apache with mod_rewrite for clean URLs
- Static file caching (1 month)
- No-cache for dynamic PHP content
- Gzip compression enabled

### Resource Limits
Add to docker-compose.yml:
```yaml
services:
  qr3k:
    # ... other config
    deploy:
      resources:
        limits:
          memory: 256M
          cpus: '0.5'
```