<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  \CouchbaseSearchQuery as SearchQuery;


class HotelController extends CouchbaseController
{
    public function find(Request $request)
    {
        return response()->json(["data" => $this->findHotels()]);
    }

    public function findByDescription(Request $request, $description)
    {
        return response()->json(["data" => $this->findHotels($description)]);
    }

    public function findByDescriptionLocation(Request $request, $description, $location)
    {
        return response()->json(["data" => $this->findHotels($description, $location)]);
    }

    /**
     * Performs full-text search for given criteria.
     *
     * If neither of criteria specified (or set to "*"), this function lists all hotels in the search index.
     * Note that in any case number of the results limited to 100 entries.
     *
     * @param string $description text to match in 'description' and 'name' fields of the hotel
     * @param string $location text to match in 'country', 'city', 'state' and 'address' fields of the hotel
     *
     * @return array list of the arrays, with 'address', 'name' and 'description' fields filled in
     */
    protected function findHotels($description = "", $location = "") {
        $queryBody = SearchQuery::conjuncts(SearchQuery::term("hotel")->field("type"));

        if (!empty($location) && $location != "*") {
            $queryBody->every(SearchQuery::disjuncts(
                SearchQuery::match($location)->field("country"),
                SearchQuery::match($location)->field("city"),
                SearchQuery::match($location)->field("state"),
                SearchQuery::match($location)->field("address")
            ));
        }

        if (!empty($description) && $description != "*") {
            $queryBody->every(SearchQuery::disjuncts(
                SearchQuery::match($description)->field("description"),
                SearchQuery::match($description)->field("name")
            ));
        }

        $query = new SearchQuery("travel-search", $queryBody);
        $query->limit(100);
        $result = $this->db->query($query);

        $response = array();
        foreach($result->hits as $hit) {
            $info = $this->db->lookupIn($hit->id)
                ->get("country")
                ->get("city")
                ->get("state")
                ->get("address")
                ->get("name")
                ->get("description")
                ->execute();
            $response[] = [
                "address" => join(',', [
                    $info->value[3]["value"],
                    $info->value[2]["value"],
                    $info->value[1]["value"],
                    $info->value[0]["value"]
                ]),
                "name" => $info->value[4]["value"],
                "description" => $info->value[5]["value"]
            ];
        }

        return $response;
    }

}
