# استخدام صورة PHP رسمية خفيفة كأساس
FROM php:8.2-cli-alpine

# تحديث حزم النظام وتثبيت الحزم اللازمة لتثبيت امتدادات PHP
RUN apk update && apk add --no-cache \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo_mysql mysqli

# تعيين مجلد العمل داخل الحاوية
WORKDIR /app

# نسخ جميع ملفات المشروع من جهازك إلى داخل الحاوية
COPY . .

# إعطاء صلاحيات للخادم لقراءة الملفات
RUN chown -R www-data:www-data /app

# الأمر الذي سيتم تشغيله عند بدء الحاوية
# نستخدم خادم PHP المدمج للاستماع على المنفذ 8080
CMD ["php", "-S", "0.0.0.0:8080"]
