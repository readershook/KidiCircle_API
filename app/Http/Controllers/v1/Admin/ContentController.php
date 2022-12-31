<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Imports};
use App\Jobs\Admin\{ProcessContentCSVFile};

class ContentController extends Controller
{
    protected function uploadContentFile(Request $request) {
        $request->validate([
            'file' => 'required'
        ]);

        $user = auth()->user();
        $file_path = __('files_path.imports.admin_content_file', [
            "file_name" => $request->file->getClientOriginalName(),
        ]);
        \Storage::disk('s3')->put($file_path, file_get_contents($request->file->getRealPath()));

        $imports = new Imports();
        $imports->user_id = $user->id;
        $imports->file = $file_path;
        $imports->status = Imports::WAITING_FOR_QUEUE;
        $imports->type = Imports::BULK_CONTENT_UPLOAD;
        $imports->save();

        ProcessContentCSVFile::dispatch($imports->id, $user);

        return response()->json([
            'file_path'=>$file_path,
            'message'=>'file uploaded succesfully. Will be processed in background'
        ]);
    }
}
