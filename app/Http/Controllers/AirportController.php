<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;


class AirportController extends CouchbaseController
{

    public function search(Request $request)
    {
        $searchStr = $request->__get("search");

        if (strlen($searchStr) == 3) {
            $param = '$faa';
            $query = \CouchbaseN1qlQuery::fromString("SELECT airportname FROM `travel-sample` WHERE faa like ".$param." limit 5");
            $query->namedParams(array('faa' => $searchStr));
        }
        else if (strlen($searchStr) == 4 && ctype_upper($searchStr)) {
            $param = '$icao';
            $query = \CouchbaseN1qlQuery::fromString("SELECT airportname FROM `travel-sample` WHERE icao like ".$param." limit 5");
            $query->namedParams(array('icao' => $searchStr));
        } else {
            $param = '$airportName';
            $query = \CouchbaseN1qlQuery::fromString("SELECT airportname FROM `travel-sample` WHERE airportname like ". $param ." limit 5");
            $query->namedParams(array('airportname' => $searchStr.'%'));
        }

        $result = $this->db->query($query);
        return response()->json(["data" =>  $result->rows]);

    }

}