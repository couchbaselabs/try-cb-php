<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  \Couchbase\SearchQuery as SearchQuery;


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
     * Note that in any case the number of results is limited to 100 entries.
     *
     * @param string $description text to match in 'description' and 'name' fields of the hotel
     * @param string $location text to match in 'country', 'city', 'state' and 'address' fields of the hotel
     *
     * @return array list of the arrays, with 'address', 'name' and 'description' fields filled in
     */
    protected function findHotels($description = "", $location = "") {

        $queryBody = [SearchQuery::term("hotel")->field("type"),];

        if (!empty($location) && $location != "*") {
            $qLoc = SearchQuery::disjuncts(
                SearchQuery::match($location)->field("country"),
                SearchQuery::match($location)->field("city"),
                SearchQuery::match($location)->field("state"),
                SearchQuery::match($location)->field("address")
            );
            array_push($queryBody, $qLoc);
        }

        if (!empty($description) && $description != "*") {
            $qDesc = SearchQuery::disjuncts(
                SearchQuery::match($description)->field("description"),
                SearchQuery::match($description)->field("name")
            );
            array_push($queryBody, $qDesc);
        }

        $query = new SearchQuery('hotels',SearchQuery::conjuncts(...$queryBody));
        $query->limit(100);

        // This causes a seg-fault if the index doesn't exist. TODO: Check exists first
        $result = $this->db->searchQuery('hotels',$query);

        $response = array();
        foreach($result->hits() as $hit) {
            // var_dump($hit);
            $result = $this->collection->lookupIn($hit["id"], [
                new \Couchbase\LookupGetSpec("country"),
                new \Couchbase\LookupGetSpec("city"),
                new \Couchbase\LookupGetSpec("state"),
                new \Couchbase\LookupGetSpec("address"),
                new \Couchbase\LookupGetSpec("name"),
                new \Couchbase\LookupGetSpec("description")
            ]);

            $response[] = [
                "address" => join(',', [
                    $result->content(3),
                    $result->content(2),
                    $result->content(1),
                    $result->content(0),
                ]),
                "name" => $result->content(4),
                "description" => $result->content(5),
            ];
        }

        return $response;
    }

}
