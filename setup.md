# Laravel & Filament Production Deployment Guide

### 1. CloudPanel & DNS Preparation
* **DNS Records:** Create IPv4 `A` records for root (`@`), `www`, and your subdomain (`lca`).
* **Clean DNS:** Delete all `AAAA` (IPv6) records. Temporarily disable Cloudflare proxies (grey cloud).
* **Install SSL:** In CloudPanel → SSL/TLS → Click **Create and Install Let's Encrypt**.
* **Update Document Root:** In CloudPanel → Vhost. Append `/public` to the root path:
    ```nginx
    root /home/luxurycode/htdocs/lca.luxurycodeqa.com/public;
    ```
* **Nginx Livewire Fix:** In the same Vhost, locate the static assets block (`location ~* ^.+\.(css|js...)$`) and inject the Laravel fallback to prevent `livewire.js` 404s:
    ```nginx
    location ~* ^.+\.(css|js|jpg|jpeg|gif|png|ico|gz|svg|svgz|ttf|otf|woff|woff2|eot|mp4|ogg|ogv|webm|webp|zip|swf|map)$ {
        try_files $uri $uri/ /index.php?$query_string; # <--- ADD THIS
        add_header Access-Control-Allow-Origin "*";
        # ...
    }
    ```

### 2. Laravel Codebase Preparation
Apply these fixes locally and commit them to your `main` branch.

* **Trust Nginx Proxy** (`bootstrap/app.php`):
    ```php
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
    })
    ```
* **Force HTTPS Assets** (`app/Providers/AppServiceProvider.php`):
    ```php
    public function boot(): void
    {
        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
    ```
* **Authorize Production Access** (`app/Models/User.php`):
    ```php
    use Filament\Models\Contracts\FilamentUser;
    use Filament\Panel;

    class User extends Authenticatable implements FilamentUser
    {
        public function canAccessPanel(Panel $panel): bool
        {
            return true; // Or add explicit role checks here later
        }
    }
    ```

### 3. Server Configuration & One-Time Filament Setup
SSH into your CloudPanel server as the site user (`luxurycode`) to run the initial setup.

**A. Configure `.env`**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lca.luxurycodeqa.com
ASSET_URL=https://lca.luxurycodeqa.com
SESSION_SECURE_COOKIE=true
```

**B. Initial Database & Filament Setup**
Run these commands to build the database, create your admin, and seed permissions.
```bash
cd /home/luxurycode/htdocs/lca.luxurycodeqa.com

# 1. Run core migrations
php artisan migrate --force

# 2. Create the Master Admin User
php artisan make:filament-user
# (Follow the prompts: Name, Email, Password)

# 3. Setup Roles/Permissions (If using Spatie/Filament Shield)
php artisan shield:install --fresh 
# OR if using custom seeders:
php artisan db:seed --class=RolesAndPermissionsSeeder

# 4. Assign Admin Role to your new user
php artisan shield:generate --all
php artisan shield:super-admin
```

### 4. CI/CD Pipeline (GitHub Actions)
Add repository secrets: `SSH_HOST`, `SSH_PORT` (e.g., 22), `SSH_USERNAME` (`luxurycode`), and `SSH_KEY` (Private SSH Key).

Create `.github/workflows/deploy.yml`:
```yaml
name: Deploy LC Assets

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: 20 }
      
      - name: Build frontend
        run: npm install && npm run build

      - name: Sync files
        uses: burnett01/rsync-deployments@5.2
        with:
          switches: -avzr --delete --omit-dir-times --no-perms --exclude='.env' --exclude='storage/' --exclude='bootstrap/cache/' --exclude='vendor/' --exclude='.git/' --exclude='.github/' --exclude='node_modules/'
          path: ./
          remote_path: /home/luxurycode/htdocs/lca.luxurycodeqa.com
          remote_host: ${{ secrets.SSH_HOST }}
          remote_user: ${{ secrets.SSH_USERNAME }}
          remote_key: ${{ secrets.SSH_KEY }}
          remote_port: ${{ secrets.SSH_PORT }}

      - name: Server Post-Deploy
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USERNAME }}
          port: ${{ secrets.SSH_PORT }}
          key: ${{ secrets.SSH_KEY }}
          script: |
            cd /home/luxurycode/htdocs/lca.luxurycodeqa.com
            composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
            php artisan optimize:clear
            php artisan config:cache
            php artisan event:cache
            php artisan route:cache
            php artisan view:cache
            php artisan migrate --force
            sudo supervisorctl restart laravel-worker:* || true
            chmod -R 775 storage bootstrap/cache
            sudo systemctl restart php8.3-fpm || true
```

### 5. Manual Reset (Troubleshooting)
If you ever manually change the `.env` on the server or encounter a persistent 500 error, clear all caches via SSH:
```bash
cd /home/luxurycode/htdocs/lca.luxurycodeqa.com
php artisan optimize:clear
php artisan config:cache
sudo systemctl restart php8.3-fpm
```