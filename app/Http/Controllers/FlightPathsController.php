<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DateTime;

class FlightPathsController extends CouchbaseController
{

    public function find(Request $request, $from, $to)
    {
        $qs1 = '
            SELECT faa AS fromAirport  FROM `travel-sample`
            WHERE airportname = $from UNION
            SELECT faa AS toAirport FROM `travel-sample`
            WHERE airportname = $to';

        $qOpts1 = $options = new \Couchbase\QueryOptions();
        $qOpts1->namedParameters(['from' => $from, 'to' => $to]);
        $result = $this->db->query($qs1, $qOpts1)->rows();

        if (count($result) != 2) {
            return response()->json(['failure' => 'Specified airports are invalid'], 404);
        }

        $result = array_merge(...$result); // Flatten result array
        $toFaa = $result["toAirport"];
        $fromFaa = $result["fromAirport"];

        $qs2 = '
            SELECT a.name, s.flight, s.utc, r.sourceairport, r.destinationairport, r.equipment
            FROM `travel-sample` AS r
            UNNEST r.schedule AS s
            JOIN `travel-sample` AS a ON KEYS r.airlineid
            WHERE r.sourceairport = $fromFaa
            AND r.destinationairport = $toFaa
            AND s.day = $dayOfWeek ORDER BY a.name ASC';

        $qOpts2 = $options = new \Couchbase\QueryOptions();
        $leaveDate = new DateTime($request->leave);
        $qOpts2->namedParameters([
            'fromFaa' => $fromFaa,
            'toFaa' => $toFaa,
            'dayOfWeek' => intval($leaveDate->format('w'))
        ]);
        $result = $this->db->query($qs2, $qOpts2)->rows();

        if (count($result) == 0) {
            return response()->json(['failure' => 'No flights found'], 404);
        }

        return response()->json(["data" =>  $result]);
    }
}
