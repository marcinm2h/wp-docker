FROM wordpress:latest
RUN usermod -u 1000 www-data \
 && groupmod -g 1000 www-data