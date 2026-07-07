FROM php:8.2-apache

# ปิด MPM module แบบ dynamic ทั้งหมด (รวม prefork) เพราะ mpm ตัวที่ใช้จริง
# ถูก compile แบบ static เข้าไปใน apache2 binary ของอิมเมจนี้อยู่แล้ว
# การเปิดซ้ำแบบ dynamic ทำให้เกิด "AH00534: More than one MPM loaded"
RUN for m in event worker itk prefork; do a2dismod mpm_$m 2>/dev/null || true; done \
    && a2enmod rewrite \
    && echo "---mods-enabled (mpm)---" \
    && (ls -la /etc/apache2/mods-enabled/ | grep -i mpm || echo "(none enabled - using static MPM)")

# ชี้ document root ไปที่ public/ ตามโครงสร้างโปรเจกต์
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . .

RUN chmod -R 775 storage 2>/dev/null || true

# Railway กำหนด port ผ่าน $PORT
RUN sed -i "s/80/\${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# --- diagnostic: หา MPM ที่ยังโหลดซ้ำอยู่จริง (จะไม่ทำให้ build fail) ---
RUN echo "=== mods-enabled ===" \
    && ls -la /etc/apache2/mods-enabled/ \
    && echo "=== grep LoadModule .*mpm ทั้งต้นไม้ /etc/apache2 ===" \
    && (grep -rn "LoadModule" /etc/apache2/ 2>/dev/null | grep -i mpm || echo "(no LoadModule mpm lines found anywhere)") \
    && echo "=== apache2ctl -M ===" \
    && (apache2ctl -M 2>&1 || true)

EXPOSE $PORT
CMD ["apache2-foreground"]