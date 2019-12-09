<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;


class AirportController extends CouchbaseController
{

    public function search(Request $request)
    {
        $searchStr = $request->search;
        $options = new \Couchbase\QueryOptions();
        if (strlen($searchStr) == 3) {
            $options->namedParameters(['faa' => $searchStr]);
            $query = 'SELECT airportname FROM `travel-sample` WHERE faa like $faa limit 5';
        }
        else if (strlen($searchStr) == 4 && ctype_upper($searchStr)) {
            $options->namedParameters(['icao' => $searchStr]);
            $query = 'SELECT airportname FROM `travel-sample` WHERE faa like $icao limit 5';
        } else if (strlen($searchStr) > 0) {
            $options->namedParameters(['airportname' => $searchStr.'%']);
            $query = 'SELECT airportname FROM `travel-sample` WHERE airportname like $airportname limit 5';
        } else {
            $query = "SELECT airportname FROM `travel-sample` limit 5";
        }

        $result = $this->db->query($query, $options);
        
        return response()->json(["data" =>  $result->rows(),
                                 "context" => [$query]
                                ]);
    }
}