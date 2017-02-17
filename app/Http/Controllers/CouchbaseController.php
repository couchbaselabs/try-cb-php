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
        $this->db = DB::connection('couchbase')->openBucket('default');
    }
}