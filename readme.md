# Couchbase PHP Travel-Sample Application

## IMPORTANT NOTE

THIS VERSION DEPENDS ON COUCHBASE PHP SDK **2.3.1**,
WHICH IS NOT AVAILABLE YET ON pecl.php.net. TO GET
MOST RECENT SNAPSHOT, WHICH PASS ALL UNIT TESTS USE

http://sdkbuilds.sc.couchbase.com/view/PHP/job/php-sdk-package/lastSuccessfulBuild/artifact/packages/

Instructions for Unix-like OSes (for Windows, just use pre-built binaries):

    shell> wget http://sdkbuilds.sc.couchbase.com/view/PHP/job/php-sdk-package/lastSuccessfulBuild/artifact/packages/couchbase-2.3.1snapshot.tgz
    shell> sudo pecl install couchbase-2.3.1snapshot.tgz

## Setting Up Prerequisites

Default sample configuration assumes that `travel-sample` bucket created on the Couchbase Server at
http://localhost:8091. Also the cluster should have Full Text Search capability, and FTS index named `hotels`
bound to this sample bucket.

See the main documentation at http://developer.couchbase.com in the developer section for more information.

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
