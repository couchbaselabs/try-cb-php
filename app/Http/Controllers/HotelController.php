<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  \CouchbaseSearchQuery as SearchQuery;


class HotelController extends CouchbaseController
{

    function findHotels($description = "", $location = "") {

        $term_query = SearchQuery::term("hotel")->field("type");
        $location_query = NULL;
        $description_query = NULL;

        if (!empty($location) && $location != "*") {
            $location_query = SearchQuery::disjuncts(array(
                SearchQuery::match($location)->field("country"),
                SearchQuery::match($location)->field("city"),
                SearchQuery::match($location)->field("state"),
                SearchQuery::match($location)->field("address")
            ));
        }

        if (!empty($description) && $description != "*") {
            $description_query = SearchQuery::disjuncts(array(
                SearchQuery::match($description)->field("description"),
                SearchQuery::match($description)->field("name")
            ));

        }

        $query = $term_query;
        if (!is_null($location_query) && !is_null($description_query)) {
            $query = new \CouchbaseConjunctionSearchQuery(array($term_query, $location_query, $description_query));
        } else if (!is_null($description_query) && is_null($location_query)) {
            $query = new \CouchbaseConjunctionSearchQuery(array($term_query, $description_query));
        }

        $query = new SearchQuery("travel-search", $query);
        $result = $this->db->query($query);

        $hits = $result->hits;

        $response = array();

        foreach($hits as $hit) {
            $info = $this->db->lookupIn($hit->id)
                ->get("country")
                ->get("city")
                ->get("state")
                ->get("address")
                ->get("name")
                ->get("description")
                ->execute();
            $hotel = (object) ["address" => $info->value[3]["value"].",".$info->value[2]["value"].","
                .$info->value[1]["value"].",".$info->value[0]["value"],
            "name" => $info->value[4]["value"],
            "description" => $info->value[5]["value"]];
            $response[] = $hotel;
        }

        return $response;
    }

    public function find(Request $request)
    {
        $hits = $this->findHotels();
        return response()->json(["data" => $hits]);
    }

    public function find_by_description(Request $request, $description)
    {
        $hits = $this->findHotels($description);
        return response()->json(["data" => $hits]);
    }

    public function find_by_description_location(Request $request, $description, $location)
    {
        $hits = $this->findHotels($description, $location);
        return response()->json(["data" => $hits]);
    }
}
