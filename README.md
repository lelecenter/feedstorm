__FeedStorm__ es un lector/editor de noticias colaborativo, anónimo y software libre.

FeedStorm usa MongoDB y php-curl. Para instalar MongoDB sigue estos tutoriales:
http://docs.mongodb.org/manual/tutorial/install-mongodb-on-ubuntu/
http://www.php.net/manual/es/mongo.installation.php

Si estás usando Ubuntu 14.04 o superior, usa este tutorial:
http://symfonyando.wordpress.com/2014/04/18/mongodb-y-php-en-ubuntu/

Una vez instalado y configurado MongoDB, copia FeedStorm a tu carpeta web,
dale permisos al servidor web para escribir sobre esa carpeta. Por ejemplo
si usas Ubuntu:

    sudo chown -R www-data /var/www/donde-esté-feedstorm


Ahora copia el archivo `config-sample.php` a `config.php`
y rellena los campos.

Para comprobar los feeds necesitas añadir el cron. En Ubuntu:

    cd /etc/cron.hourly/
    sudo nano feedstorm


Copia este script:

    #!/bin/sh
    cd /var/www/donde-este-feedstorm
    php5 cron.php


Por último dale permisos de ejecución:

    sudo chmod +x feedstorm


Y listo. Si tienes algún problema no dudes en informar:
https://github.com/NeoRazorX/feedstorm/issues


Ejemplos de webs creadas con FeedStorm:
http://www.locierto.es
http://www.kelinux.net