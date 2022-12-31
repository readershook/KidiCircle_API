<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imports extends Model
{
    use HasFactory;


    const WAITING_FOR_QUEUE     = 1;
    const PROCESSING            = 2;
    const PROCESSED             = 3;
    const ERROR                 = 4;



    const BULK_CONTENT_UPLOAD = 1;

}
