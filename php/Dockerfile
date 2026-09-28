FROM php:8.3-fpm-alpine

# Instalar extensiones de PHP requeridas para conectar con MySQL mediante PDO
RUN docker-php-ext-install pdo pdo_mysql

# Establecer directorio de trabajo dentro del contenedor
WORKDIR /var/www/html
