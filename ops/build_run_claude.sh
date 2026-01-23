#!/bin/bash

# Description:
# Instance provisioning
# Author : Kartik Patel


# Install GIT
sudo apt install -y git
git config --global user.name "Name"
git config --global user.email "mail@gmail.com"


git clone https://github.com/name
cd name
cp .env.example .env
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

sudo -H -u www-data bash -c 'php artisan migrate:fresh --seed'
#sudo -H -u www-data bash -c 'php artisan passport:install'
sudo -H -u www-data bash -c 'php artisan passport:client --personal'
sudo -H -u www-data bash -c 'php artisan storage:link'
