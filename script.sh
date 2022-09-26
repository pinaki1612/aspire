#!/bin/bash
docker-compose down
echo "docker container down";
docker-compose up --build -d
echo "docker build";
docker exec demo-laravel-app composer install
echo "Dependency install";
docker exec demo-laravel-app php artisan optimize
echo "Optimize app"
docker exec demo-laravel-app php artisan migrate
echo "Migration Started";
docker exec demo-laravel-app php artisan db:seed --class=UserSeeder
echo "Seed super admin and customer";
