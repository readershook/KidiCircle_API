<?php

namespace App\Jobs\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessContentCSVFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $file_id;
    private $user;



    public function __construct($file_id, $user)
    {
        $this->file_id = $file_id;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //use Pipes here
    }

    public function saveBasicInformation($basicInfo)
    {

    }



    public function uploadFileTos3($file)
    {
        //copy file from 1 s3 folder to other
    }



    public function 

}
