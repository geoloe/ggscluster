#!/bin/bash

# Change ownership of the uploads directory
chown -R www-data:www-data /usr/local/apache2/htdocs/files/uploads

# Start Apache in the foreground
apache2ctl -D FOREGROUND