# Apptica test project

## Launch project in dev mode

Step 1. Create and fill `.env` file based on example
````
cp .env.dev.example .env
````

Step 2. Up docker container
````
docker compose up
````

Step 3. Seed database from docker terminal (optional)
* Only for first run
````
php artisan db:seed
````

Step 4. Send Request from postman or etc
```
GET http://127.0.0.1:8111/backend/api/appTopCategory?date=2023-03-13

*Port 8111 is NGINX_PORT in your .env file
```
