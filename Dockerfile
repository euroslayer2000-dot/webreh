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

# ปิด mpm_event ตอน "runtime" ไม่ใช่ตอน build เพราะพบว่ามีบางอย่าง
# (dpkg trigger ของ apache2 เอง) มา re-enable mpm_event คืนตอน container
# เริ่มทำงานทุกครั้ง ทำให้ mpm_event + mpm_prefork ถูกโหลดพร้อมกัน
# -> "AH00534: More than one MPM loaded" ต้องปิดซ้ำตรงนี้ก่อน apache จะ start จริง
CMD sh -c 'a2dismod mpm_event >/dev/null 2>&1 || true; a2enmod mpm_prefork >/dev/null 2>&1 || true; exec apache2-foreground'