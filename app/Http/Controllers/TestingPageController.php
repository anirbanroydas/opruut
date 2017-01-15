<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\OpruutRequest;
use GraphAware\Neo4j\Client\ClientBuilder;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

Log::useFiles('php://stdout', config('app.log_level'));






class TestingPageController extends Controller
{
    

    public $opruut;

	/**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
        
    }




	public function searchStations(Request $request)
    {
        
    	$r = $request;
    	$search_term = $request->input('q');
    	$city = $request->input('city');
    	$station_list_encoded = null;
    	$station_list = [];
    	$noStations = false;

    	// dd($r, $r->attributes, $r->request, $r->query(), $r->input(), $r->all(), $r->fullUrl(), $search_term, $city);

    	// only initiate search if search term clicked
    	if ($search_term !== null)
	    {	    	
	    	$key = ($city === null) ? 'opruut:search:station:'.$search_term : 'opruut:search:city:'.$city.':station:'.$search_term;
	    
	    	$station_list_encoded = Redis::lrange($key, 0, 10);

	    	// dd($station_list_encoded, $station_list);

	    	if (count($station_list_encoded) === 0) {
	    		// no input on cache, do a databae query and store in cache via Redis list
	    		$neo4j = app('neo4j');

	    		$query = null;
	    		$params = null;
	    		
	    		if ($city === null) {
					$query = "MATCH (s:Station) 
							  USING INDEX s:Station(name) 
							  WHERE s.name STARTS WITH {name} 
							  RETURN s as station
							  LIMIT 10";

	    			$params = ['name' => $search_term];
	    		}
	    		else {
	    			$query = "MATCH (s:Station) 
							  USING INDEX s:Station(name) 
							  WHERE s.name STARTS WITH {name} and s.city={city}
							  RETURN s as station
							  LIMIT 10";

	    			$params = ['name' => $search_term, 'city' => $city];
	    		}

		        $result = $neo4j->run($query, $params);
		        $results = $result->records();

		        if (count($results) === 0) 
		        {
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


		        	// since no station starts with the search term
		        	// lets do a fuzzy match over city names 
		        	// irrespective of city query parameter present or not
		        	// and if city query parameter present then skip this step 
		        	// and return the empty result
		        	

		    //     	if ($city === null) {
						// $query = "MATCH (s:Station) 
						// 		  USING INDEX s:Station(city) 
						// 		  WHERE s.city CONTAINS {name} 
						// 		  RETURN s as station
						// 		  LIMIT 10";

		    // 			$params = ['name' => $search_term];

		    // 			$result = $neo4j->run($query, $params);
		    //     		$results = $result->records();
		    // 		}
			    		
		        	
		        }

		        $res_array = [];
		        
		        // dd(['neorj search resutl->recoreds()' => $results, 'count' => count($results)]);
		        
		        foreach ($result->records() as $record) {
		        	// dd('redis push', $station_list_encoded, $station_list, $record, $record->get('station'), $record->get('station')->value('name'), $record->get('station')->value('id'), $record->get('station')->value('city'));

		        	$station = $record->get('station');
	        		$res = [$station->value('name'), $station->value('id'), $station->value('line'), $station->get('city') ];
	        		$station_list_encoded[] = json_encode($res);
		        }

		        

		        if (count($station_list_encoded) > 0)
		        {
		        	// add to general search list
		        	Redis::lpush('opruut:search:station:'.$search_term, $station_list_encoded);
		        	Redis::ltrim('opruut:search:station:'.$search_term, 0, 10);
		        	
		        	if ($city !== null) 
			        {	
			        	// add to city wise search list
			        	Redis::lpush('opruut:search:city:'.$city.':station:'.$search_term, $station_list_encoded);
			        	Redis::ltrim('opruut:search:city:'.$city.':station:'.$search_term, 0, 10);
			        }
		        
		        }
		        
		      	// dd('redis push', $station_list_encoded, $station_list);


	    		
	    	}
	    	
			foreach ($station_list_encoded as $station) {
				$station_list[] = json_decode($station);
			} 

			if (count($station_list) === 0) {
				$noStations=true;
			} 

		}

		return response()->json(['station_list' => $station_list, 'noStations' => $noStations], 200);

		dd('only redis lrange', $station_list_encoded, $station_list, $noStations, $city);


        $neo4j = app('neo4j');
        
        // $query = "MATCH (n:Station)-[r]->(n2:Station) RETURN n,r,n2 LIMIT 22";
    	
    	$query = "match (s:Station) where s.name contains {name} return s.name as name, s.id as id, s.line as line limit 10;";

        $result = $neo4j->run($query, ['name' => $r->input('q')]);

        $res_array = [];
        
        foreach ($result->records() as $record) {
        		$res = [$record->get('name'), $record->get('id'), $record->get('line')];
        		$res_array[] = json_encode($res);
        }

        Redis::lpush('test:opruut:key:'.$r->input('q'), $res_array);

        dd($neo4j, $result, $result->records(), $res_array);
    }






    public function findOpruut(OpruutRequest $opruut)
    {

    	$this->opruut = $opruut; /// OpruutRequest::find(113);


        $neo4j = app('neo4j');
       

        $query =   "MATCH path=(s:Station)-[:CONNECTED_TO*1..160]-(d:Station) 
                    USING INDEX s:Station(id)
                    USING INDEX d:Station(id)
                    WHERE s.id={source_id} AND d.id={destination_id}
                    RETURN REDUCE(dist=0, r in RELATIONSHIPS(path) | dist + r.distance) AS TravelDistance, NODES(path) as Stations, SIZE(NODES(path)) as StationCount, RELATIONSHIPS(path) as Routes
                    ORDER BY TravelDistance ASC
                    LIMIT 5";

        $params = ["source_id" => $this->opruut->source_id, "destination_id" => $this->opruut->destination_id];
    
        $result = $neo4j->run($query, $params);
        
        $ride_time = $this->opruut->ride_time;
        $preference = $this->opruut->preference;

        $total_city_population_normalized = 2000000;
        $min_city_population_normalized = 1000; // 10000 * 10<- factor

        $interchanges_normalization_factor = 10;
        $seat_comfort_normalization_factor = 10;
        $crowd_comfort_normalization_factor = 5;
        $crowd_comfort_population_normalization_factor = $min_city_population_normalized/$total_city_population_normalized;
        
        $routes_array = [];
        $station_count_array = [];
        $travel_distance_array = [];
        $travel_time_array = [];
        $interchanges_factor_array = [];
        $interchanges_factor_original_array = [];
        $empty_seat_factor_array = [];
        $crowd_factor_array = [];

        $route_no = 0;

        // dd($ride_time);
        
        // dd($result->records());
        
        // $count = 1;

        foreach ($result->records() as $record) {

        	// if ($route_no === 0) {
            	
         //    	$TravelDistance = $record->get('TravelDistance');
         //    	$Stations = $record->get('Stations');
         //    	$StationValues = [];
         //    	$StationCount = $record->get('StationCount');
         //    	$Routes = $record->get('Routes');
         //    	$RoutesValues = [];

         //    	foreach ($Stations as $station) {
         //    		$StationValues[] = $station->values();
         //    	}

         //    	foreach ($Routes as $route) {
         //    		$RoutesValues[] = $route->values();
         //    	}

         //    	dd(['TravelDistance' => $TravelDistance, 'Stations' => $Stations, 'StationCount' => $StationCount, 'Route' => $Routes, 'StationValues' => $StationValues, 'RouteValues' => $RoutesValues]);
        	// }
         

            $Stations = $record->get('Stations');
            $StationValues = [];
            $Routes = $record->get('Routes');
            $RoutesValues = [];
            
            foreach ($Stations as $station) {
        		$StationValues[] = ['id' => $station->value('id'), 'name' => $station->value('name'), 'isJunction' => $station->value('isJunction')];
        	}

        	foreach ($Routes as $route) {
        		$RoutesValues[] = ['line'=> $route->value('line'), 'distance' => $route->value('distance')];
        	}

            $routes_array[] = ['route_no' => $route_no, 'stations' => $StationValues, 'route' => $RoutesValues];
            
            $station_count = $record->get('StationCount');
            $travel_distance = $record->get('TravelDistance');

            $travel_distance_array[] = ['route_no' => $route_no, 'travel_distance' => $travel_distance];
            $station_count_array[] = ['route_no' => $route_no, 'station_count' => $station_count];

            // count the travel time of the route
            $travel_time = $this->travelTime($travel_distance, $station_count);            
            $travel_time_array[] = ['route_no' => $route_no, 'travel_time' => $travel_time];

            // count the interchange stations count of the route
            $interchanges_factor = $this->interchangeFactor($Stations, $station_count, $interchanges_normalization_factor);
            $interchanges_factor_array[] = ['route_no' => $route_no, 'interchanges_factor' => $interchanges_factor];
            $interchanges_factor_original_array[] = [
            	'route_no' => $route_no, 
            	'interchanges_factor' => $interchanges_factor/$interchanges_normalization_factor
            ];
            
            // count the empty seat factor for the route
            $empty_seat_factor = $this->emptySeatFactor($Stations, $Routes, $station_count, $seat_comfort_normalization_factor);
            $empty_seat_factor_array[] = ['route_no' => $route_no, 'empty_seat_factor' => $empty_seat_factor];


            $time = clone $ride_time;
            
            // count the crowd factor for the route
            $crowd_factor = $this->crowdFactor($Stations, $Routes, $station_count, $crowd_comfort_normalization_factor, 
                $crowd_comfort_population_normalization_factor, $time);
            $crowd_factor_array[] = ['route_no' => $route_no, 'crowd_factor' => $crowd_factor];

            // dd($ride_time);
            
            

            $route_no++;
            
        }

        // dd($ride_time);

        // dd($station_count_array, $travel_time_array, $interchanges_factor_array, $empty_seat_factor_array, $crowd_factor_array);

        $comfort_normalized = $this->normalize_comfort_paths($interchanges_factor_array, $empty_seat_factor_array, $crowd_factor_array);
        $travel_time_normalized = $this->normalize_travel_time_paths($travel_time_array);

        // dd($station_count_array, $travel_time_array, $interchanges_factor_array, $empty_seat_factor_array, $crowd_factor_array, 
        //     $comfort_normalized, $travel_time_normalized);

        $time = clone $ride_time;
        $optimized_routes = $this->optimize_routes($comfort_normalized, $travel_time_normalized, $time, $preference);


        // dd($station_count_array, $travel_time_array, $interchanges_factor_array, $empty_seat_factor_array, $crowd_factor_array, 
        //     $comfort_normalized, $travel_time_normalized, $optimized_routes, $routes_array);



        $opruut_result_array = [];


        for ($i = 0; $i < count($routes_array); ++$i) {
        	$route_result = null;

        	$stations_list = $routes_array[$i]['stations'];
        	$routes_list = $routes_array[$i]['route'];
        	$station_count_value = $station_count_array[$i]['station_count'];
        	$interchanges_value = $interchanges_factor_original_array[$i]['interchanges_factor'];
        	$travel_distance_value = $travel_distance_array[$i]['travel_distance'];
        	$travel_time_value = $travel_time_array[$i]['travel_time'];
        	$time_factor_value = $travel_time_normalized[$i]['travel_time_factor'];
        	$comfort_factor_value = $comfort_normalized[$i]['comfort_factor'];

        	$rank = 1;
        	foreach($optimized_routes as $oroute) {

        		if ($oroute['route_no'] === $i) {
        			break;	
        		}

        		$rank++;
	        }
        	


        	$route_result = [
	        	'stations' => $stations_list,
	        	'routes' => $routes_list,
	        	'station_count' => $station_count_value,
	        	'interchanges' => $interchanges_value,
	        	'travel_distance' => $travel_distance_value,
	        	'travel_time' => $travel_time_value,
	        	'time_factor' => $time_factor_value,
	        	'comfort_factor' => $comfort_factor_value,
	        	'rank' => $rank
	        ];


        	
        	// add the oprruut result to table and retrieve the opruut result id
        	$opruut_result_array[] = $this->opruut->add_opruut_result($route_result);

        }
        
     //    $r = 1;
     //    $routes_json = [];

    	// foreach($optimized_routes as $oroute) {

     //    	$routes_json['rank_'.$r] =  $opruut_result_array[$oroute['route_no']]->id;
     //    	$r++;
     //    }


        // add routes json to opruut request
        // $this->opruut->routes = json_encode($routes_json);
        // $this->opruut->routes = $routes_json;
        // $this->opruut->save();
        

        $this->opruut->load('user', 'opruut_results'); 

        // OpruutRequest::with('user', 'opruut_results')->find(intval($opruut_id));
    
        $opruut_result = [
        	'opruut_request' => $this->opruut,
        	'routes' => $routes_array,
	        'station_count' => $station_count_array,
	        'travel_distance' => $travel_distance_array,
	        'travel_time' => $travel_time_array,
	        'interchanges' => $interchanges_factor_original_array,
            'time_factor' => $travel_time_normalized,  
            'comfort_factor' => $comfort_normalized,
            'optimized_routes_sorted' => $optimized_routes,
            'opruut_result_array' => $opruut_result_array
        ];




        $jor = json_encode($opruut_result);

     	dd($opruut_result, json_decode($jor), json_decode($jor, true));




        // $key = 'opruut_request:'.$this->opruut->id;
        // Cache::forever($key, json_encode($opruut_result));
       
    }






    protected function travelTime($travel_distance, $station_count=2)
    {
        $wait_time = ($station_count-1)*30; // in seconds

        $route_time = ($travel_distance/40)*60*60; //in seconds
        
        $total_time = $wait_time + $route_time; // in seconds
        
        $travel_hours = intval($total_time/3600);
        $travel_mins = intval($total_time/60);
        $travel_secs = $total_time%60;

        return ['hours' => $travel_hours, 'mins' => $travel_mins, 'secs' => $travel_secs, 'total_time_secs' => $total_time];
    }




    protected function interchangeFactor($stations, $station_count, $interchanges_normalization_factor)
    {
        $intersectionCount = 0;

        if ($station_count < 3) {
            return $intersectionCount;
        }

        // $stations = $route->nodes();


        for ($i = 0; $i < $station_count - 1; ++$i)
        {
            Log::debug('$i : '.$i.'station : '.$stations[$i]->value('name'));

            if (!isset($stations[$i+2])) {
                Log::debug('$i : '.$i.' station at this node not present');
                // no more possibility of interchangin juntion, since the very next station is the destination junction
                break;
            }
            else {
                // check if the next station is an interchangin junction or not
                // by checking the next to next station's line color
                // if current and next to next stattion's line colors are different
                // it means there is an interchangin junction in between
                // Note the current station can be an interchangin junction itself or
                // may be the next to next station be an interchangin junction
                // in these cases we will check the array of line colors in both the stations
                // and they have to a strict non intersection of colors in both stations' line color arrays
                
                $currentStation = $stations[$i];
                $currentStation_line_colors = explode(';', $currentStation->line);

                $nextToNextStation = $stations[$i+2];
                $nextToNextStation_line_colors = explode(';', $nextToNextStation->line);
                $isIntersection = true;

                Log::debug('currentStationColor : '.implode(" ",$currentStation_line_colors));
                Log::debug('nextToNextStation_line_colors : '.implode(" ",$nextToNextStation_line_colors));
                Log::debug('isIntesrsection before foreach loop: '.$isIntersection);
                Log::debug('intesrsectionCount before foreach loop: '.$intersectionCount);

                foreach ($currentStation_line_colors as $color) 
                {
                    
                    Log::debug('foreach loop current color: '.$color);

                    if (in_array($color, $nextToNextStation_line_colors))
                    {
                        Log::debug('in_array true : current color: ');
                        // there is an intersection of line color, means no interchanging junction in between, hence break
                        $isIntersection = false;
                        break;
                    }
                    else {
                       Log::debug('in_array false : current color: '.$color); 
                    }

                }

                Log::debug('isIntesrsection after foreach loop: '.$isIntersection);

                // now check if there was match of color or not
                if ($isIntersection) 
                {
                    $intersectionCount++;
                }

                Log::debug('intesrsectionCount after foreach loop: '.$intersectionCount);

            }
        }

        Log::debug('intesrsectionCount after for loop: hence returning value : '.$intersectionCount);


        return $intersectionCount*$interchanges_normalization_factor;

    }




    protected function emptySeatFactor($stations, $routes, $station_count, $seat_comfort_normalization_factor)
    {
        // $stations = $route->nodes();
        // $routes = $route->relationships();
        $empty_seat_factor = 0;

        for ($i = 0; $i < $station_count - 1; ++$i)
        {
            Log::debug('$i : '.$i.' current station : '.$stations[$i]->value('name'));
            Log::debug('$i : '.$i.' route : '.json_encode($routes[$i]));
            Log::debug('$i : '.$i.' next station : '.$stations[$i+1]->value('name'));

            if ($stations[$i]->value('id') === $routes[$i]->value('source_id')) {
                Log::debug('$i : '.$i.' source station id : '.$stations[$i]->value('id'));
                Log::debug('$i : '.$i.' route source_id : '.$routes[$i]->value('source_id'));
                Log::debug('$i : '.$i.' route destination_id : '.$routes[$i]->value('destination_id'));
                // direction of route is from source to destination
                // hence empty seat factor is toward destination
                if ($routes[$i]->value('empty_seat_factor_toward_destination') > 0) {
                    // means empty seat factor is positive hence we will consider this factor as constant
                    // throughout end of journey and stop counting any further
                    $empty_seat_factor = $empty_seat_factor 
                    + ($seat_comfort_normalization_factor*($station_count - $i -1));

                    break;
                }
                else {
                    // means empty seat factor is negative hence we will consider this factor and continue adding different
                    // factors till end of journey or until we reach any positive factor
                    $empty_seat_factor = $empty_seat_factor 
                    + -$seat_comfort_normalization_factor;
                }
                
            }
            else {
                Log::debug('$i : '.$i.' source station id : '.$stations[$i]->value('id'));
                Log::debug('$i : '.$i.' route source_id : '.$routes[$i]->value('source_id'));
                Log::debug('$i : '.$i.' route destination_id : '.$routes[$i]->value('destination_id'));
                // direction of route is from destination to source
                // hence empty seat factor is toward source
                if ($routes[$i]->value('empty_seat_factor_toward_source') > 0) {
                    // means empty seat factor is positive hence we will consider this factor as constant
                    // throughout end of journey and stop counting any further
                    $empty_seat_factor = $empty_seat_factor 
                    + ($seat_comfort_normalization_factor*($station_count - $i -1));

                    break;
                }
                else {
                    // means empty seat factor is negative hence we will consider this factor and continue adding different
                    // factors till end of journey or until we reach any positive factor
                    $empty_seat_factor = $empty_seat_factor 
                    + -$seat_comfort_normalization_factor;
                }

            }
        }

        
        Log::debug('iempty seat factor final returning : '.$empty_seat_factor);


        return $empty_seat_factor;

    }






    protected function crowdFactor($stations, $routes, $station_count, $crowd_comfort_normalization_factor, $crowd_comfort_population_normalization_factor,
        $ride_time)
    {
        // $ride_time = $ride_time_immutable;

        // $stations = $route->nodes();
        // $routes = $route->relationships();
        $crowd_factor = 0;

        for ($i = 0; $i < $station_count - 1; ++$i)
        {
            Log::debug('$i : '.$i.' current station : '.$stations[$i]->value('name'));
            Log::debug('$i : '.$i.' route : '.json_encode($routes[$i]));
            Log::debug('$i : '.$i.' ride time : '.$ride_time->toDateTimeString());

            // find crowd factor for current station
            // since crowd factor is directly proportional to IF of current station
            // find IF for current station
            $I_F = $this->importanceFactor($stations[$i], $crowd_comfort_normalization_factor, $crowd_comfort_population_normalization_factor,
                $ride_time);

            $crowd_factor = $crowd_factor + $I_F;
            
            // distnace between current and next station
            $distance = $routes[$i]->value('distance');

            $travel_time = $this->travelTime($distance);
            // dd($travel_time, $travel_time['hours'], $travel_time['mins']);
            // calculate ride time for next station
            $ride_time->addHours($travel_time['hours'])->addMinutes($travel_time['mins'])->addSeconds($travel_time['secs']);

        }

        Log::debug('crowd factor after for loop: hence returning value : '.$crowd_factor);


        return $crowd_factor;

    }



    protected function importanceFactor($station, $crowd_comfort_normalization_factor, $crowd_comfort_population_normalization_factor, 
        $ride_time) 
    {
        // IF is dependent on the following
        // population_avg_ridership_normalized, offices, entertainment, food, education, amenities
        // out the above, requirement of population_avg_ridership_normalized and amenities don't depend on time
        // hence they have a constant multiplier but for the others, their multipliers depend on the ride time and
        // hence calculated based on the polynomial regresession for each of them separately
        $IF_population_avg_ridership_normalized = $station->value('population_avg_ridership_normalized')
                                                    *$crowd_comfort_population_normalization_factor; 
        // not adding the normalization factor since the population itself is large in number, multiplying with normalization factor may give large deviations
        Log::debug('IF factor for population: '.$IF_population_avg_ridership_normalized);

        // multiplyig a normalization factor since amenities may be small in number and have considerable impact
        $IF_amenities = ($station->value('amenities')/1000)*$crowd_comfort_normalization_factor;
        Log::debug('IF factor for population: '.$IF_amenities);

        // calculating the time dependent IF values
        $IF_offices = ($station->value('offices')/1000)*$this->IF_at($ride_time, 'offices', $crowd_comfort_normalization_factor);
        Log::debug('IF factor for IF_offices: '.$IF_offices);
        $IF_entertainment = ($station->value('entertainment')/1000)*$this->IF_at($ride_time, 'entertainment', $crowd_comfort_normalization_factor);
        Log::debug('IF factor for IF_entertainment: '.$IF_entertainment);
        $IF_food = ($station->value('food')/1000)*$this->IF_at($ride_time, 'food', $crowd_comfort_normalization_factor);
        Log::debug('IF factor for IF_food: '.$IF_food);
        $IF_education = ($station->value('education')/1000)*$this->IF_at($ride_time, 'education', $crowd_comfort_normalization_factor);
        Log::debug('IF factor for IF_education: '.$IF_education);

        $IF_combined = $IF_population_avg_ridership_normalized
                        + $IF_amenities
                        + $IF_offices
                        + $IF_entertainment
                        + $IF_food
                        + $IF_education;

         Log::debug('IF fcombined: '.$IF_combined);
        
        Log::debug('IF factor after for station: '.$station->value('name').' is : '.$IF_combined);


        return $IF_combined;

    }





    protected function IF_at($time, $type, $crowd_comfort_normalization_factor) 
    {
        $hour = $time->hour;
        $min = $time->minute;
        $IF_at = 0;

        $ride_time = $hour + $min/60; // in hours   

        if ($type === 'offices')
        {
            $IF_at = $this->polynomialOffices($ride_time);
        }
        else if ($type === 'entertainment')
        {
            $IF_at = $this->polynomialEntertainment($ride_time);
        }
        else if ($type === 'food')
        {
            $IF_at = $this->polynomialFood($ride_time);
        }
        else if ($type === 'education')
        {
            $IF_at = $this->polynomialEducation($ride_time);
        }

        Log::debug('IF factor after for type: '.$type.' at time : '.$time->toDateTimeString().' with IF_at : '.$IF_at);

        $IF_at_normalized = ($IF_at/100)*$crowd_comfort_normalization_factor;
        
        Log::debug('IF factor after for normalization: '.$IF_at_normalized);

        // normalize the value according to crowd normalization factor
        return $IF_at_normalized;
    }




    protected function normalize_comfort_paths($interchanges_factor_array, $empty_seat_factor_array, $crowd_factor_array)
    {
        // minimize interchanges factor
        $min_interchanges_factor = min($interchanges_factor_array)['interchanges_factor'];

        // maximized empyt seat factor
        $max_empty_seat_factor = max($empty_seat_factor_array)['empty_seat_factor'];

        // minimized crowd factor
        $min_crowd_factor = min($crowd_factor_array)['crowd_factor'];

        // dd($interchanges_factor_array, $empty_seat_factor_array, $crowd_factor_array, $min_interchanges_factor, $max_empty_seat_factor, $min_crowd_factor);

        // thus the most optimized combo for comfort maximization
        // is min(interchanges), max(empty_seat), min(crowd)
        // lets denote these 3 as coordinates in 3D Geometry
        // x=>interchanges, y=>empty_seats, z=>crowd
        // Add the deviation of each path coordinates from the optimized coordinates
        $path_comfort_deviations = [];

        // Add the optimized ideal case as the first coordinate point in the array
        $path_comfort_optimized_coordinates = ['x' => $min_interchanges_factor, 'y' => $max_empty_seat_factor, 'z' => $min_crowd_factor];

        for ($i=0; $i < count($interchanges_factor_array); ++$i)
        {
            $deviation = sqrt(

                pow(($path_comfort_optimized_coordinates['x'] - $interchanges_factor_array[$i]['interchanges_factor']), 2)
                + pow(($path_comfort_optimized_coordinates['y'] - $empty_seat_factor_array[$i]['empty_seat_factor']), 2)
                + pow(($path_comfort_optimized_coordinates['z'] - $crowd_factor_array[$i]['crowd_factor']), 2)

            );

            $path_comfort_deviations[] = [ 'deviation' => $deviation, 'route_no' => $i ];

        }

        // $path_comfort_deviations_desc = $path_comfort_deviations;

        // sort the deviations in ascnding order
        // the smallest is the most comfort path since it has the least deviation
        // from the most optimized coordinate
        usort($path_comfort_deviations, function($a, $b) {
            
            if ($a['deviation'] === $b['deviation']) {
                return 0;
            }

            return ($a['deviation'] < $b['deviation']) ? -1 : 1;

        });


        // reverse the deviations kepping the path as constant and just reversing the deviation values
        // now we will asign the most deviation value to least deviation path and 
        // do it in order
        // so that later we can normalize the values out of 100
        for ($i=0, $j=count($path_comfort_deviations) - 1; $i < $j; ++$i, --$j) {
            $temp = $path_comfort_deviations[$i]['deviation'];
            $path_comfort_deviations[$i]['deviation'] = $path_comfort_deviations[$j]['deviation'];
            $path_comfort_deviations[$j]['deviation'] = $temp;
        }

        // find the sum
        $sum = array_reduce($path_comfort_deviations, function($carry, $item) {
            $carry += $item['deviation'];
            return $carry;
        });

        // Give final normalized comfort values out of 100 
        $comfort_normalized_sorted = array_map(function($item) use($sum) {

            return ['route_no' => $item['route_no'], 'comfort_factor' => ($item['deviation']/$sum)*100];

        }, $path_comfort_deviations);

        $comfort_normalized = [];

        for ($i = 0; $i < count($comfort_normalized_sorted); ++$i) {

            $temp = array_filter($comfort_normalized_sorted, function($item) use($i) {
                return $item['route_no'] === $i;
            });

            $comfort_normalized[] = array_values($temp)[0];
        }

        
        return $comfort_normalized;
        
    }





    protected function normalize_travel_time_paths($travel_time_array)
    {

        // find the sum of time in seconds
        $sum = array_reduce($travel_time_array, function($carry, $item) {
            $carry += $item['travel_time']['total_time_secs'];   // in seconds
            return $carry;
        });

        // Give final normalized travel time factor values out of 100 
        $travel_time_normalized = array_map(function($item) use($sum) {

            return ['route_no' => $item['route_no'], 'travel_time_factor' => ($item['travel_time']['total_time_secs']/$sum)*100];

        }, $travel_time_array);

        
        return $travel_time_normalized;

    }



    protected function optimize_routes($comfort_normalized, $travel_time_normalized, $ride_time, $preference)
    {
        $hour = $ride_time->hour;
        $min = $ride_time->minute;

        $ride_time = $hour + $min/60; // in hours 
        

        $preference_travel_time_factor = $this->polynomialPreferenceTravelTime($ride_time);
        $preference_comfort_factor = $this->polynomialPreferenceComfort($ride_time);

        // dd($comfort_normalized, $travel_time_normalized, $ride_time, $preference, 
        //    $preference_travel_time_factor, $preference_comfort_factor);

        // if ($preference === 0) {
        //     // internally make travel time more important and make comfort factor 75% of travel preference irrespective
        //     $preference_comfort_factor = $preference_travel_time_factor*1.2;
        // }

        if ($preference === 2) {
            $preference_travel_time_factor = $preference_travel_time_factor*1.5;
        }
        else if ($preference === 3) {
            $preference_comfort_factor = $preference_comfort_factor*1.5;
        }

        // dd($comfort_normalized, $travel_time_normalized, $ride_time, $preference, 
        //    $preference_travel_time_factor, $preference_comfort_factor);

        $min_travel_time_factor = null;
        $max_comfort_factor = null;

        // if ($preference === 0) {
        //     // i.e no preference
        //     // means normalize using the base comfort factors and travel time factors
            
        //     // minimize travel time factor
        //     $min_travel_time_factor = min($travel_time_normalized);

        //     // maximized comfort factor
        //     $max_comfort_factor = max($comfort_normalized);

        //     dd($comfort_normalized, $travel_time_normalized, $ride_time, $preference, 
        //    $preference_travel_time_factor, $preference_comfort_factor, $min_travel_time_factor, $max_comfort_factor);
            
        // }
        
        // i.e preference factor will influence the min and max normalization factors
        // means the min travel time factor and max comfort factor to be normazlied accordingly
        $maxPreference = max($preference_travel_time_factor, $preference_comfort_factor);

        // minimize travel time factor 
        $min_travel_time_factor_rank = count($travel_time_normalized) - intval(round((count($travel_time_normalized)/$maxPreference)*$preference_travel_time_factor));


        if ($min_travel_time_factor_rank !== 0) {
            $min_travel_time_factor_rank--;
        }
        
        // maximized comfort factor
        $max_comfort_factor_rank = intval(round((count($comfort_normalized)/$maxPreference)*$preference_comfort_factor));

        if ($max_comfort_factor_rank !== 0) {
            $max_comfort_factor_rank--;
        }


        $travel_time_normalized_sorted = $travel_time_normalized;

        // dd($comfort_normalized, $travel_time_normalized, $ride_time, $preference, 
        //     $preference_travel_time_factor, $preference_comfort_factor, $min_travel_time_factor_rank, $max_comfort_factor_rank, $travel_time_normalized_sorted);
        
        // sort the travel time factors in ascnding order
        // the smallest is the most optimized 
        usort($travel_time_normalized_sorted, function($a, $b) {
            
            if ($a['travel_time_factor'] === $b['travel_time_factor']) {
                return 0;
            }

            return ($a['travel_time_factor'] < $b['travel_time_factor']) ? -1 : 1;

        });

        $min_travel_time_factor = null;
        if ($preference === 0 ) {
            $min_travel_time_factor = $travel_time_normalized_sorted[0];
        }
        else {
            $min_travel_time_factor = $travel_time_normalized_sorted[$min_travel_time_factor_rank];  
        }
        

        // dd($comfort_normalized, $travel_time_normalized, $ride_time, $preference, 
        //     $preference_travel_time_factor, $preference_comfort_factor, $min_travel_time_factor_rank, $max_comfort_factor_rank, $travel_time_normalized_sorted, $min_travel_time_factor);


        $comfort_normalized_sorted = $comfort_normalized;

        // sort the comfort factors in ascending order
        // the largest is the most optimized 
        usort($comfort_normalized_sorted, function($a, $b) {
            
            if ($a['comfort_factor'] === $b['comfort_factor']) {
                return 0;
            }

            return ($a['comfort_factor'] < $b['comfort_factor']) ? -1 : 1;

        });

        // since the largest is the most optimized, hence thaking the rank as the position towards the largest
        // the larger the rank the larger is the value
        $max_comfort_factor = null;
        if ($preference === 0 ) {
            $max_comfort_factor = $comfort_normalized_sorted[count($comfort_normalized) - 1];
        }
        else {
            $max_comfort_factor = $comfort_normalized_sorted[$max_comfort_factor_rank];
        }

        // dd($comfort_normalized, $travel_time_normalized, $ride_time, $preference, 
        //     $preference_travel_time_factor, $preference_comfort_factor, $min_travel_time_factor_rank, $max_comfort_factor_rank, $travel_time_normalized_sorted, $min_travel_time_factor, $comfort_normalized_sorted, $max_comfort_factor);
            
        

        // // minimize travel time factor
        // $min_travel_time_factor = min($travel_time_normalized)['travel_time_factor']*$preference_travel_time_factor;

        // // maximized comfort factor
        // $max_comfort_factor = max($comfort_normalized)['comfort_factor']*$preference_comfort_factor;


        // thus the most optimized combo for route
        // is min(travel_time) and max(comfort)
        // lets denote these 2 as coordinates in 2D Geometry
        // x=>travel_time, y=>comfort
        // Add the deviation of each path coordinate from the optimized coordinate
        $optimized_path_deviations = [];

        // Add the optimized ideal case as the first coordinate point in the array
        $optimized_path_coordinates = ['x' => $min_travel_time_factor['travel_time_factor'], 'y' => $max_comfort_factor['comfort_factor']];

        // dd($comfort_normalized, $travel_time_normalized, $ride_time, $preference, 
        //         $preference_travel_time_factor, $preference_comfort_factor, $min_travel_time_factor_rank, $max_comfort_factor_rank, $travel_time_normalized_sorted, $min_travel_time_factor, $comfort_normalized_sorted, $max_comfort_factor, $optimized_path_coordinates);

        for ($i=0; $i < count($travel_time_normalized); ++$i)
        {
            $deviation = sqrt(

                pow(($optimized_path_coordinates['x'] - $travel_time_normalized[$i]['travel_time_factor']), 2)
                + pow(($optimized_path_coordinates['y'] - $comfort_normalized[$i]['comfort_factor']), 2)

            );

            $optimized_path_deviations[] = [ 'deviation' => $deviation, 'route_no' => $i ];

        }

        // dd($comfort_normalized, $travel_time_normalized, $ride_time, $preference, 
        //         $preference_travel_time_factor, $preference_comfort_factor, $min_travel_time_factor, $max_comfort_factor, $optimized_path_coordinates, $optimized_path_deviations);

        // dd($comfort_normalized, $travel_time_normalized, $ride_time, $preference, 
        //         $preference_travel_time_factor, $preference_comfort_factor, $min_travel_time_factor_rank, $max_comfort_factor_rank, $travel_time_normalized_sorted, $min_travel_time_factor, $comfort_normalized_sorted, $max_comfort_factor, $optimized_path_coordinates, $optimized_path_deviations);


        // sort the deviations in ascnding order
        // the smallest is the most optimized path since it has the least deviation
        // from the most optimized coordinate
        usort($optimized_path_deviations, function($a, $b) {
            
            if ($a['deviation'] === $b['deviation']) {
                return 0;
            }

            return ($a['deviation'] < $b['deviation']) ? -1 : 1;

        });

        // dd($comfort_normalized, $travel_time_normalized, $ride_time, $preference, 
        //         $preference_travel_time_factor, $preference_comfort_factor, $min_travel_time_factor_rank, $max_comfort_factor_rank, $travel_time_normalized_sorted, $min_travel_time_factor, $comfort_normalized_sorted, $max_comfort_factor, $optimized_path_coordinates, $optimized_path_deviations);
        


        return $optimized_path_deviations;

    }











    protected function polynomialPreferenceTravelTime($x) 
    {
        
        return -4.3319638850346419e+003 * pow($x,0)
        +  1.3095958881598890e+003 * pow($x,1)
        +  5.8979234815187192e+002 * pow($x,2)
        + -3.6010983876214959e+002 * pow($x,3)
        +  7.0183484094767664e+001 * pow($x,4)
        + -5.5637672567613627e+000 * pow($x,5)
        + -3.9116381994429173e-003 * pow($x,6)
        +  2.7226861773625030e-002 * pow($x,7)
        + -1.1967467469347465e-003 * pow($x,8)
        + -1.0096064394548057e-005 * pow($x,9)
       +  7.9542025485296698e-008 * pow($x,10)
       +  8.5144099665366270e-008 * pow($x,11)
       +  1.7188504284150361e-010 * pow($x,12)
       + -9.2919932655424391e-011 * pow($x,13)
       + -1.4095623149347298e-012 * pow($x,14)
       +  9.2867699140838572e-015 * pow($x,15)
       + -4.8705146793549740e-015 * pow($x,16)
       +  2.5385423119519907e-016 * pow($x,17)
       +  3.8997161985343088e-018 * pow($x,18)
       + -1.3443380325705288e-019 * pow($x,19)
       +  2.8293479993319026e-020 * pow($x,20)
       + -4.4751012394777773e-022 * pow($x,21)
       +  7.8175708291235854e-024 * pow($x,22)
       + -4.4054816064175392e-024 * pow($x,23)
       +  1.1538978022817307e-025 * pow($x,24)
       +  3.2092752476307256e-028 * pow($x,25)
       + -7.2519972100883378e-029 * pow($x,26)
       + -2.3358733189050182e-030 * pow($x,27)
       +  3.0670890509853865e-031 * pow($x,28)
       +  5.3803119752265588e-034 * pow($x,29)
       + -1.1905291278734840e-034 * pow($x,30)
       + -2.7491803793641728e-036 * pow($x,31)
       +  6.0745595005872470e-037 * pow($x,32)
       + -3.4856059053865002e-038 * pow($x,33)
       +  3.3029554375933927e-040 * pow($x,34)
       +  6.5133586183737782e-041 * pow($x,35)
       + -2.2900772169577234e-042 * pow($x,36)
       + -1.1195011958617577e-044 * pow($x,37)
       + -1.7672742544674642e-045 * pow($x,38)
       + -1.4351299755644441e-046 * pow($x,39)
       +  6.2445138577924533e-049 * pow($x,40)
       +  7.1383888301523579e-049 * pow($x,41)
       + -9.9822275578046871e-051 * pow($x,42)
       +  1.0153788829357849e-052 * pow($x,43)
       + -3.5840143489037982e-053 * pow($x,44)
       +  8.2554561461406040e-055 * pow($x,45)
       + -4.7769445428922247e-056 * pow($x,46)
       +  3.9737039244729215e-057 * pow($x,47)
       +  2.8761151120859302e-059 * pow($x,48)
       + -4.7328560421198607e-060 * pow($x,49)
       +  1.1722780803032201e-061 * pow($x,50)
       + -4.5452361903675213e-063 * pow($x,51)
       +  1.3059173539369041e-064 * pow($x,52)
       + -1.3975747751108979e-065 * pow($x,53)
       + -1.9350904850683296e-067 * pow($x,54)
       +  1.1525477654946291e-068 * pow($x,55)
       +  1.4053192414939219e-069 * pow($x,56)
       + -8.9694422463418045e-072 * pow($x,57)
       + -1.7976396301148058e-072 * pow($x,58)
       +  2.7302420182709005e-074 * pow($x,59);

    }



    protected function polynomialPreferenceComfort($x) 
    {
        
          return  3.3717898845788623e+004 * pow($x,0)
        + -2.7350839471643019e+004 * pow($x,1)
        +  8.9307952720147005e+003 * pow($x,2)
        + -1.4494799473724961e+003 * pow($x,3)
        +  1.0860586817030854e+002 * pow($x,4)
        + -6.5900437074051288e-001 * pow($x,5)
        + -3.6746352083354694e-001 * pow($x,6)
        +  6.5368100994107753e-003 * pow($x,7)
        +  1.3039263505003991e-003 * pow($x,8)
        + -4.2012100039396658e-005 * pow($x,9)
       + -1.4862565149334350e-006 * pow($x,10)
       +  5.2347855885645186e-008 * pow($x,11)
       + -1.7769985086453128e-009 * pow($x,12)
       +  2.2058362348161920e-011 * pow($x,13)
       +  1.1701614593097191e-011 * pow($x,14)
       +  3.4966858197783407e-014 * pow($x,15)
       + -2.2198786836990098e-014 * pow($x,16)
       + -4.5101114683296259e-016 * pow($x,17)
       +  2.6201889862954878e-018 * pow($x,18)
       +  7.8643476193004082e-019 * pow($x,19)
       + -1.0664336657296551e-020 * pow($x,20)
       +  3.1707233197852208e-021 * pow($x,21)
       + -7.4696472349686570e-024 * pow($x,22)
       + -2.1086129171492448e-024 * pow($x,23)
       + -1.2748533619592720e-025 * pow($x,24)
       +  7.1115984059810458e-028 * pow($x,25)
       + -1.4792125174623080e-028 * pow($x,26)
       +  2.5946171834912253e-030 * pow($x,27)
       +  4.2698035578813131e-031 * pow($x,28)
       +  8.0434881807996472e-033 * pow($x,29)
       +  2.4173349770008139e-034 * pow($x,30)
       + -4.2537234676620712e-035 * pow($x,31)
       + -3.2216810094117271e-037 * pow($x,32)
       + -1.3350204952648874e-038 * pow($x,33)
       +  9.8041813512599023e-040 * pow($x,34)
       + -4.6920407831235465e-041 * pow($x,35)
       +  5.2274842284753482e-042 * pow($x,36)
       + -2.0491533411034873e-043 * pow($x,37)
       +  4.7107394904783425e-045 * pow($x,38)
       +  1.2652051378666684e-046 * pow($x,39)
       +  1.0475155725064395e-047 * pow($x,40)
       +  8.3376343081108535e-050 * pow($x,41)
       + -1.6755446846454184e-050 * pow($x,42)
       + -8.3462253857554208e-052 * pow($x,43)
       +  4.3922260955031410e-053 * pow($x,44)
       + -2.2893160900787978e-054 * pow($x,45)
       + -1.1063201813457433e-056 * pow($x,46)
       + -2.4464662490550136e-057 * pow($x,47)
       +  1.1951632535316452e-058 * pow($x,48)
       +  3.3919946034658633e-060 * pow($x,49)
       + -1.6938752253262198e-061 * pow($x,50)
       +  7.1226290868708484e-063 * pow($x,51)
       + -2.3170996802518313e-064 * pow($x,52)
       +  1.3890991303733240e-065 * pow($x,53)
       + -4.7268115305223983e-067 * pow($x,54)
       +  4.6965861524628550e-068 * pow($x,55)
       + -1.0348886178628681e-069 * pow($x,56)
       +  4.4208594481025432e-071 * pow($x,57)
       + -1.9928808459464876e-072 * pow($x,58)
       + -6.3577747937169746e-074 * pow($x,59)
       + -3.5988338938723339e-076 * pow($x,60)
       +  1.7258987950932338e-076 * pow($x,61)
       + -5.4595335179809365e-078 * pow($x,62)
       + -1.1291323735325080e-079 * pow($x,63)
       + -7.2013939656942364e-082 * pow($x,64)
       +  1.1238955540152949e-082 * pow($x,65)
       +  3.0684755044654076e-084 * pow($x,66)
       + -8.0505116495969569e-086 * pow($x,67)
       +  3.6969048105643672e-086 * pow($x,68)
       +  3.2984063522702075e-088 * pow($x,69)
       + -8.3667876939403328e-089 * pow($x,70)
       +  1.1570163548518184e-090 * pow($x,71);
       
    }



    protected function polynomialOffices($x) 
    {
        
        return  6.8013661063059699e+003 * pow($x,0)
        + -9.8053004272424550e+003 * pow($x,1)
        +  5.2464276475093993e+003 * pow($x,2)
        + -1.4040698775019230e+003 * pow($x,3)
        +  1.9979845305056946e+002 * pow($x,4)
        + -1.3071413741355386e+001 * pow($x,5)
        + -8.9192724628234951e-002 * pow($x,6)
        +  6.5297265633361370e-002 * pow($x,7)
        + -3.0247924611742352e-003 * pow($x,8)
        +  7.9615255749879027e-006 * pow($x,9)
       + -3.7934886503818004e-007 * pow($x,10)
       +  1.6065639506128978e-007 * pow($x,11)
       +  5.1453211261965305e-010 * pow($x,12)
       + -1.8324653076206037e-010 * pow($x,13)
       + -3.2556095325640709e-012 * pow($x,14)
       +  1.1558147451616800e-013 * pow($x,15)
       + -1.3531434799276410e-014 * pow($x,16)
       +  4.5031350569690090e-016 * pow($x,17)
       +  1.5323688589161150e-017 * pow($x,18)
       + -5.8232265928156081e-019 * pow($x,19)
       +  8.1897991306685200e-020 * pow($x,20)
       + -1.5242471511346530e-021 * pow($x,21)
       +  2.1603668827163925e-023 * pow($x,22)
       + -1.0595281014893396e-023 * pow($x,23)
       +  2.3761345573575952e-025 * pow($x,24)
       + -3.5778544006786184e-028 * pow($x,25)
       + -2.0318366288520791e-029 * pow($x,26)
       + -1.1628066977269816e-030 * pow($x,27)
       +  7.0971089021140669e-031 * pow($x,28)
       + -8.0380810162576804e-033 * pow($x,29)
       + -1.7165293684913254e-034 * pow($x,30)
       + -8.5178101092599037e-036 * pow($x,31)
       +  1.0949666269067865e-036 * pow($x,32)
       + -8.5326914987691205e-038 * pow($x,33)
       +  1.2039526946642506e-039 * pow($x,34)
       +  1.7202282691836617e-040 * pow($x,35)
       + -6.9228131867820851e-042 * pow($x,36)
       + -3.4098354840272168e-044 * pow($x,37)
       + -3.8578953916152038e-045 * pow($x,38)
       + -2.8287305072061645e-046 * pow($x,39)
       +  3.4245608754300316e-048 * pow($x,40)
       +  1.8535774255999977e-048 * pow($x,41)
       + -2.4942060946428959e-050 * pow($x,42)
       +  2.3680100922730933e-052 * pow($x,43)
       + -1.0092394899888809e-052 * pow($x,44)
       +  2.3558972235758531e-054 * pow($x,45)
       + -1.0753081988342381e-055 * pow($x,46)
       +  8.8122805004667382e-057 * pow($x,47)
       +  6.6350134230182373e-060 * pow($x,48)
       + -1.2196032298776714e-059 * pow($x,49)
       +  3.3438518784610147e-061 * pow($x,50)
       + -8.3553355306579045e-063 * pow($x,51)
       +  3.4054614465939784e-064 * pow($x,52)
       + -2.8176755405695963e-065 * pow($x,53)
       + -1.0536205158875201e-067 * pow($x,54)
       +  1.3258911815082093e-068 * pow($x,55)
       +  2.7009643118798918e-069 * pow($x,56)
       + -2.8656439037340552e-071 * pow($x,57)
       + -3.3697051521658890e-072 * pow($x,58)
       +  6.0726971081471591e-074 * pow($x,59);
    }




    protected function polynomialEducation($x) 
    {
        
        return  7.8122348724550015e+004 * pow($x,0)
        + -6.7104980812738650e+004 * pow($x,1)
        +  2.3245415598631131e+004 * pow($x,2)
        + -4.0210585045201333e+003 * pow($x,3)
        +  3.1668286509983750e+002 * pow($x,4)
        +  1.6963000070577898e+000 * pow($x,5)
        + -2.1760568964437734e+000 * pow($x,6)
        +  1.2559664729564946e-001 * pow($x,7)
        +  6.3124968410155097e-004 * pow($x,8)
        + -1.9761315816709923e-004 * pow($x,9)
       + -2.0963767594038569e-006 * pow($x,10)
       +  2.1311480574658113e-007 * pow($x,11)
       +  1.1657208783886174e-008 * pow($x,12)
       + -3.7458265463837411e-010 * pow($x,13)
       + -1.2025147843493060e-011 * pow($x,14)
       +  1.0115324680157131e-012 * pow($x,15)
       + -3.9289574413792012e-014 * pow($x,16)
       + -6.1486071154818189e-016 * pow($x,17)
       +  3.9832425167250104e-017 * pow($x,18)
       + -1.6158896453052280e-018 * pow($x,19)
       +  9.3142272371926455e-020 * pow($x,20)
       +  4.5208348292710507e-021 * pow($x,21)
       + -9.0957440751881178e-023 * pow($x,22)
       + -2.1490298020859147e-024 * pow($x,23)
       + -6.9134457900219383e-026 * pow($x,24)
       +  5.2953521528340111e-027 * pow($x,25)
       + -1.2330696284626465e-027 * pow($x,26)
       +  1.4148388227869298e-029 * pow($x,27)
       + -8.2114084276918103e-031 * pow($x,28)
       +  9.8192232943356310e-032 * pow($x,29)
       +  1.7784538153319622e-033 * pow($x,30)
       + -9.1510668205570690e-035 * pow($x,31)
       +  5.4226705919143540e-036 * pow($x,32)
       + -1.9089314451595865e-037 * pow($x,33)
       + -9.1951008964759275e-039 * pow($x,34)
       + -2.0507125164465574e-040 * pow($x,35)
       +  2.5682607566550037e-041 * pow($x,36)
       + -1.1653691053734529e-044 * pow($x,37)
       + -1.5078623521476336e-044 * pow($x,38)
       + -1.9674564314936170e-046 * pow($x,39)
       +  2.6231695411458056e-050 * pow($x,40)
       +  1.8260270768511212e-048 * pow($x,41)
       + -2.2295875005906125e-050 * pow($x,42)
       + -2.3892386174390946e-051 * pow($x,43)
       +  1.1334911489577303e-052 * pow($x,44)
       + -4.0583862582229293e-054 * pow($x,45)
       +  6.7916787595132015e-056 * pow($x,46);
    }



    protected function polynomialEntertainment($x) 
    {
        
        return  3.9657051833819460e+003 * pow($x,0)
        + -3.0191944648729773e+003 * pow($x,1)
        +  9.1783295294329037e+002 * pow($x,2)
        + -1.3923916023623241e+002 * pow($x,3)
        +  1.0254495083735240e+001 * pow($x,4)
        + -2.0541441547411604e-001 * pow($x,5)
        + -1.2439126411402830e-002 * pow($x,6)
        + -1.7694423082829827e-004 * pow($x,7)
        +  1.1005691644447423e-004 * pow($x,8)
        + -7.6286867800345685e-006 * pow($x,9)
       +  3.0643413287925941e-007 * pow($x,10)
       + -3.1133932234713731e-009 * pow($x,11)
       + -2.0125166886874663e-010 * pow($x,12)
       + -3.2344600912969743e-012 * pow($x,13)
       + -3.1858350050074739e-013 * pow($x,14)
       +  3.3956353749046781e-014 * pow($x,15)
       +  5.3118685811538402e-016 * pow($x,16)
       +  5.0720946885067415e-017 * pow($x,17)
       + -4.2800480523279989e-018 * pow($x,18)
       + -4.4143081498236584e-020 * pow($x,19)
       +  1.5652728821553971e-021 * pow($x,20)
       + -6.2756534010447188e-023 * pow($x,21)
       +  2.6076802518236383e-024 * pow($x,22)
       +  5.4176893375031589e-025 * pow($x,23)
       + -2.4252309653953383e-026 * pow($x,24)
       +  3.6522698005170307e-028 * pow($x,25)
       + -1.0564769842482265e-029 * pow($x,26)
       + -4.7659734632113870e-031 * pow($x,27)
       + -4.5196619138195751e-032 * pow($x,28)
       +  3.3193873081986983e-033 * pow($x,29)
       +  1.1084759558035968e-034 * pow($x,30)
       +  1.9776312146651729e-036 * pow($x,31)
       + -8.1029313311362253e-038 * pow($x,32)
       + -3.0934836036746737e-039 * pow($x,33)
       + -3.4071968460168377e-040 * pow($x,34)
       + -4.2352013043219726e-042 * pow($x,35)
       + -2.6225147081867248e-043 * pow($x,36)
       +  1.4457891454198250e-044 * pow($x,37)
       + -4.2041736534916541e-046 * pow($x,38)
       + -3.9420619885875767e-047 * pow($x,39)
       +  1.3375805818319302e-048 * pow($x,40)
       +  9.0162390309415280e-050 * pow($x,41)
       +  1.1064716883964175e-051 * pow($x,42)
       +  1.2359224720072986e-052 * pow($x,43)
       + -1.7657720387656514e-055 * pow($x,44)
       + -1.1188598027699106e-055 * pow($x,45)
       +  5.1513327647667106e-057 * pow($x,46)
       + -5.7687334119508169e-058 * pow($x,47)
       + -1.9535453291113196e-059 * pow($x,48)
       +  9.4724061436671541e-061 * pow($x,49)
       + -9.0375735645105841e-063 * pow($x,50)
       + -5.9301516510535546e-064 * pow($x,51)
       + -2.7995419097541010e-065 * pow($x,52)
       +  1.5275764748359573e-066 * pow($x,53)
       +  1.2030990453446934e-067 * pow($x,54)
       + -1.5389560515848384e-070 * pow($x,55)
       +  1.7504681680060280e-072 * pow($x,56)
       + -8.6878433776465812e-072 * pow($x,57)
       + -1.6845690076985298e-073 * pow($x,58)
       + -1.1989212972265106e-074 * pow($x,59)
       +  4.6010556224682404e-076 * pow($x,60)
       +  1.2614836478110660e-077 * pow($x,61)
       +  1.8099808042865181e-078 * pow($x,62)
       + -4.6959129172797535e-080 * pow($x,63)
       +  1.6364327707724081e-081 * pow($x,64)
       +  1.1227045698872586e-083 * pow($x,65)
       + -2.7679041241785659e-084 * pow($x,66)
       + -1.3787037289501400e-085 * pow($x,67)
       + -3.0009717341989485e-087 * pow($x,68)
       +  2.0979770793519242e-088 * pow($x,69)
       +  4.1166464165041598e-090 * pow($x,70)
       + -4.5460454895917876e-092 * pow($x,71)
       + -1.1312447516554082e-093 * pow($x,72);
    }




    protected function polynomialFood($x) 
    {
        
        return -1.3663311180704804e+004 * pow($x,0)
        +  8.4339474972131939e+003 * pow($x,1)
        + -1.4569508784490608e+003 * pow($x,2)
        + -1.2178699570771823e+002 * pow($x,3)
        +  7.2669674270958581e+001 * pow($x,4)
        + -9.0081489839566302e+000 * pow($x,5)
        +  3.2402373352683023e-001 * pow($x,6)
        +  1.5448246042006092e-002 * pow($x,7)
        + -8.5531825260390555e-004 * pow($x,8)
        + -3.7794215270718309e-005 * pow($x,9)
       +  1.0608047889601204e-007 * pow($x,10)
       +  1.4902822132625968e-007 * pow($x,11)
       +  2.4224585699125798e-009 * pow($x,12)
       + -1.4790953277898010e-010 * pow($x,13)
       + -8.5322923362185865e-012 * pow($x,14)
       + -6.7040812511719883e-013 * pow($x,15)
       +  2.3951193569639698e-014 * pow($x,16)
       +  1.6353271180631716e-015 * pow($x,17)
       + -6.7869369495664654e-018 * pow($x,18)
       + -1.2947577652231670e-018 * pow($x,19)
       + -4.7295182408135866e-020 * pow($x,20)
       + -2.8968985054691677e-023 * pow($x,21)
       +  1.0264394671257390e-022 * pow($x,22)
       + -2.9696010895838315e-024 * pow($x,23)
       +  7.6567878035899794e-026 * pow($x,24)
       + -6.8956480542074765e-027 * pow($x,25)
       + -1.2505551256071307e-028 * pow($x,26)
       +  1.2543917124035489e-029 * pow($x,27)
       +  1.0184546156826218e-030 * pow($x,28)
       +  5.4518331409576468e-034 * pow($x,29)
       + -2.8202782456223658e-033 * pow($x,30)
       +  8.7227447931680859e-036 * pow($x,31)
       +  6.0986308223991484e-037 * pow($x,32)
       +  3.8566851294881237e-038 * pow($x,33)
       +  5.2850325398494805e-039 * pow($x,34)
       + -6.6589574307375361e-041 * pow($x,35)
       + -1.0076691022665530e-041 * pow($x,36)
       +  1.4817829777526615e-043 * pow($x,37)
       +  1.7131545146145047e-045 * pow($x,38)
       +  1.8936369033281361e-046 * pow($x,39)
       + -3.0917832683475069e-047 * pow($x,40)
       +  1.7170919795203336e-050 * pow($x,41)
       +  1.0613668859194654e-050 * pow($x,42)
       +  7.7763897636139818e-052 * pow($x,43)
       +  4.0498718622091972e-053 * pow($x,44)
       +  2.2121190643110538e-054 * pow($x,45)
       + -7.6322240105831890e-056 * pow($x,46)
       +  1.5456551505041719e-057 * pow($x,47)
       +  1.5275104468376283e-058 * pow($x,48)
       + -2.2307965886969050e-059 * pow($x,49)
       + -3.3682939896553576e-061 * pow($x,50)
       +  9.3212250788293799e-063 * pow($x,51)
       +  1.4488354761654130e-063 * pow($x,52)
       + -1.0039951923474640e-065 * pow($x,53)
       + -6.5803306879952870e-067 * pow($x,54);
    }


}
