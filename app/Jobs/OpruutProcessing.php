<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\OpruutRequest;
use App\Lib\PolynomialsLibrary;
use GraphAware\Neo4j\Client\ClientBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

// Log::useFiles('php://stdout', config('app.log_level'));





class OpruutProcessing implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $opruut;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(OpruutRequest $opruut)
    {
        // $this->opruut = $opruut->load('user');
        $this->opruut = $opruut;
    }



    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Send user notification of failure, etc...
    }





    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        // Log::debug("[OpruutProcessing] handle");
        $neo4j = app('neo4j');
        // Log::debug("[OpruutProcessing] neo4j : ".var_dump($neo4j));
        

        $query =   "MATCH path=(s:Station)-[:CONNECTED_TO*1..150]-(d:Station) 
                    USING INDEX s:Station(id)
                    USING INDEX d:Station(id)
                    WHERE s.id={source_id} AND d.id={destination_id}
                    RETURN REDUCE(dist=0, r in RELATIONSHIPS(path) | dist + r.distance) AS TravelDistance, NODES(path) as Stations, SIZE(NODES(path)) as StationCount, RELATIONSHIPS(path) as Routes
                    ORDER BY TravelDistance ASC
                    LIMIT 4";

        $params = ["source_id" => $this->opruut->source_id, "destination_id" => $this->opruut->destination_id];
    
        $result = $neo4j->run($query, $params);
        
        // convert the ride time to the user's timezone by creating a copy of the ride_time 
        // Carbon instance, Copy is created otherwise the original ride time will change in the database
        // if not given then the applications timezone (config('app.timezone'))
        // or for now a local hard coded timezone 'Asia/Kolkata'
        $timezone = 'Asia/Kolkata';
        $ride_time = $this->opruut->ride_time->copy()->timezone($timezone);
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
        // Log::debug('ride_time : '.var_dump($ride_time));

        foreach ($result->records() as $record) {

            // dd($record->pathValue('Route')->nodes());

            $Stations = $record->get('Stations');
            $StationValues = [];
            $Routes = $record->get('Routes');
            $RoutesValues = [];
            
            foreach ($Stations as $station) {
                $StationValues[] = ['id' => $station->value('id'), 'name' => $station->value('name'), 'isJunction' => $station->value('isJunction')];
            }
            // Log::debug('StationValues : '.var_dump($StationValues));

            foreach ($Routes as $route) {
                $RoutesValues[] = ['line'=> $route->value('line'), 'distance' => $route->value('distance')];
            }
            // Log::debug('RoutesValues : '.var_dump($RoutesValues));

            $routes_array[] = ['route_no' => $route_no, 'stations' => $StationValues, 'route' => $RoutesValues];
            // Log::debug('routes_array : '.var_dump($routes_array));
            
            $station_count = $record->get('StationCount');
            $travel_distance = $record->get('TravelDistance');

            $travel_distance_array[] = ['route_no' => $route_no, 'travel_distance' => $travel_distance];
            $station_count_array[] = ['route_no' => $route_no, 'station_count' => $station_count];
            // Log::debug('travel_distance_array : '.var_dump($travel_distance_array));
            // Log::debug('station_count_array : '.var_dump($station_count_array));

            // count the travel time of the route
            $travel_time = $this->travelTime($travel_distance, $station_count);            
            $travel_time_array[] = ['route_no' => $route_no, 'travel_time' => $travel_time];
            // Log::debug('travel_time_array : '.var_dump($travel_time_array));

            // count the interchange stations count of the route
            $interchanges_factor = $this->interchangeFactor($Stations, $station_count, $interchanges_normalization_factor);
            $interchanges_factor_array[] = ['route_no' => $route_no, 'interchanges_factor' => $interchanges_factor['interchanges_factor'], 'interchanges' => $interchanges_factor['interchanges']];
            $interchanges_factor_original_array[] = [
                'route_no' => $route_no, 
                'interchanges_factor' => $interchanges_factor['interchanges_factor']/$interchanges_normalization_factor,
                'interchanges' => $interchanges_factor['interchanges']
            ];
            // Log::debug('interchanges_factor_array : '.var_dump($interchanges_factor_array));
            // Log::debug('interchanges_factor_original_array : '.var_dump($interchanges_factor_original_array));
            
            // count the empty seat factor for the route
            $empty_seat_factor = $this->emptySeatFactor($Stations, $Routes, $station_count, $seat_comfort_normalization_factor);
            $empty_seat_factor_array[] = ['route_no' => $route_no, 'empty_seat_factor' => $empty_seat_factor];
            // Log::debug('empty_seat_factor_array : '.var_dump($empty_seat_factor_array));

            $time = clone $ride_time;
            
            // count the crowd factor for the route
            $crowd_factor = $this->crowdFactor($Stations, $Routes, $station_count, $crowd_comfort_normalization_factor, 
                $crowd_comfort_population_normalization_factor, $time);
            $crowd_factor_array[] = ['route_no' => $route_no, 'crowd_factor' => $crowd_factor];
            // Log::debug('crowd_factor_array : '.var_dump($crowd_factor_array));

            $route_no++;
            
        }

        // dd($ride_time);

        // dd($station_count_array, $travel_time_array, $interchanges_factor_array, $empty_seat_factor_array, $crowd_factor_array);

        $comfort_normalized = $this->normalize_comfort_paths($interchanges_factor_array, $empty_seat_factor_array, $crowd_factor_array);
        // Log::debug('comfort_normalized : '.var_dump($comfort_normalized));
        
        $travel_time_normalized = $this->normalize_travel_time_paths($travel_time_array);
        // Log::debug('travel_time_normalized : '.var_dump($travel_time_normalized));

        // dd($station_count_array, $travel_time_array, $interchanges_factor_array, $empty_seat_factor_array, $crowd_factor_array, 
        //     $comfort_normalized, $travel_time_normalized);

        $time = clone $ride_time;
        $optimized_routes = $this->optimize_routes($comfort_normalized, $travel_time_normalized, $time, $preference);
        // Log::debug('optimized_routes : '.var_dump($optimized_routes));

        // dd($station_count_array, $travel_time_array, $interchanges_factor_array, $empty_seat_factor_array, $crowd_factor_array, 
        //     $comfort_normalized, $travel_time_normalized, $optimized_routes, $routes_array);


    
        $opruut_result_array = [];


        for ($i = 0; $i < count($routes_array); ++$i) {
            $route_result = null;

            $stations_list = $routes_array[$i]['stations'];
            $routes_list = $routes_array[$i]['route'];
            $station_count_value = $station_count_array[$i]['station_count'];
            $interchanges_value = $interchanges_factor_original_array[$i]['interchanges_factor'];
            $interchanges_stations = $interchanges_factor_original_array[$i]['interchanges'];
            $travel_distance_value = $travel_distance_array[$i]['travel_distance'];
            $travel_time_value = $travel_time_array[$i]['travel_time'];
            $time_factor_value = $travel_time_normalized[$i]['travel_time_factor'];
            $comfort_factor_value = $comfort_normalized[$i]['comfort_factor'];


            // find rank for cuurent route based on optimized routes array
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
                'interchanges_stations' => $interchanges_stations,
                'travel_distance' => $travel_distance_value,
                'travel_time' => $travel_time_value,
                'time_factor' => $time_factor_value,
                'comfort_factor' => $comfort_factor_value,
                'rank' => $rank
            ];
            
            // add the oprruut result to table and retrieve the opruut result id
            $opruut_result_array[] = $this->opruut->add_opruut_result($route_result);

        }
        
        // $r = 1;
        // $routes_json = [];

        // foreach($optimized_routes as $opt_route) {

        //     $routes_json['rank_'.$r] = $opruut_result_array[$opt_route['route_no']]->id ;
        //     $r++;
        // }


        // add routes json to opruut request
        // $this->opruut->routes = $routes_json;
        // $this->opruut->save();

       
    }






    protected function travelTime($travel_distance, $station_count=2)
    {
        $wait_time = ($station_count-1)*20; // in seconds

        $route_time = ($travel_distance/40)*60*60; //in seconds
        
        $total_time = $wait_time + $route_time; // in seconds
        
        $travel_secs = intval($total_time%60);
        $travel_hours = intval(($total_time/60)/60);
        $travel_mins = intval(($total_time/60)%60);
        
        return ['hours' => $travel_hours, 'mins' => $travel_mins, 'secs' => $travel_secs, 'total_time_secs' => $total_time];
    }




    protected function interchangeFactor($stations, $station_count, $interchanges_normalization_factor)
    {
        $intersectionCount = 0;
        $interchanges = [];

        if ($station_count < 3) {
            return $intersectionCount;
        }

        // $stations = $route->nodes();


        for ($i = 0; $i < $station_count - 1; ++$i)
        {
            // Log::debug('$i : '.$i.'station : '.$stations[$i]->value('name'));

            if (!isset($stations[$i+2])) {
                // Log::debug('$i : '.$i.' station at this node not present');
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

                // Log::debug('currentStationColor : '.implode(" ",$currentStation_line_colors));
                // Log::debug('nextToNextStation_line_colors : '.implode(" ",$nextToNextStation_line_colors));
                // Log::debug('isIntesrsection before foreach loop: '.$isIntersection);
                // Log::debug('intesrsectionCount before foreach loop: '.$intersectionCount);

                foreach ($currentStation_line_colors as $color) 
                {
                    
                    // Log::debug('foreach loop current color: '.$color);

                    if (in_array($color, $nextToNextStation_line_colors))
                    {
                        // Log::debug('in_array true : current color: ');
                        // there is an intersection of line color, means no interchanging junction in between, hence break
                        $isIntersection = false;
                        break;
                    }
                    else {
                       // Log::debug('in_array false : current color: '.$color); 
                    }

                }

                // Log::debug('isIntesrsection after foreach loop: '.$isIntersection);

                // now check if there was match of color or not
                if ($isIntersection) 
                {
                    $intersectionCount++;
                    $interchanges[] = $i+1;
                }

                // Log::debug('intesrsectionCount after foreach loop: '.$intersectionCount);

            }
        }

        // Log::debug('intesrsectionCount after for loop: hence returning value : '.$intersectionCount);
        // Log::debug('interchanges station indexes after for loop : '.var_dump($interchanges));


        return ['interchanges' => $interchanges, 'interchanges_factor' => $intersectionCount*$interchanges_normalization_factor];

    }




    protected function emptySeatFactor($stations, $routes, $station_count, $seat_comfort_normalization_factor)
    {
        // $stations = $route->nodes();
        // $routes = $route->relationships();
        $empty_seat_factor = 0;

        for ($i = 0; $i < $station_count - 1; ++$i)
        {
            // Log::debug('$i : '.$i.' current station : '.$stations[$i]->value('name'));
            // Log::debug('$i : '.$i.' route : '.json_encode($routes[$i]));
            // Log::debug('$i : '.$i.' next station : '.$stations[$i+1]->value('name'));

            if ($stations[$i]->value('id') === $routes[$i]->value('source_id')) {
                // Log::debug('$i : '.$i.' source station id : '.$stations[$i]->value('id'));
                // Log::debug('$i : '.$i.' route source_id : '.$routes[$i]->value('source_id'));
                // Log::debug('$i : '.$i.' route destination_id : '.$routes[$i]->value('destination_id'));
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
                // Log::debug('$i : '.$i.' source station id : '.$stations[$i]->value('id'));
                // Log::debug('$i : '.$i.' route source_id : '.$routes[$i]->value('source_id'));
                // Log::debug('$i : '.$i.' route destination_id : '.$routes[$i]->value('destination_id'));
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

        
        // Log::debug('iempty seat factor final returning : '.$empty_seat_factor);


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
            // Log::debug('$i : '.$i.' current station : '.$stations[$i]->value('name'));
            // Log::debug('$i : '.$i.' route : '.json_encode($routes[$i]));
            // Log::debug('$i : '.$i.' ride time : '.$ride_time->toDateTimeString());

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

        // Log::debug('crowd factor after for loop: hence returning value : '.$crowd_factor);


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
        // Log::debug('IF factor for population: '.$IF_population_avg_ridership_normalized);

        // multiplyig a normalization factor since amenities may be small in number and have considerable impact
        $IF_amenities = ($station->value('amenities')/1000)*$crowd_comfort_normalization_factor;
        // Log::debug('IF factor for population: '.$IF_amenities);

        // calculating the time dependent IF values
        $IF_offices = ($station->value('offices')/1000)*$this->IF_at($ride_time, 'offices', $crowd_comfort_normalization_factor);
        // Log::debug('IF factor for IF_offices: '.$IF_offices);
        $IF_entertainment = ($station->value('entertainment')/1000)*$this->IF_at($ride_time, 'entertainment', $crowd_comfort_normalization_factor);
        // Log::debug('IF factor for IF_entertainment: '.$IF_entertainment);
        $IF_food = ($station->value('food')/1000)*$this->IF_at($ride_time, 'food', $crowd_comfort_normalization_factor);
        // Log::debug('IF factor for IF_food: '.$IF_food);
        $IF_education = ($station->value('education')/1000)*$this->IF_at($ride_time, 'education', $crowd_comfort_normalization_factor);
        // Log::debug('IF factor for IF_education: '.$IF_education);

        $IF_combined = $IF_population_avg_ridership_normalized
                        + $IF_amenities
                        + $IF_offices
                        + $IF_entertainment
                        + $IF_food
                        + $IF_education;

         // Log::debug('IF fcombined: '.$IF_combined);
        
        // Log::debug('IF factor after for station: '.$station->value('name').' is : '.$IF_combined);


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
            $IF_at = PolynomialsLibrary::polynomialOffices($ride_time);
        }
        else if ($type === 'entertainment')
        {
            $IF_at = PolynomialsLibrary::polynomialEntertainment($ride_time);
        }
        else if ($type === 'food')
        {
            $IF_at = PolynomialsLibrary::polynomialFood($ride_time);
        }
        else if ($type === 'education')
        {
            $IF_at = PolynomialsLibrary::polynomialEducation($ride_time);
        }

        // Log::debug('IF factor after for type: '.$type.' at time : '.$time->toDateTimeString().' with IF_at : '.$IF_at);

        $IF_at_normalized = ($IF_at/100)*$crowd_comfort_normalization_factor;
        
        // Log::debug('IF factor after for normalization: '.$IF_at_normalized);

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
        // Log::debug('path_comfort_optimized_coordinates : '.var_dump($path_comfort_optimized_coordinates));

        for ($i=0; $i < count($interchanges_factor_array); ++$i)
        {
            $deviation = sqrt(

                pow(($path_comfort_optimized_coordinates['x'] - $interchanges_factor_array[$i]['interchanges_factor']), 2)
                + pow(($path_comfort_optimized_coordinates['y'] - $empty_seat_factor_array[$i]['empty_seat_factor']), 2)
                + pow(($path_comfort_optimized_coordinates['z'] - $crowd_factor_array[$i]['crowd_factor']), 2)

            );

            // Log::debug($i.' : deviation : '.$deviation);

            $path_comfort_deviations[] = [ 'deviation' => $deviation, 'route_no' => $i ];
            // Log::debug($i.' : path_comfort_deviations : '.var_dump($path_comfort_deviations));

        }

        // Log::debug('path_comfort_deviations : '.var_dump($path_comfort_deviations));

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

        // Log::debug('after usort : path_comfort_deviations : '.var_dump($path_comfort_deviations));

        // reverse the deviations kepping the path as constant and just reversing the deviation values
        // now we will asign the most deviation value to least deviation path and 
        // do it in order
        // so that later we can normalize the values out of 100
        for ($i=0, $j=count($path_comfort_deviations) - 1; $i < $j; ++$i, --$j) {
            // Log::debug('i : '.$i.' j : '.$j.' for loop');
            
            $temp = $path_comfort_deviations[$i]['deviation'];
            // Log::debug('$temp : '.var_dump($temp));
            $path_comfort_deviations[$i]['deviation'] = $path_comfort_deviations[$j]['deviation'];
            $path_comfort_deviations[$j]['deviation'] = $temp;

            // Log::debug('after iteration : ', $path_comfort_deviations);
        }

        // Log::debug('after forloop final : '.var_dump($path_comfort_deviations));

        // find the sum
        $sum = array_reduce($path_comfort_deviations, function($carry, $item) {
            $carry += $item['deviation'];
            return $carry;
        });

        // Log::debug('[normalize_comfort_paths] $sum : '.$sum);
        // Give final normalized comfort values out of 100 
        // $comfort_normalized_sorted = array_map(function($item) use($sum) {
        //     // Log::debug('$sum inside array_map : '.$sum);
        //     if ($sum !== 0) {
        //         // Log::debug('$sum !== 0 hence doing /$sum : '.$sum);
        //         return ['route_no' => $item['route_no'], 'comfort_factor' => ($item['deviation']/$sum)*100];
        //     }
        //     else {
        //         // Log::debug('$sum === 0 hence not doing /$sum, just returning: '.$sum);
        //         return ['route_no' => $item['route_no'], 'comfort_factor' => $item['deviation']];
        //     }

        // }, $path_comfort_deviations);
        

        $comfort_normalized_sorted = array_map(function($item) use($sum) {
            // Log::debug('sum inside array_map : '.$sum);
            if ($sum === 0.0) {
                // Log::debug('sum === 0, hence not returning /sum ');
                return ['route_no' => $item['route_no'], 'comfort_factor' => $item['deviation']];
            }
            else {
                // Log::debug('sum !== 0, hence returning /sum');
                return ['route_no' => $item['route_no'], 'comfort_factor' => ($item['deviation']/$sum)*100];
            }

        }, $path_comfort_deviations);

        // Log::debug('comfort_normalized_sorted: '.var_dump($comfort_normalized_sorted)); 
        
        $comfort_normalized = [];

        for ($i = 0; $i < count($comfort_normalized_sorted); ++$i) {

            $temp = array_filter($comfort_normalized_sorted, function($item) use($i) {
                return $item['route_no'] === $i;
            });

            $comfort_normalized[] = array_values($temp)[0];
        }

        // Log::debug('comfort_normalized: '.var_dump($comfort_normalized)); 
        
        return $comfort_normalized;
        
    }





    protected function normalize_travel_time_paths($travel_time_array)
    {

        // find the sum of time in seconds
        $sum = array_reduce($travel_time_array, function($carry, $item) {
            $carry += $item['travel_time']['total_time_secs'];   // in seconds
            return $carry;
        });

        // Log::debug('[normalize_travel_time_paths] $sum : '.$sum);

        // Give final normalized travel time factor values out of 100 
        $travel_time_normalized = array_map(function($item) use($sum) {
            // Log::debug('$sum inside array_map : '.$sum);
            if ($sum === 0.0) {
                // Log::debug('$sum === 0 hence not doing /$sum, just returning: '.$sum);
                return ['route_no' => $item['route_no'], 'travel_time_factor' => $item['travel_time']['total_time_secs']];
            }
            else {
                
                // Log::debug('$sum !== 0 hence doing /$sum : '.$sum);
                return ['route_no' => $item['route_no'], 'travel_time_factor' => ($item['travel_time']['total_time_secs']/$sum)*100];
            }

        }, $travel_time_array);

        
        return $travel_time_normalized;

    }



    protected function optimize_routes($comfort_normalized, $travel_time_normalized, $ride_time, $preference)
    {
        $hour = $ride_time->hour;
        $min = $ride_time->minute;

        $ride_time = $hour + $min/60; // in hours 
        

        $preference_travel_time_factor = PolynomialsLibrary::polynomialPreferenceTravelTime($ride_time);
        $preference_comfort_factor = PolynomialsLibrary::polynomialPreferenceComfort($ride_time);
        // Log::debug('[optimize_routes] preference_travel_time_factor : '.var_dump($preference_travel_time_factor));
        // Log::debug('[optimize_routes] preference_comfort_factor : '.var_dump($preference_comfort_factor));
        // Log::debug('[optimize_routes] preference : '.var_dump($preference));

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
        // Log::debug('[optimize_routes] after checking preference preference_travel_time_factor : '.var_dump($preference_travel_time_factor));
        // Log::debug('[optimize_routes] after checking preference preference_comfort_factor : '.var_dump($preference_comfort_factor));

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
        // Log::debug('[optimize_routes] maxPreference : '.var_dump($maxPreference));

        if ($maxPreference === 0) {
            $maxPreference = 1;
        }

        // Log::debug('[optimize_routes] after checkin for zero value maxPreference : '.var_dump($maxPreference));
        
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




}
