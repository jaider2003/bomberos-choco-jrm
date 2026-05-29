FROM php:8.2-apache

# Habilitar módulos necesarios
RUN docker-php-ext-install pdo_mysql

# Copiar todos los archivos del proyecto al servidor web
COPY . /var/www/html/

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto 80
EXPOSE 80
