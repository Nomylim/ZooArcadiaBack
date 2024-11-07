# Utiliser une image PHP officielle
FROM php:8.1-cli

# Installer les dépendances nécessaires (curl, git, unzip, etc.)
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définir le répertoire de travail
WORKDIR /app

# Copier tous les fichiers du projet dans le conteneur
COPY . .

# Installer les dépendances via Composer
RUN composer install --ignore-platform-reqs

# Commande pour démarrer l'application (exemple avec PHP built-in server)
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
