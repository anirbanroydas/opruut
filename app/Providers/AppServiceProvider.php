<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobFailed;
use GraphAware\Neo4j\Client\ClientBuilder;

use App\OpruutRequest;
use App\Events\OpruutCalculated;
use Illuminate\Support\Facades\Log;

Log::useFiles('php://stdout', config('app.log_level'));




class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{



			

		Queue::failing(function (JobFailed $event) {
			// $event->connectionName
			// $event->job
			// $event->exception
			
			Log::error('Job Failed : '.json_encode($event->job).' with exception : '.json_encode($event->exception));

		});  


		// Queue::before(function (JobProcessing $event) {
		// 
		//	  // $event->connectionName
		//    // $event->job
		//    // $event->job->payload()
		// });

		Queue::after(function (JobProcessed $event) {

			// $event->connectionName
			// $event->job
			// $event->job->payload()
			// Log::info('Job Completed : job : resolviName '.var_dump( $event->job->resolveNmae()));
			// Log::info('Job Completed : job : getName '.var_dump( $event->job->getName()));
			$job_queue = $event->job->getQueue();
			Log::info('Job Completed : job : getQueue '.var_dump($job_queue));
			// $job_payload = $event->job->payload();
			// Log::info('Job Completed : job : payload '.var_dump($job_payload));
			// Log::info('Job Completed : job : payload : data '.var_dump($job_payload['data']));
			// Log::info('Job Completed : job : payload : data : commandName '.var_dump($job_payload['data']['commandName']));
			// $command = $job_payload['data']['command'];
			// Log::info('Job Completed : job : payload : data : command '.var_dump($command));
			// Log::info('Job Completed : job : payload : data : command : postition 150'.var_dump(substr($command, 153, -89)));
			// $command_unserialized = unserialize($job_payload['data']['command']);
			// Log::info('Job Completed : job : payload : data : command : model '.var_dump($command_unserialized));
			// Log::info('Job Completed : job : payload : data : command : model '.var_dump($command_unserialized->opruut));
			
			if ($job_queue === 'opruut_processing') {
				$job_payload = $event->job->payload();
				$command = $job_payload['data']['command'];
				$opruut_id = substr($command, 153, -89);
				Log::info('Job Completed : job : payload : data : command : postition 150'.$opruut_id);

				// find the opruut results asscosiated with the opruut
				$opruut = OpruutRequest::with(['user' => function($q1) { 
				
					$q1->select('id', 'name', 'username', 'avatar'); 
				
				}, 'opruut_results'=> function($q2) { 
				
					$q2->orderBy('rank')->select('opruut_request_id', 'stations', 'routes', 'rank', 'station_count', 'interchanges', 'travel_distance', 'travel_time', 'time_factor', 'comfort_factor'); 

				}])
				->select('id', 'user_id', 'source', 'source_id', 'destination', 'destination_id', 'preference', 'city', 'cityImg', 'ride_time', 'created_at')
				->find(intval($opruut_id));
				
				// broadcast request calculated to the requested user
				broadcast(new OpruutCalculated($opruut));
			}
			
		});
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
			
		$this->app->singleton('neo4j', function() {

			// Log::debug('neo4j :  singleton');

			$connection_url = env('NEO4J_PROTOCOL', 'http').'://'.env('NEO4J_USER', 'guest').':'.env('NEO4J_PASSWORD', 'guest').'@'.env('NEO4J_HOST', 'localhost').':'.env('NEO4J_PORT', '7474');

			// Log::debug('neo4j :  connection : '.$connection_url);

			$neo4j_client = ClientBuilder::create() 
							->addConnection(env('NEO4J_PROTOCOL'), $connection_url)
							->setDefaultTimeout(10)
							->build();

			return $neo4j_client;
		
		});

	}
}
