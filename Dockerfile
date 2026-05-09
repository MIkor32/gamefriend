FROM php:8.2-fpm-alpine

# 使用镜像源加速 apk 下载（可通过构建参数覆盖）
ARG ALPINE_MIRROR=dl-cdn.alpinelinux.org
RUN sed -i "s/dl-cdn.alpinelinux.org/${ALPINE_MIRROR}/g" /etc/apk/repositories \
    && apk update \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html
COPY . /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
