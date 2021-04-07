FROM php:7.4.16-cli-alpine3.13
#Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

#Instal all gd with all relevant extentions
RUN apk add --no-cache freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev libwebp-dev
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install gd

WORKDIR /home/ancestor

COPY composer.json .

RUN composer update

COPY . .

CMD ["php", "bot_start.php"]
