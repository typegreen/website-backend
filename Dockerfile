FROM php:8.1-cli

# Install system dependencies and then the PostgreSQL PDO driver
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

WORKDIR /app
COPY . .

EXPOSE 9000

CMD ["php", "-S", "0.0.0.0:9000"]
