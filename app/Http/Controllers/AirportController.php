<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;


class AirportController extends CouchbaseController
{

    public function search(Request $request)
    {
        $searchStr = $request->search;

        if (strlen($searchStr) == 3) {
            $query = \CouchbaseN1qlQuery::fromString('SELECT airportname FROM `travel-sample` WHERE faa like $faa limit 5');
            $query->namedParams(['faa' => $searchStr]);
        }
        else if (strlen($searchStr) == 4 && ctype_upper($searchStr)) {
            $param = '$icao';
            $query = \CouchbaseN1qlQuery::fromString('SELECT airportname FROM `travel-sample` WHERE icao like $icar limit 5');
            $query->namedParams(['icao' => $searchStr]);
        } else if (strlen($searchStr) > 0) {
            $query = \CouchbaseN1qlQuery::fromString('SELECT airportname FROM `travel-sample` WHERE airportname like $airportname limit 5');
            $query->namedParams(['airportname' => $searchStr.'%']);
        } else {
            $query = \CouchbaseN1qlQuery::fromString("SELECT airportname FROM `travel-sample` limit 5");
        }

        $result = $this->db->query($query);
        return response()->json(["data" =>  $result->rows]);
    }
}