# Couchbase PHP Travel-Sample Application

## Running the Application

To download the application you can either download the archive or clone the repository:

    $ git clone https://github.com/couchbaselabs/try-cb-php.git

Now change into the directory

    $ cd try-cb-php

Install all dependencies

    $ composer install

Configure environment file (edit parameters if necessary):

    $ cp .env.example .env

Generate secret key

    $ php artisan key:generate

And run the application:

    $ php artisan serve --port 8080
    Laravel development server started on http://localhost:8080/

