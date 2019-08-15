<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;


class CouchbaseController extends Controller
{
    protected $db;
    /**
     * Create a new couchbase controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Establish username and password for bucket-access
        $authenticator = new \Couchbase\PasswordAuthenticator();
        $authenticator->username('Administrator')->password('password');

        // Connect to Couchbase Server
        $cluster = new \CouchbaseCluster("couchbase://127.0.0.1");
        // Authenticate, then open bucket
        $cluster->authenticate($authenticator);

        $this->db = $cluster->openBucket('travel-sample');
    }
}