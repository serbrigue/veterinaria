# ============================================================
# Etapa base: PHP 8.4 FPM
# ============================================================
FROM php:8.4-fpm

# Argumentos de build
ARG user=www
ARG uid=1000

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP necesarias para Laravel
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Instalar extensión Redis para PHP
RUN pecl install redis && docker-php-ext-enable redis

# Copiar configuración personalizada de PHP y entrypoint
COPY --chown=root:root ./docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY --chown=root:root ./docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Instalar Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Crear usuario no-root para ejecutar la aplicación
RUN useradd -G www-data,root -u $uid -d /home/$user $user \
    && mkdir -p /home/$user/.composer \
    && chown -R $user:$user /home/$user

# Directorio de trabajo
WORKDIR /var/www

# Usar usuario no privilegiado
USER $user

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
