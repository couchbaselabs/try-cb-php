<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class AirportController extends CouchbaseController
{

    public function search(Request $request)
    {
        $searchStr = $request->search;
        $options = new \Couchbase\QueryOptions();
        $sameCase = ctype_upper($searchStr) || ctype_lower($searchStr);
        $queryPrep = 'SELECT airportname FROM `travel-sample`.inventory.airport WHERE ';
        if ($sameCase && strlen($searchStr) == 3) {
            // FAA code
            $options->namedParameters(['faa' => strtoupper($searchStr)]);
            $query = $queryPrep . 'faa=$faa';
        } else if ($sameCase && strlen($searchStr) == 4) {
            // ICAO code
            $options->namedParameters(['icao' => strtoupper($searchStr)]);
            $query = $queryPrep . 'icao=$icao';
        } else {
            // Airport name
            $options->namedParameters(['airportname' => strtolower($searchStr)]);
            $query = $queryPrep . 'POSITION(LOWER(airportname), $airportname) = 0';
        }

        $result = $this->cluster->query($query, $options);

        $queryType = "N1QL query - scoped to inventory: ";
        return response()->json([
            "data" => $result->rows(),
            "context" => [$queryType, $query]
        ]);
    }
}