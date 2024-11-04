#!/bin/bash

# Change ownership of the uploads directory
mkdir -p /usr/local/apache2/htdocs/files/uploads
chown -R www-data:www-data /usr/local/apache2/htdocs/files/uploads

# Start Apache in the foreground
apache2ctl -D FOREGROUND