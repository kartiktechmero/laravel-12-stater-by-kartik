#!/bin/bash

# Description:
# Instance provisioning
# Author : Kartik Patel

# Update dependencies
sudo apt update -y -qq
sudo apt upgrade -y -qq
sudo apt autoremove -y
sudo apt autoclean -y

# Install common packages
sudo apt install -y -qq software-properties-common
sudo apt install -y curl zip unzip wget nmap telnet openssl

# Install GIT
sudo apt install -y git
git config --global user.name "kartik"
git config --global user.email "kartikptechmero@gmail.com"

# Install Apache
# sudo apt install -y apache2
# sudo a2enmod headers
# sudo a2enmod rewrite
# sudo a2enmod ssl
# sudo ufw allow 'Apache'
# sudo systemctl restart apache2

# Install PHP
# sudo add-apt-repository -y ppa:ondrej/php
# sudo apt update -y -qq
# sudo apt install -y php8.3 libapache2-mod-php8.3
# sudo apt install -y php8.3-common php8.3-bcmath php8.3-gd php8.3-opcache php8.3-mbstring php8.3-tokenizer
# sudo apt install -y php8.3-zip php8.3-bz2 php8.3-mysql php8.3-xml php8.3-mcrypt php8.3-curl
# sudo apt install -y php8.3-intl php8.3-dom php8.3-imagick
# sudo systemctl restart apache2

# Install Composer
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm -f composer-setup.php
composer --version
sudo apt update -y -qq

# Install Supervisor
# sudo apt install -y supervisor
# sudo systemctl start supervisor
# sudo systemctl enable supervisor

# Node setup
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
node -v

# Mount External EBS
# sudo lsblk -d | grep disk
# sudo file -s /dev/nvme1n1
# sudo mkfs -t ext4 /dev/nvme1n1
# sudo mount /dev/nvme1n1 /mnt/

# Setup project
sudo chown -R $USER:$USER /mnt
cd /mnt
git clone https://github.com/name
cd name
cp .env.example .env
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm install && npm run build
sudo chown -R $USER:www-data .
sudo find . -type f -exec chmod 664 {} \;
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type d -exec chmod g+s {} \;
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
sudo chown -R $USER:$USER ./node_modules
sudo chmod -R 755 ./node_modules

# setup virtual host
# sudo cp ops/apache-vhost.conf /etc/apache2/sites-available/111-dp.conf
# sudo a2ensite 111-dp.conf
# sudo service apache2 restart

sudo -H -u www-data bash -c 'php artisan migrate:fresh --seed'
