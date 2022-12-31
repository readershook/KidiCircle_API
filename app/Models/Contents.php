<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contents extends Model
{
    use HasFactory;

    const PUBLISHED     = 1;
    const UNPUBLISHED   = 2;
    const ARCHIVED      = 3;


    public static function store($request_data, $user) : self
    {

        $content = new Contents();



		// print_r($data);die;

		$content->title = $request_data['title'];
		$content->description = $request_data['description'];

	
		if( $user->id == 1 ) {//admin user
			$content->is_global = true;
		} else {
			$content->is_global = false;
		}
		// $content->is_slides = $request_data['is_slides'];

		$content->status = self::UNPUBLISHED;//remamin unpublished untill all the content are uploaded//1 for testing

		$content->language = $request_data['language'];
		$content->created_by = $user->id;
		$content->save();

		return $content;
    }
}
