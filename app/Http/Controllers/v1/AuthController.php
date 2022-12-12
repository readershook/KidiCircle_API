<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Otp, User};
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $data = $request->all();

        $validator = $request->validate([
            "email" => "required|string|email|exists:users,email",
        ]);
        $user = User::where("email", $data["email"])->first();

        if ($user->email) {
            $getOtpCode = (new Otp())->generateOtp(
                $user->id,
                $user->email,
                "email",
                "3600"
            );

            $email_data = [
                "name" => $user->name,
                "email" => $user->email,
                "otp" => $getOtpCode,
            ];

            Mail::send("mails.userverification", $email_data, function (
                $message
            ) use ($email_data) {
                $message
                    ->to($email_data["email"], $email_data["name"])
                    ->subject("Verify Email Address");
            });
        }
        $success["message"] = "Otp has been sent to your email.";
        $success["status_code"] = 200;
        return \Response::make($success, 200);
    }

    public function verifyOTP(Request $request)
    {
        $data = $request->all();

        $request->validate([
            "email" => "required|string|email",
            "otp" => "required|digits:4",
        ]);
        $user = User::where("email", $data["email"])->first();
        $verifyOtp = false;
        if (!$user) {
            return \Response::make($verifyOtp, 401);
        }

        $verifyOtp = (new Otp())->verifyOtp(
            $user->id,
            $data["email"],
            "email",
            $data["otp"]
        );
        $date = date("Y-m-d g:i:s");
        $user->email_verified_at = $date; // to enable the “email_verified_at field of that user be a current time stamp by mimicing the must verify email feature
        $user->save();

        $verifyOtp["token"] = auth()->tokenById($user->id);
        return \Response::make($verifyOtp, 200);
    }
}
