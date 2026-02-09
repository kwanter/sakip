#!/bin/bash
# SAKIP Security Credential Rotation Script
# Run this script to rotate all exposed credentials

echo "🔐 SAKIP Credential Rotation Script"
echo "===================================="
echo ""

# Generate new APP_KEY
echo "1. Generating new APP_KEY..."
php artisan key:generate --show

echo ""
echo "2. Generate new database passwords:"
echo "   DB_PASSWORD: $(openssl rand -base64 24)"
echo "   DB_ROOT_PASSWORD: $(openssl rand -base64 32)"

echo ""
echo "3. Generate new Redis password:"
echo "   REDIS_PASSWORD: $(openssl rand -base64 24)"

echo ""
echo "⚠️  IMPORTANT NEXT STEPS:"
echo "   1. Update .env with the new credentials above"
echo "   2. Update docker-compose.yml with new DB and Redis passwords"
echo "   3. Restart all services: docker-compose down && docker-compose up -d"
echo "   4. Run migrations if needed: php artisan migrate"
echo "   5. Clear all caches: php artisan optimize:clear"
echo ""
echo "✅ After updating, verify the application works correctly"
