# Couchbase PHP Travel-Sample Application

This is a sample application for getting started with [Couchbase Server] and the [PHP SDK].
The application runs a single page web UI for demonstrating SQL for Documents (N1QL), Sub-document requests and Full Text Search (FTS) querying capabilities.
It uses Couchbase Server together with the [Laravel] web framework for [PHP], [Vue] and [Bootstrap].

The application is a flight planner that allows the user to search for and select a flight route (including the return flight) based on airports and dates.
Airport selection is done dynamically using an autocomplete box bound to N1QL queries on the server side. After selecting a date, it then searches
for applicable air flight routes from a previously populated database. An additional page allows users to search for Hotels using less structured keywords.

## Prerequisites

To download the application you can either download [the archive](https://github.com/couchbaselabs/try-cb-php/archive/master.zip) or clone the repository:

```
git clone https://github.com/couchbaselabs/try-cb-php.git
```

We recommend running the application with Docker, which starts up all components for you, but you can also run it in a Mix-and-Match style, which we'll decribe below.

## Running the application with Docker

You will need [Docker](https://docs.docker.com/get-docker/) installed on your machine in order to run this application as we have defined 
a [_Dockerfile_](Dockerfile) and a [_docker-compose.yml_](docker-compose.yml) to run Couchbase Server 7.0.0, 
the frontend [Vue app](https://github.com/couchbaselabs/try-cb-frontend-v2.git) and the PHP REST API.

To launch the full application you can simply run this command from a terminal:

```
docker-compose up
```

> **_NOTE:_** You may need more than the default RAM to run the images.
We have tested the travel-sample apps with 4.5 GB RAM configured in Docker's Preferences... -> Resources -> Memory.
When you run the application for the first time, it will pull/build the relevant docker images, so it might take a bit of time.

This will start the PHP backend, Couchbase Server 7.0.0 and the Vue frontend app.

You can access the backend API on `http://localhost:8080/`, the UI on `http://localhost:8081/` and Couchbase Server at `http://localhost:8091/`.

You should then be able to browse the UI, search for US airports and get flight route information.

To end the application press <kbd>Control</kbd>+<kbd>C</kbd> in the terminal and wait for docker-compose to gracefully stop your containers.

## Mix and match services

Instead of running all services, you can start any combination of `backend`,`frontend`, `db` via docker, and take responsibility for starting the other services yourself.

As the provided `docker-compose.yml` sets up dependencies between the services, to make startup as smooth and automatic as possible, we also provide an alternative `mix-and-match.yml`.  We'll look at a few useful scenarios here.

### Bring your own database
If you wish to run this application against your own configuration of Couchbase Server, you will need version 7.0.0 or later with the `travel-sample` bucket setup.

> **_NOTE:_** If you are not using Docker to start up the Database, or the provided wrapper wait-for-couchbase.sh, you will need to create a full text search index on travel-sample bucket called 'hotels-index'. You can do this via the following command:

```
curl --fail -s -u <username>:<password> -X PUT \
        http://<host>:8094/api/index/hotels-index \
        -H 'cache-control: no-cache' \
        -H 'content-type: application/json' \
        -d @fts-hotels-index.json
```

With a running Couchbase Server, you can edit the database details in the [_.env_](.env) file:

```
CB_HOST=10.144.211.101
CB_USER=Administrator 
CB_PSWD=password
```

Then simply run `docker-compose -f mix-and-match.yml up backend frontend`.

The Docker image will run the same checks as usual, and also create the hotels-index if it does not already exist.

### Running the backend manually

If you want to run the PHP API yourself without using Docker, you will need to ensure that you have `PHP 7.3` or higher installed on your machine. You may still use Docker to run the Database and Frontend components if desired.

Before setting up the PHP SDK, you must install [libcouchbase] (LCB); version 3.1 or higher is required.

You can now install the SDK through your PHP distribution’s pecl command:

```
pecl install couchbase
```

To load the Couchbase SDK extension you will need to locate your `php.ini` file.
If you run the `php --ini` command in your shell it will tell you its location.

```
Configuration File (php.ini) Path: /usr/local/etc/php/7.4
Loaded Configuration File:         /usr/local/etc/php/7.4/php.ini
Scan for additional .ini files in: /usr/local/etc/php/7.4/conf.d
Additional .ini files parsed:      /usr/local/etc/php/7.4/conf.d/ext-opcache.ini
```

Depending on whether you are on a Mac, Linux or Windows machine you will need to add
the following to the ini file:

Mac/Linux
```
extension=couchbase.so
```

Windows
```
extension=couchbase.dll
```

Lastly, setup the [Composer] PHP dependency management tool to ensure you can install the project dependencies.

Now we can install the project dependencies:

```
composer install
```

The first time you run against a new database image, you may want to use the provided
`wait-for-couchbase.sh` wrapper to ensure that all indexes are created.

For example, using the Docker image provided:

```
docker-compose -f mix-and-match.yml up -d db
export CB_HOST=localhost
./wait-for-couchbase.sh echo Couchbase is ready!
```

Update the [_.env_](.env) file with your Couchbase Server details (i.e. `CB_HOST=localhost`) and then run:

```
php artisan config:clear

php artisan serve --host 0.0.0.0 --port 8080
```

Finally, if you want to see how the sample frontend Vue application works with your changes,
run it with:

```
docker-compose -f mix-and-match.yml up frontend
```

### Running the frontend manually

To run the frontend components manually without Docker, follow the guide
[here](https://github.com/couchbaselabs/try-cb-frontend-v2)


[Couchbase Server]: https://www.couchbase.com/
[PHP SDK]: https://docs.couchbase.com/php-sdk/current/hello-world/overview.html
[Laravel]: https://laravel.com/
[PHP]: https://www.php.net/
[Vue]: https://vuejs.org/
[Bootstrap]: https://getbootstrap.com/
[libcouchbase]: https://docs.couchbase.com/c-sdk/current/hello-world/start-using-sdk.html
[Composer]: https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos
