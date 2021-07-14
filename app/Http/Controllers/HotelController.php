<?php

namespace App\Http\Controllers;

use Couchbase\LookupGetSpec;
use Couchbase\MatchSearchQuery;
use Couchbase\TermSearchQuery;
use Couchbase\ConjunctionSearchQuery;
use Couchbase\DisjunctionSearchQuery;
use Couchbase\SearchOptions;
use Illuminate\Http\Request;


class HotelController extends CouchbaseController
{
    public function find(Request $request)
    {
        return response()->json($this->findHotels());
    }

    public function findByDescription(Request $request, $description)
    {
        return response()->json($this->findHotels($description));
    }

    public function findByDescriptionLocation(Request $request, $description, $location)
    {
        return response()->json($this->findHotels($description, $location));
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
    protected function findHotels($description = "", $location = "")
    {
        $queryBody = [(new TermSearchQuery("hotel"))->field("type")];

        if (!empty($location) && $location != "*") {
            $qLoc = new DisjunctionSearchQuery([
                (new MatchSearchQuery($location))->field("country"),
                (new MatchSearchQuery($location))->field("city"),
                (new MatchSearchQuery($location))->field("state"),
                (new MatchSearchQuery($location))->field("address")
            ]);
            array_push($queryBody, $qLoc);
        }

        if (!empty($description) && $description != "*") {
            $qDesc = new DisjunctionSearchQuery([
                (new MatchSearchQuery($description))->field("description"),
                (new MatchSearchQuery($description))->field("name")
            ]);
            array_push($queryBody, $qDesc);
        }

        $opts = new SearchOptions();
        $opts->limit(100);
        $conjunctionQuery = new ConjunctionSearchQuery($queryBody);
        $result = $this->cluster->searchQuery('hotels-index', $conjunctionQuery, $opts);

        $response = array();
        $scope = $this->bucket->scope("inventory");
        $collection = $scope->collection("hotel");
        $lookupFields = ["country", "city", "state", "address", "name", "description"];
        foreach ($result->rows() as $row) {
            $result = $collection->lookupIn($row["id"], [
                new LookupGetSpec($lookupFields[0]),
                new LookupGetSpec($lookupFields[1]),
                new LookupGetSpec($lookupFields[2]),
                new LookupGetSpec($lookupFields[3]),
                new LookupGetSpec($lookupFields[4]),
                new LookupGetSpec($lookupFields[5])
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

        $context = [sprintf(
            "FTS search - scoped to: %s.hotel within fields %s",
            $scope->name(),
            implode(" ", $lookupFields)
        )];
        return ["data" => $response, "context" => $context];
    }

}
