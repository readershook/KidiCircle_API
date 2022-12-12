<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'otp';

     /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded  = [];

    public $timestamps = false;

    const VERIFIED = 1;
    const UNVERIFIED = 0;
    const MAX_ALLOWED_ATTEMPT = 5;



    public function generateOtp($user_id, $for, $type, $expires=600)
    {
        $generateOtp = array(

            'otp'=>rand(1000,9999),
            'type'=>$type,
            'user_id'=>$user_id,
            'for'=>$for,
            'verified_status'=>self::UNVERIFIED,
            'expires_at'=> date('Y-m-d H:i:s',strtotime('+'.$expires.' seconds',strtotime(date('Y-m-d H:i:s')))),
            
        );
        $getOtp = Otp::create($generateOtp);
         return $getOtp->otp;
    }
}
