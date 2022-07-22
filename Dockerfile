FROM php:7.4-cli
RUN apt update \
  && apt install -y -f apt-transport-https \
		libicu-dev \
		libjpeg-dev \
		libfreetype6-dev \
		libonig-dev \
		libpng-dev \
		libpq-dev \
		libwebp-dev \
		libxml2-dev \
		libzip-dev \
		acl \
		cron \
		git \
		zip

RUN  docker-php-ext-install \
		bcmath \
		exif \
		gd \
		gettext \
		intl \
		mbstring \
		mysqli \
		opcache \
		pgsql \
		pdo \
		pdo_mysql \
		pdo_pgsql \
		zip

COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
CMD [ "php", "./vnkm.php" ]
