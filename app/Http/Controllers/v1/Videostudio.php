<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Filters\Video\VideoFilters;
// use FFMpeg\Filters\AdvancedMedia\ComplexFilters;
use ProtoneMedia\LaravelFFMpeg\Exporters\EncodingException;
use ProtoneMedia\LaravelFFMpeg\Filesystem\Media;
class Videostudio extends Controller
{
    public function acquire(Request $request)
    {
        return \Response::make(['resource_id'=>uniqid(), 'message'=>'Resource id created succesfully']);
    }

    public function upload(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'resource_id' => 'required|string',
            'sequence_id' => 'required|int',
            'file' => 'required|file|mimes:jpg,mp3,mp4',
        ]);
        if ($validator->fails())
        {
            return \Response::make([
                'message'     => $validator->errors(),
                'status_code' => 422,
            ], 422);
        }
        $user = auth()->user();
        $directory = $request->resource_id;
        if(!Storage::exists($directory)){
            Storage::makeDirectory($directory);
        }
        $path = "/".$directory."/".$request->sequence_id.".".$request->file('file')->extension();
        Storage::disk('local')->put($path, file_get_contents($request->file->getRealPath()));
        return \Response::make(['message'=>'Uploaded succesfully']);
    }

    public function merge(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'resource_id' => 'required|string',
        ]);
        if ($validator->fails())
        {
            return \Response::make([
                'message'     => $validator->errors(),
                'status_code' => 422,
            ], 422);
        }
        $resourceId = $request->resource_id;
        $input = "action=merge&resource_id=$resourceId";
        $user = auth()->user();
        $command = "clear && /usr/bin/php /home/vipul/project/Kidi_API/app/Http/Controllers/v1/Ffmpeg.php \"$input\" >/dev/null &";
        exec ($command);
        return \Response::make(['message'=>'Process inititated','command'=>$command]);
    }

    public function status(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'resource_id' => 'required|string',
        ]);
        if ($validator->fails())
        {
            return \Response::make([
                'message'     => $validator->errors(),
                'status_code' => 422,
            ], 422);
        }
        $directory = $request->resource_id;
        $path = "/".$directory."/final.mp4";
        
        $user = auth()->user();
        $fileExists = Storage::disk('local')->exists($path);
        $local_path = Storage::url('app'.$path);

        if($fileExists)
            return \Response::make(["message"=>"File created succesfully","fileExists"=>$fileExists,"url"=>$local_path]);

        return \Response::make(["message"=>"File generation is in progress","fileExists"=>$fileExists]);
    }
}