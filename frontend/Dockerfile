FROM yiisoftware/yii2-php:8.2-apache

# Change document root for Apache
RUN sed -i -e 's|/app/web|/app/frontend/web|g' /etc/apache2/sites-available/000-default.conf

# Enable mod_rewrite for Yii2 URL rewriting
RUN a2enmod rewrite
