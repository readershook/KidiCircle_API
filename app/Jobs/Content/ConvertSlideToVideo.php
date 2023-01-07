<?php

namespace App\Jobs\Content;

use App\Models\Contents;
use App\Models\ContentSlides;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ConvertSlideToVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */


    private $slide_id;

    public function __construct($slide_id)
    {
        $this->slide_id = $slide_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $slides = ContentSlides::where([
            'id' => $this->slide_id
        ])->first();
        
        $folder = "slide_".$this->slide_id."_".uniqid();
        $directory = "/$folder/";
        $storage_path = storage_path();
        $input = "action=merge&resource_id=$folder&storage_path=$storage_path";

        $allSlides = json_decode($slides->slides, true);
        foreach ($allSlides as $key => $value) {
            Storage::disk('local')->put($directory.$value["sequence"].".mp3", file_get_contents($value["audio_file"]));
            Storage::disk('local')->put($directory.$value["sequence"].".jpg", file_get_contents($value["media_file"]));
        }
        $app_path = app_path();
        // var_dump(storage_path());
        $command = "/usr/bin/php $app_path/Http/Controllers/v1/Ffmpeg.php \"$input\"";
        // echo $command;exit;
        exec ($command);
        // sleep(5);
        $path = $directory."final.mp4";
        $fileExists = Storage::disk('local')->exists($path);
        $local_path = Storage::url('app'.$path);
        if($fileExists){
            $content = Contents::where([
                'id' => $slides->content_id,
            ])->first();
            if($content && empty($content->media_url)){
                $content->media_url = $local_path;
                $content->save();
            }
        }
    }
}
