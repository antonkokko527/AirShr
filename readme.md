# AirShr Connect

## Installation

Prerequisites; to launch the Vagrant based development environment please make sure the following is installed on your local system:

  - [Virtualbox](https://www.virtualbox.org/wiki/Downloads)
  - [Vagrant](https://www.vagrantup.com)
  - Git

## Launching

After the prerequisites have been installed clone this repository and in the cloned directory run the following command:

    vagrant up

This will launch the Vagrant based virtual development environment provisioned with the required packages and config. Building the environment will take a little while since a number of dependecies need to be downloaded.

## Prepping

After the environment has been build we need to install all the dependcies by running Composer:

    vagrant ssh

The password is `vagrant`

Once logged in, inside the Vagrant box switch to the synced directory:

    cd /vagrant

Now run Composer:

    composer install

To actually access the backend it's required to load the lastest version of the database. The Vagrant deployment will only create an empty database. To obtain the latest data set first log into the Vagrant box:

This directory mirrors the cloned repository on the local file system. To obtain the latest data set run the `downloaddb` command:

    database/downloaddb airshrproductionmaindb.csrm9mkgmeda.ap-southeast-2.rds.amazonaws.com readonly [password]

Where `[password]` use the password to access the data set.

After the data set has been downloaded it's required to "load" that data set into the prepared database schema:

    database/reloaddb

The both the `downloaddb` and `reloaddb` command can be run as many times as required but it's not required to use `downloaddb` for each `reloaddb` meaning; if you want to reset the data set simply execute `reloaddb`.

## Access

To access the application point your browser to the following location:

    http://localhost:8000

To access the mysql server from outside the Vagrant VM use port `13306` with username `airshr_dev` and password `airshr`.

## Development

Edit files on your local file system with your editor/IDE of choice. Run Git on your local machine. Run Composer from within the Vagrant box.

## Unit Tests

To run the unit tests, simply execute:

    vendor/bin/phpunit
