<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KidsCoach extends Model
{
    use HasFactory;

    const PARENTS ='P';
    const MENTOR ='M';

    const STATUS_ACCEPTED = 1;
	const STATUS_PENDING = 2;
    const STATUS_REJECTED  = 3;

    const PUBLISHED = 1;
    const UNPUBLISHED = 2;

    protected $table = 'kids_coach';
     /**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [];
}
