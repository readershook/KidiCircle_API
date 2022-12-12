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
    protected $table = "otp";

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    const VERIFIED = 1;
    const UNVERIFIED = 0;
    const MAX_ALLOWED_ATTEMPT = 5;

    public function generateOtp($user_id, $for, $type, $expires = 600)
    {
        $generateOtp = [
            "otp" => rand(1000, 9999),
            "type" => $type,
            "user_id" => $user_id,
            "for" => $for,
            "verified_status" => self::UNVERIFIED,
            "expires_at" => date(
                "Y-m-d H:i:s",
                strtotime(
                    "+" . $expires . " seconds",
                    strtotime(date("Y-m-d H:i:s"))
                )
            ),
        ];
        $getOtp = Otp::create($generateOtp);
        return $getOtp->otp;
    }

    public function verifyOtp($user_id, $for, $type, $otp)
    {
        $check_otp = Otp::where(["for" => $for, "type" => $type])
            ->latest()
            ->first();

        if ($check_otp->verified_status == self::VERIFIED) {
            return [
                "status" => 0,
                "message" =>
                    "Otp is already varified. please request a new OTP",
            ];

            // return 'alrady_verified';
        } elseif ($check_otp->expires_at < date("Y-m-d H:i:s")) {
            return [
                "status" => 0,
                "message" => "Otp is already expired. please request a new OTP",
            ];
        } elseif ($check_otp->attempt_count >= self::MAX_ALLOWED_ATTEMPT) {
            return [
                "status" => 0,
                "message" =>
                    "You have exceeded max limit of otp verification. please request a new otp",
            ];
        }

        $check_otp->increment("attempt_count", 1);

        // Otp::where(['for' => $for, 'type'=>$type])->latest()->first()

        $otpExists = Otp::where([
            "user_id" => $user_id,
            "for" => $for,
            "type" => $type,
            "otp" => $otp,
        ])->first();

        if ($otpExists) {
            $otpExists->update(["verified_status" => self::VERIFIED]);
            return ["status" => 1, "message" => "Otp verified Successfully"];
        } else {
            return ["status" => 0, "message" => "Invalid Otp"];
        }
    }
}
