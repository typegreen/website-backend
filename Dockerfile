FROM php:8.1-cli

RUN docker-php-ext-install pdo_pgsql

WORKDIR /app
COPY . .

EXPOSE 9000

CMD ["php", "-S", "0.0.0.0:9000"]
