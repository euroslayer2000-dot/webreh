FROM php:8.2-apache

# ติดตั้ง dependencies ที่ Laravel ต้องใช้
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

RUN a2enmod rewrite

# ตั้งให้ Apache ชี้ไปที่ public/ ตาม Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader

# ให้ storage และ cache เขียนได้
RUN chmod -R 775 storage bootstrap/cache

# Railway กำหนด port ผ่าน $PORT ต้องปรับ Apache ให้ฟัง port นี้
RUN sed -i "s/80/\${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE $PORT
CMD ["apache2-foreground"]