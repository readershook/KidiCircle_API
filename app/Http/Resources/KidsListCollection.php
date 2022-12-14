<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class KidsListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($data)
    {
        return [
            'data' => $this->collection->transform(function($data){
                return [
                    'id' => $data->id,
                    'name' => $data->name,
                    'dob'   =>$data->dob,
                    'avatar' => config('constants.s3_base_url').$data->avatar,
          
                ];
            }),
        ];
    }
}
