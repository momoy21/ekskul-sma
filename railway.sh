#!/bin/bash
# Run database migrations on Railway
# Execute manually via: railway run bash railway.sh

set -e

echo "Running database migrations..."
php artisan migrate --force

echo "Migrations completed successfully!"
