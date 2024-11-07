# Utilisez une image PHP officielle
FROM php:8.1-cli

# Installez les dépendances de base (curl, git, unzip)
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip

# Installez Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Vérifiez que Composer est installé correctement
RUN composer --version

# Copiez le code source dans le conteneur
COPY . /app
WORKDIR /app

# Installez les dépendances du projet
RUN composer install --ignore-platform-reqs

