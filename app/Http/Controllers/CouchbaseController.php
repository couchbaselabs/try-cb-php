<?php

namespace App\Http\Controllers;

use Couchbase\Cluster;
use Couchbase\ClusterOptions;

class CouchbaseController extends Controller
{
    protected $cluster;

    /**
     * Create a new couchbase controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $connectionString = config('database.connections.couchbase.host');
        $opts = new ClusterOptions();
        $opts->credentials(
            config('database.connections.couchbase.user'),
            config('database.connections.couchbase.password')
        );
        $cluster = new Cluster($connectionString, $opts);
        $dataBucket = $cluster->bucket(config("database.connections.couchbase.bucket"));

        $this->bucket = $dataBucket;
        $this->cluster = $cluster;
    }
}