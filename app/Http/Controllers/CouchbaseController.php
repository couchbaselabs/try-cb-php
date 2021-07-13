<?php

namespace App\Http\Controllers;

use Couchbase\Cluster;

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
        $connectionString = "couchbase://localhost";
        $opts = new \Couchbase\ClusterOptions();
        $opts->credentials("Administrator", "password");
        $cluster = new Cluster($connectionString, $opts);
        $dataBucket = $cluster->bucket("travel-sample");

        $this->bucket = $dataBucket;
        $this->cluster = $cluster;
    }
}