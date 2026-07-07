FROM php:8.2-apache

RUN a2enmod rewrite

# ชี้ document root ไปที่ public/ ตามโครงสร้างโปรเจกต์
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . .

RUN chmod -R 775 storage 2>/dev/null || true

# Railway กำหนด port ผ่าน $PORT
RUN sed -i "s/80/\${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE $PORT
CMD ["apache2-foreground"]