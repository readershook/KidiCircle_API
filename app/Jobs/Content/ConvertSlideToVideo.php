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
            
            $audiofile = $directory.$value["sequence"].".mp3";
            
            if(isset($value["image_duration"]) && (int)$value["image_duration"]>0){
                
                Storage::disk('local')->put($audiofile, file_get_contents($value["audio_file"]));
                $precise_audio_length = exec("ffprobe -show_streams -select_streams a -v quiet $storage_path/app$audiofile | grep \"duration=\" | cut -d '=' -f 2");
                $audio_length = round($precise_audio_length);
                $diff = (int)$value["image_duration"] - $precise_audio_length;
                if($diff>0){
                    $time = gmdate("H:i:s", (int)$value["image_duration"]);
                    $imaudiofile = str_replace($value["sequence"].".mp3", $value["sequence"]."_v1.mp3", $audiofile);
                    Storage::copy($audiofile, $imaudiofile);
                    $command = "ffmpeg -y -i $storage_path/app$imaudiofile -vcodec copy -af apad -ss 00:00:00.000 -t $time $storage_path/app$audiofile";
                    // echo "command= $command";
                    exec($command);
                    unlink("$storage_path/app$imaudiofile");
                }
                // exit;
            }
            else{
                Storage::disk('local')->put($audiofile, file_get_contents($value["audio_file"]));
            }
            Storage::disk('local')->put($directory.$value["sequence"].".jpg", file_get_contents($value["media_file"]));
        }
        // exit;
        $app_path = app_path();
        $command = "/usr/bin/php $app_path/Http/Controllers/v1/Ffmpeg.php \"$input\"";
        exec ($command);
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
        Storage::deleteDirectory($folder);
    }
}
