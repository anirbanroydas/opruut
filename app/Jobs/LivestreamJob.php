<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Livestream;
use Illuminate\Support\Facades\Log;

Log::useFiles('php://stdout', config('app.log_level'));




class LivestreamJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;


    public $livestream;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $livestream)
    {
        $this->livestream = $livestream;
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
        $livestream = Livestream::create($this->livestream);
    }
}
