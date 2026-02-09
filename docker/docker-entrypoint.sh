#!/bin/bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting SAKIP Application...${NC}"

# Wait for MySQL to be ready
if [ ! -z "$DB_HOST" ]; then
    echo -e "${YELLOW}Waiting for MySQL at $DB_HOST...${NC}"
    max_attempts=30
    attempt=0

    while [ $attempt -lt $max_attempts ]; do
        if php -r "try {
            \$pdo = new PDO('mysql:host=$DB_HOST;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }" 2>/dev/null; then
            echo -e "${GREEN}MySQL is ready!${NC}"
            break
        fi
        attempt=$((attempt + 1))
        echo "Waiting for MySQL... (attempt $attempt/$max_attempts)"
        sleep 2
    done

    if [ $attempt -eq $max_attempts ]; then
        echo -e "${RED}MySQL connection timeout!${NC}"
        exit 1
    fi
fi

# Wait for Redis if configured
if [ ! -z "$REDIS_HOST" ]; then
    echo -e "${YELLOW}Checking Redis at $REDIS_HOST...${NC}"
    max_attempts=10
    attempt=0

    while [ $attempt -lt $max_attempts ]; do
        if php -r "
            \$redis = new Redis();
            if (@\$redis->connect('$REDIS_HOST', 6379, 2)) {
                exit(0);
            }
            exit(1);
        " 2>/dev/null; then
            echo -e "${GREEN}Redis is ready!${NC}"
            break
        fi
        attempt=$((attempt + 1))
        echo "Waiting for Redis... (attempt $attempt/$max_attempts)"
        sleep 1
    done
fi

# Check if APP_KEY is set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo -e "${YELLOW}Generating APP_KEY...${NC}"
    php artisan key:generate --force
fi

# Run migrations if auto-migrate is enabled
if [ "$AUTO_MIGRATE" = "true" ]; then
    echo -e "${YELLOW}Running migrations...${NC}"
    php artisan migrate --force
fi

# Clear and cache config if needed
if [ "$CACHE_CONFIG" = "true" ]; then
    echo -e "${YELLOW}Caching configuration...${NC}"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Create storage directories if they don't exist
echo -e "${YELLOW}Ensuring storage directories exist...${NC}"
mkdir -p /var/www/html/storage/app
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/framework
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create symbolic link for public storage
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link || true
fi

echo -e "${GREEN}SAKIP Application is ready!${NC}"

# Execute the main command
exec "$@"
