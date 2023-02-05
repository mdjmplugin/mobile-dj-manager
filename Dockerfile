FROM wordpress:php8.1-fpm
WORKDIR /var/www/html/wp-content/plugins/mobile-dj-manager
COPY . /var/www/html/wp-content/plugins/mobile-dj-manager

# RUN chown -R www-data:www-data /var/www/html/wp-content/plugins/mobile-dj-manager
CMD ["npm", "start"]
