#!/bin/bash
set -e

# Switch to the application directory
cd /var/www

# Run Laravel optimization commands for production caching
php artisan config:cache   # Cache configuration
php artisan route:cache    # Cache routes
php artisan view:cache     # Cache Blade templates
php artisan optimize
php artisan filament:optimize
# (php artisan event:cache can be added if you use auto-discovered events)

# (Optional) You could run migrations here if desired:
php artisan migrate --force

# Start Supervisor (which starts all processes)
exec /usr/bin/supervisord -c /etc/supervisord.conf
