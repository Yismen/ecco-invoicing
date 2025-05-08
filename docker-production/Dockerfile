# docker-production/Dockerfile
# Stage 1: Build and install dependencies
FROM php:8.3-fpm AS base

# Install system packages and PHP extensions for Laravel
RUN apt-get update && apt-get install -y --no-install-recommends --fix-missing \
    sudo git unzip vim cron supervisor \
    libzip-dev libpq-dev libicu-dev libonig-dev libxml2-dev libssl-dev \
    && docker-php-ext-install pdo_mysql bcmath opcache intl zip \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Create a user 'yjorge' with sudo privileges
RUN useradd -m -d /home/yjorge -s /bin/bash yjorge && \
    usermod -aG sudo yjorge && \
    echo "yjorge ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/90-yjorge

# Set working directory
WORKDIR /var/www

# Copy application code and composer files
COPY . /var/www

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Install Composer (copy from official image) and PHP dependencies
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copy Laravel env and optimize configuration
# (Ensure .env is present in build context; it will be used for config caching)
# COPY .env.example /var/www/.env
# RUN if .env ; then COPY .env /var/www/.env ; COPY .env.example /var/www/.env; fi
RUN if [ -f "./.env" ]; then \
      cp ./.env /var/www/.env; \
    else \
      cp ./.env.example /var/www/.env; \
    fi
# RUN php artisan key:generate --ansi && \
#     php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize

# Set proper permissions for storage (so that 'yjorge' can write logs, cache, etc.)
# RUN chmod -R 775 storage bootstrap/cache
# RUN chown -R www-data:www-data /var/www && chmod -R 775 storage bootstrap/cache

# Cron setup: copy cron schedule file and give proper permissions
COPY docker-production/laravel-scheduler.cron /etc/cron.d/laravel-scheduler
RUN chmod 0644 /etc/cron.d/laravel-scheduler

# Supervisor setup: copy supervisor config
# COPY docker-production/supervisor.conf /etc/supervisor/conf.d/laravel.conf
COPY docker-production/supervisor.conf /etc/supervisord.conf

# Make the startup script executable
COPY docker-production/run.sh /usr/local/bin/run.sh
RUN chmod +x /usr/local/bin/run.sh

ENTRYPOINT ["/usr/local/bin/run.sh"]

# Expose port 9000 for PHP-FPM (to be used by Nginx)
EXPOSE 9000

# Start Supervisor (which will start php-fpm, queue worker, and cron)
# CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/laravel.conf"]
# CMD ["supervisor reread","supervisor update","supervisor start", "laravel-worker:*", "/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

RUN
