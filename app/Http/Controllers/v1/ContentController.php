<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\{Contents, ContentCategories, TempFileLogs, ContentSlides};
use App\Jobs\Content\ConvertSlideToVideo;
class ContentController extends Controller
{
    protected function save(Request $request)
    {

        $request->validate([
            'title' => 'required|max:200',
            'description' => 'required|max:500',
            'language' => 'required|max:2',
            'is_global' => 'required|boolean',
            'cover_image' => 'required|file|max:2048|mimes:jpg,jpeg,png',
        ]);
        $user = auth()->user();
        $content = Contents::store($request->all(), $user);


        if (!empty($request->tags)) {
            $tags = explode(',', $request->tags);
            foreach ($tags as $tag) {
                $cc = ContentCategories::updateOrCreate([
                    'content_id' => $content->id,
                    'category_id'=> $tag
                ]);
                $cc->save();
            }
        }
        $file_path = __('files_path.content.cover_image', [
            "content_id" => $content->id,
            "file_name" => $request->cover_image->getClientOriginalName(),
        ]);
        \Storage::disk('s3')->put($file_path, file_get_contents($request->cover_image->getRealPath()));
        
        $content->cover_image = $file_path;
        $content->save();

        ConvertSlideToVideo::dispatch($slides->id);


        return response()->json([
            'status'=>200,
            'content'=>[
                'id'=>$content->id
            ]
        ]);
    }


    protected function uploadAssets(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,gif,png,mp3,mp4,wav,mov,flv,avi,WebM,mkv',
            'content_id' => 'required|exists:contents,id',
        ]);

        // $user = auth()->user();
        try {
            $file_path = __('files_path.content.temp_assets', [
                "content_id" => $request->content_id,
                "file_name" => $request->file->getClientOriginalName(),
            ]);
            \Storage::disk('s3')->put($file_path, file_get_contents($request->file->getRealPath()));
            // $data =  TempFileLogs::create(['file_path'=>$file_path]);
            return response()->json([
                'file_path'=>$file_path,
                'message'=>'file uploaded succesfully'
            ]);

        } catch (\Exception $e) {

             return response()->json([
                'message'     => 'Internal server error',
                'status_code' => 500,
            ], 500);
            
        }
    }


    function saveSlides(Request $request, $content_id)
    {
        $request->validate([
            'slides'=> 'required|array|min:1'
        ]);
        $user = auth()->user();
        $slides = ContentSlides::where([
            'content_id' => $content_id,
            'created_by' => $user->id
        ])->first();

        if (!$slides) {
            ContentSlides::create([
                'content_id' => $content_id,
                'created_by' => $user->id,
                // 'status'     => ContentSlides::PUBLISHED,
                'slides'     => json_encode($request->slides),
            ]);
        } else {
            $slides->slies = json_encode($request->slides);
            $slides->save();
        }


        return response()->json([
            'file_path'=>$file_path,
            'message'=>'Content is uploaded and is beiing processed'
        ]);
    }



}
