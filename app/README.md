# Medical records, with Git backend.

## Start Server

    php -S localhost:8080 -t public -c custom-php.ini

Or on a device, as in production

    APP_ENV=prod APP_DEBUG=0 php -S localhost:8080 -t public -c custom-php.ini

Navigate to http://localhost:8080

## Install for Local Dev

    ddev composer install
    yarn install

## Install on Termux

First install [Termux](https://termux.com/), start it and enter the following commands:

    # Install packages.
    pkg install git
    pkg install php
    pkg install sqlite

    # Clone repo
    git clone git@github.com:Gizra/mdr-symfony.git
    cd mdr-symfony/app

    # Download composer, as per instructions from https://getcomposer.org/download/
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('sha384', 'composer-setup.php') === 'e0012edf3e80b6978849f5eff0d4b4e4c79ff1609dd1e613307e16318854d24ae64f26d17af3ef0bf7cfb710ca74755a') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    
    # Add .env.local (and edit if necessary)
    cp .env.local.example .env.local

    # Install packages
    php composer.phar install

    # Remove existing DB if needed.
    rm var/data-*.db
    php bin/console doctrine:migrations:migrate -n
    php bin/console sync:download defer_photos_download

    # To fetch photos
    php bin/console sync:fetch-photos

On the device, under Settings > Battery - make sure to disable "Manage automatically",
otherwise Termux may crash from time to time.

## Console Commands

To run in loop

    ./watch_sync
    
## Sync photos and DB via ssh to device

On termux have rsync: 
    
    pkg install rsync

On computer, From `symfony-client` folder (ssh address would change from device to device):

    # Sync photos
    rsync -arv -zz -e 'ssh -p 8022' --progress ./public/uploads/child/photos u0_a110@10.0.0.4:~/ihangane/symfony-client/public/uploads/child
    # Sync DBs
    rsync -arv -zz -e 'ssh -p 8022' --progress ./var/data-*.db u0_a110@10.0.0.4:~/ihangane/symfony-client/var
    
### ngrok
 
On Termux

    ngrok tcp 8022

On host:
 
    rsync -arv -zz -e 'ssh -p 11394' --progress ./public/uploads/child/photos 0.tcp.ngrok.io:~/ihangane/symfony-client/public/uploads/child/photos
    rsync -arv -zz -e 'ssh -p 11394' --progress ./var/data-*.db 0.tcp.ngrok.io:~/ihangane/symfony-client/var

## SSH to Remote Termux

Assuming device is connected on same Wifi (otherwise, connection should be done
with ngrok).

Find IP with `ifconfig`

    ssh <username>@<IP address> -p 8022
    
## Remote browsing

After running the PHP server on the device, we can use our Browser (e.g. Chrome or Firefox)
on the host computer to view the site.

    ssh -L 8080:127.0.0.1:8080 -C -N -l <username> <IP address> -p 8022
    
for example:

    ssh -L 8080:127.0.0.1:8080 -C -N -l u0_a110 10.0.0.4 -p 8022
    
(Remember to change your <username> and <IP address>)

This will work also with ngrok (`ngrok tcp 8022`)

    ssh -L 8080:127.0.0.1:8080 -C -N -l <username> 0.tcp.ngrok.io -p <port>

for example

    ssh -L 8080:127.0.0.1:8080 -C -N -l u0_a110 0.tcp.ngrok.io -p 10205

## Development of Assets

Execute the following while developing CSS & JS

    yarn encore dev --watch

When ready to publish, minify assets, and commit built files

    yarn encore production
