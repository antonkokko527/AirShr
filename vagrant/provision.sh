#!/bin/bash

DBNAME=airshr
DBUSER=airshr
DBPASS=airshr

# Add swap space.
sh /vagrant/vagrant/swap.sh

# Set the mysql password so we're not asked for it during provisioning.
debconf-set-selections <<< "mysql-server mysql-server/root_password password ${DBPASS}"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password ${DBPASS}"

# add php5.6+ repos.
add-apt-repository ppa:ondrej/php

# install prep.
apt-get update
#apt-get -y upgrade
#apt-get -y install software-properties-common
#apt-get -y install python-software-properties

# Install basic necessities.
apt-get -y install ntp git htop tree unzip
service ntp restart

# install php 5.6+
apt-get -y install libapache2-mod-php5.6 php5.6 php5.6-cli php5.6-curl php5.6-mysqlnd php5.6-mcrypt php5.6-intl php5.6-json php5.6-gd php5.6-redis php5.6-xml php-pear php5.6-mbstring php5.6-zip  

sed -i "s/;date timezone =/date timezone = UTC/" /etc/php/5.6/apache2/php.ini
sed -i "s/;date timezone =/date timezone = UTC/" /etc/php/5.6//cli/php.ini

sed -i "s/;realpath_cache_size = 16k/realpath_cache_size = 4096k/" /etc/php/5.6//apache2/php.ini
sed -i "s/;realpath_cache_size = 16k/realpath_cache_size = 4096k/" /etc/php/5.6//cli/php.ini

sed -i "s/;realpath_cache_ttl = 120/realpath_cache_size = 7200/" /etc/php/5.6/apache2/php.ini
sed -i "s/;realpath_cache_ttl = 120/realpath_cache_size = 7200/" /etc/php/5.6//cli/php.ini

# install php 5.6+
apt-get -y install \
  libapache2-mod-php5.6 \
  php5.6 \
  php5.6-cli \
  php5.6-curl \
  php5.6-mysqlnd \
  php5.6-mcrypt \
  php5.6-intl \
  php5.6-json \
  php5.6-gd \
  php5.6-redis \
  php5.6-xml \
  php5.6-mbstring \
  php-pear

sed -i "s/;date timezone =/date timezone = UTC/" /etc/php/5.6/apache2/php.ini
sed -i "s/;date timezone =/date timezone = UTC/" /etc/php/5.6//cli/php.ini

sed -i "s/;realpath_cache_size = 16k/realpath_cache_size = 4096k/" /etc/php/5.6//apache2/php.ini
sed -i "s/;realpath_cache_size = 16k/realpath_cache_size = 4096k/" /etc/php/5.6//cli/php.ini

sed -i "s/;realpath_cache_ttl = 120/realpath_cache_size = 7200/" /etc/php/5.6/apache2/php.ini
sed -i "s/;realpath_cache_ttl = 120/realpath_cache_size = 7200/" /etc/php/5.6//cli/php.ini

# Install Composer.
php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/bin/composer

# install apache & mysql
apt-get -y install apache2 mysql-server

# remove MySQL localhost host binding so we can access the server from the guest machine.
sed -i "s/bind-address$(printf '\t\t')= 127.0.0.1/bind-address$(printf '\t\t')= 0.0.0.0/" /etc/mysql/my.cnf
service mysql restart

# create Apache virtual host.
cat > /etc/apache2/sites-available/000-default.conf <<EOL
<VirtualHost *:80>
  #ServerName www.example.com

  ServerAdmin webmaster@localhost
  DocumentRoot /vagrant/public

  <Directory /vagrant/public>
    Options Indexes FollowSymLinks MultiViews
    AllowOverride All
    Order allow,deny
    Allow from all
    Require all granted
  </Directory>

  ErrorLog \${APACHE_LOG_DIR}/error.log
  CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOL

# change apache user to vagrant so we don't have to mess with permissions.
sed -i "s/User \${APACHE_RUN_USER}/User vagrant/" /etc/apache2/apache2.conf
sed -i "s/Group \${APACHE_RUN_GROUP}/Group vagrant/" /etc/apache2/apache2.conf

# enable required apache modules and restart server.
a2enmod rewrite
a2enmod php5.6
a2enmod status
a2dismod mpm_event
a2enmod mpm_prefork
service apache2 restart

# create the dev and prod database with user. The dev database is for development, the prod
# database is used to create migrations against.
declare -a dbs=("dev" "prod")
for i in "${dbs[@]}"
do
cat > /tmp/${DBNAME}_${i}.sql <<EOL
create database if not exists ${DBNAME}_${i};
alter database ${DBNAME}_${i} character set utf8 collate utf8_general_ci;
grant usage on ${DBNAME}_${i}.* to '${DBUSER}'@'%' identified by '${DBPASS}';
grant all privileges on ${DBNAME}_${i}.* to '${DBUSER}'@'%'; 
flush privileges;
EOL
mysql -uroot -p${DBPASS} -hlocalhost < /tmp/${DBNAME}_${i}.sql
done

cat > /vagrant/database/downloaddb <<EOL
#!/bin/bash
if [ $# -ne 3 ]; then
  downloaddb [url] [user] [password]
  exit 1
fi

mkdir -p /vagrant/database/exports
mysqldump -h$1 --single-transaction -u$2 -p$3 airshr > /vagrant/database/exports/${DBNAME}.sql
EOL
chmod +x /vagrant/database/downloaddb

cat > /vagrant/database/reloaddb <<EOL
#!/bin/bash

mysqladmin -uroot -pairshr drop ${DBNAME}_dev -f
mysql -uroot -p${DBPASS} -hlocalhost -e "CREATE SCHEMA airshr_dev DEFAULT CHARACTER SET utf8 ;"
mysql -uroot -p${DBPASS} -hlocalhost ${DBNAME}_dev < /vagrant/database/exports/${DBNAME}.sql
EOL
chmod +x /vagrant/database/reloaddb

# create laravel config environment.
cat > /vagrant/.env <<EOL
API_DOMAIN_NAME=localhost
CONNECT_DOMAIN_NAME=localhost
SHARE_DOMAIN_NAME=localhost

APP_ENV=local
APP_DEBUG=true
APP_KEY=SomeRandomString

DB_HOST=localhost
DB_DATABASE=${DBNAME}_dev
DB_USERNAME=${DBUSER}
DB_PASSWORD=${DBPASS}

CACHE_DRIVER=file
SESSION_DRIVER=file

APP_DEBUG=true
EOL
