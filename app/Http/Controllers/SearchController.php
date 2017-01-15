<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\OpruutRequest;
use GraphAware\Neo4j\Client\ClientBuilder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

Log::useFiles('php://stdout', config('app.log_level'));





class SearchController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
        
    }





    public function stations(Request $request) {
       
    	$search_term = $request->input('q');
    	$city = $request->input('city');
    	$station_list_encoded = null;
    	$station_list = [];
    	$noStations = false;

    	// dd($r, $r->attributes, $r->request, $r->query(), $r->input(), $r->all(), $r->fullUrl(), $search_term, $city);

    	// only initiate search if search term clicked
    	if ($search_term !== null)
	    {	    	
	    	$key = ($city === null) ? 'search:station:'.$search_term : 'search:city:'.$city.':station:'.$search_term;
	    	
	    
	    	// $station_list_encoded = Redis::lrange($key, 0, 20);
	    	$station_list = json_decode(Redis::get($key), true);

	    	// dd($station_list_encoded, $station_list);

	    	if ($station_list === null) {
	    		// no input on cache, do a databae query and store in cache via Redis list
	    		$neo4j = app('neo4j');

	    		$query = null;
	    		$params = null;
	    		
	    		if ($city === null) {
					$query = "MATCH (s:Station) 
							  USING INDEX s:Station(name) 
							  WHERE s.name STARTS WITH {name} 
							  RETURN s as station
							  LIMIT 20";

	    			$params = ['name' => $search_term];
	    		}
	    		else {
	    			$query = "MATCH (s:Station) 
							  USING INDEX s:Station(name) 
							  WHERE s.name STARTS WITH {name} and s.city={city}
							  RETURN s as station
							  LIMIT 20";

	    			$params = ['name' => $search_term, 'city' => $city];
	    		}

		        $result = $neo4j->run($query, $params);
		        $results = $result->records();

		    //     if (count($results) === 0) 
		    //     {
		    //     	// since no station starts with the search term
		    //     	// lets do a fuzzy match over entire station names
		        	
		    //     	if ($city === null) {
						// $query = "MATCH (s:Station) 
						// 		  USING INDEX s:Station(name) 
						// 		  WHERE s.name CONTAINS {name} 
						// 		  RETURN s as station
						// 		  LIMIT 10";

		    // 			$params = ['name' => $search_term];
		    // 		}
		    // 		else {
		    // 			$query = "MATCH (s:Station) 
						// 		  USING INDEX s:Station(name) 
						// 		  WHERE s.name CONTAINS {name} and s.city={city}
						// 		  RETURN s as station
						// 		  LIMIT 10";

		    // 			$params = ['name' => $search_term, 'city' => $city];
		    // 		}


		    // 		$result = $neo4j->run($query, $params);
		    //     	$results = $result->records();

		    //     	if (count($results) === 0) 
			   //      {
			   //      	// since no station contains the search term
			   //      	// lets do a fuzzy match over city names 
			   //      	// irrespective of city query parameter present or not
			   //      	// and if city query parameter present then skip this step 
			   //      	// and return the empty result
			        	

			   //      	if ($city === null) {
						// 	$query = "MATCH (s:Station) 
						// 			  USING INDEX s:Station(city) 
						// 			  WHERE s.city CONTAINS {name} 
						// 			  RETURN s as station
						// 			  LIMIT 10";

			   //  			$params = ['name' => $search_term];

			   //  			$result = $neo4j->run($query, $params);
			   //      		$results = $result->records();
			   //  		}
			   //  	}
			    			        	
		       // }
		        
		        // dd(['neorj search resutl->recoreds()' => $results, 'count' => count($results)]);
		        
		        $station_list = [];

		        foreach ($result->records() as $record) {
		        	// dd('redis push', $station_list_encoded, $station_list, $record, $record->get('station'), $record->get('station')->value('name'), $record->get('station')->value('id'), $record->get('station')->value('city'));

		        	$station = $record->get('station');
		        	
		        	$isJunction = intval($station->value('isJunction')) === 1 ? ' (J)' : '';
		        	
		        	$line = explode(';', $station->value('line'));
		        	$line = explode('_', $line[0])[0];
		        	$line_final = ucfirst($line).$isJunction;
		        	
		        	$city = $station->get('city');
		        	// Log::debug('city : '.$city);
		        	$ncr_pos = strpos($city, ' ncr');
		        	// Log::debug('ncr_pos : '.$ncr_pos );
		        	
		        	if ($ncr_pos !== false && (substr($city, $ncr_pos + 4) === ' ' || strlen($city) === $ncr_pos + 4)) {
		        		// Log::debug('ncr_pos !== false ');
		        		$city = ucwords(substr($city, 0, $ncr_pos)).strtoupper(substr($city, $ncr_pos, 4)).ucwords(substr($city, $ncr_pos + 4));
		        	}
		        	else {
		        		// Log::debug('ncr_pos === false ');
		        		$city = ucwords($city);
		        	}
		        	
		        	// Log::debug('final city : '.$city);
		        	
		        	$value = ucwords($station->value('name')).', '.$city.' | '.$line_final ; 
	        		
	        		$station_list[$station->value('id')] = $value;
		        }

		        

		        if (count($station_list) > 0)
		        {
		        	// add to general search list
		        	Redis::set('search:station:'.$search_term, json_encode($station_list));
		        	// Redis::lpush('opruut:search:station:'.$search_term, $station_list_encoded);
		        	// Redis::ltrim('opruut:search:station:'.$search_term, 0, 20);
		        	
		        	// if ($city !== null) 
			        // {	
			        // 	// first parse the city term to be in proper format
			        // 	$city = preg_replace('/\s+/', '_', strtolower($city));
			        // 	// add to city wise search list
			        // 	Redis::lpush('opruut:search:city:'.$city.':station:'.$search_term, $station_list_encoded);
			        // 	Redis::ltrim('opruut:search:city:'.$city.':station:'.$search_term, 0, 20);
			        // }
		        
		        }
		        
		      	// dd('redis push', $station_list_encoded, $station_list);


	    		
	    	}
	    	
			// foreach ($station_list_encoded as $station) {
			// 	$station_list[] = json_decode($station);
			// }
			
			if (count($station_list) === 0) {
				$noStations=true;
			} 

		}


		return response()->json(['station_list' => $station_list, 'noStations' => $noStations], 200);

    }



}
