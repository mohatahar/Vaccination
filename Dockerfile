# Utilisation de l'image officielle PHP avec Apache
FROM php:8.1-apache

# Installation des extensions PHP nécessaires (si besoin)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copier l'application PHP dans le répertoire racine du serveur web
COPY . /var/www/html/

# Autoriser l'exécution du script index.php (ou autre fichier PHP)
RUN chown -R www-data:www-data /var/www/html

# Exposer le port 80 pour accéder à l'application via le navigateur
EXPOSE 80

# Commande par défaut pour démarrer le serveur Apache
CMD ["apache2-foreground"]
