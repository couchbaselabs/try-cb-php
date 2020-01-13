# Couchbase PHP Travel-Sample Application

## Setting Up Prerequisites

Default sample configuration assumes that `travel-sample` bucket created on the Couchbase Server at
http://localhost:8091. Also the cluster should have Full Text Search capability, and FTS index named `hotels`
bound to this sample bucket.

Collections can be set up using the `create-collections.sh` script included in this repository, with the following syntax:
```bash
$ sh create-collections.sh [ClusterUser] [ClusterPassword] [ClusterHost]
# Example:
$ sh create-collections.sh Administrator password localhost
```

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
