<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Otp, User, LinkedSocialAccount};
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    const LOG_PREFIX = "User Controller";

    public function create(Request $request)
    {
        $data = $request->all();
        $validator = $request->validate([
            "name" => "required|string",
            "email" => "required|string|email|unique:users",
        ]);

        try {
            $data["password"] = bcrypt(mt_rand(999, 9999));

            // $data["password"] = bcrypt(123456);
            $user = User::create($data);
            // generate_mentor_code($user->id);//move this to job
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

                /*
                    move otp code to Job 
                //  */
                Mail::send("mails.userverification", $email_data, function (
                    $message
                ) use ($email_data) {
                    $message
                        ->to($email_data["email"], $email_data["name"])
                        ->subject("Verify Email Address");
                });
            }
        } catch (\Exception $th) {
            \Log::error(self::LOG_PREFIX . "Error while creating user", [
                "trace" => $th->__toString(),
            ]);
            return response()->json(
                [
                    "message" => "Internal server error",
                    "status_code" => 500,
                ],
                500
            );
        }

        if ($user) {
            // $success['token'] =  $user->createToken('token')->accessToken;
            $success["message"] = "Registration successfull..";
            $success["status_code"] = 200;

            return response()->json($success, 200);
        }
    }

    public function login(Request $request)
    {
        $data = $request->all();
        if (!empty($data["provider"])) {
            $validator = $request->validate([
                "provider" => "required|in:facebook,google,linkedin,twitter",
                "provider_metadata" => "required",
                "provider_metadata.email" => "required|string|email",
                "provider_metadata.id" => "required|alpha_num|max:30",
                "provider_metadata.name" => "required|string",
                "provider_access_token" => "required|min:100",
            ]);

            // if($validator->fails())
            //     throw new StoreResourceFailedException('Input Params are incorrect',$validator->errors());

            $linkedSocialAccount = LinkedSocialAccount::where(
                "provider_name",
                $data["provider"]
            )
                ->where("provider_id", $data["provider_metadata"]["id"])
                ->first();
            if ($linkedSocialAccount) {
                $user = $linkedSocialAccount->user;
            } else {
                $user = null;

                if ($email = $data["provider_metadata"]["email"]) {
                    $user = User::where("email", $email)->first();
                }

                if (!$user && $email) {
                    $user = User::create([
                        "name" => $data["provider_metadata"]["name"],
                        "email" => $data["provider_metadata"]["email"],
                    ]);

                    $user->email_verified_at = date("Y-m-d g:i:s");

                    switch ($data["provider"]) {
                        case "facebook":
                            $user->fb_data = json_encode(
                                $data["provider_metadata"]
                            );
                            break;
                        case "google":
                            $user->google_data = json_encode(
                                $data["provider_metadata"]
                            );
                            break;
                        case "twitter":
                            $user->twitter_data = json_encode(
                                $data["provider_metadata"]
                            );
                            break;
                    }
                    $user->save();
                }
                $user->linkedSocialAccounts()->create([
                    "provider_id" => $data["provider_metadata"]["id"],
                    "provider_name" => $data["provider"],
                ]);
            }

            $success["token"] = $user->createToken("token")->accessToken;
            return response()->json($success, 200);
        }
        // elseif(!empty($data['otp']))
        // {
        //     $validator = Validator::make($data, [
        //         'email' => 'required|string|email',
        //         'otp' => 'required|digits:4'
        //     ]);
        //     if($validator->fails()){
        //         throw new StoreResourceFailedException('Input Params are incorrect',$validator->errors());
        //     }
        //     $user = User::where('email',$data['email'])->first();
        //     $verifyOtp = false;
        //     if($user)
        //         $verifyOtp = (new Otp)->verifyOtp($user->id, $data['email'], 'email', $data['otp']);
        //     if($verifyOtp)
        //     {
        //         $date = date("Y-m-d g:i:s");
        //         $user->email_verified_at = $date; // to enable the “email_verified_at field of that user be a current time stamp by mimicing the must verify email feature
        //         $user->save();
        //         $success['token'] =  $user->createToken('token')->accessToken;
        //         return response()->json($success
        //             , 200);
        //     }
        //     else
        //     {
        //         return response()->json([
        //             'message'     => 'Invalid OTP',
        //             'status_code' => 401,
        //         ], 401);
        //     }
        // }
        else {
            $validator = $request->validate([
                "email" => "required|string|email",
                "password" => "required",
            ]);

            // if($validator->fails()){
            //     throw new StoreResourceFailedException('Input Params are incorrect',$validator->errors());
            // }

            $credentials = request(["email", "password"]);

            if (!($token = auth()->attempt($credentials))) {
                return response()->json(
                    [
                        "message" => "Wrong credentials.",
                        "status_code" => 401,
                    ],
                    401
                );
            }

            // return $this->respondWithToken($token);
            //           echo $token;die;
            $user = auth()->user();
            if ($user->email_verified_at !== null) {
                $success["token"] = $token;
                return response()->json($success, 200);
            } else {
                return response()->json(
                    [
                        "message" => "Please Verify Email.",
                        "status_code" => 401,
                    ],
                    401
                );
            }
        }
    }

    public function getUser(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $user->profile_picture = $user->profile_picture
                ? config("constants.s3_base_url") . $user->profile_picture
                : null;
            return response()->json($user, 200);
        } else {
            return response()->json(
                [
                    "message" => "User not found",
                    "status_code" => 401,
                ],
                401
            );
        }
    }

    public function updateUser(Request $request)
    {
        if (auth()->user()) {
            $userID = auth()->id();
            $user = User::find($userID);
        
            if ($user) {
                $data = $request->all();
                $validator = $request->validate([
                    "name" => "string|max:255",
                    "nickname" => "string|max:50",
                    "city" => "alpha|string|min:3|max:50",
                    "country" => "alpha|string|min:4|max:50",
                    "age" => "int|min:1|max:3|between:1,200",
                    "gender" => "alpha|in:M,F,Other,m,f",
                    "pin" => "int|digits_between:4,4",
                    // 'mentor_code    ' => 'int|min:1000|max:9999',
                    "dob" => "date",
                ]);
        
                $data = collect($data);
                $filtered = $data
                    ->only([
                        "name",
                        "nickname",
                        "about",
                        "gender",
                        "city",
                        "coutnry",
                        "dob",
                        "age",
                        "fb_data",
                        "google_data",
                        "twitter_data",
                        "pin",
                    ])
                    ->all();
                $user->fill($filtered)->save();
                $user->profile_picture = $user->profile_picture
                    ? config("constants.s3_base_url") . $user->profile_picture
                    : null;
                return response()->json($user, 200);
            }
        } else {
            return response()->json(
                [
                    "message" => "User not found",
                    "status_code" => 401,
                ],
                401
            );
        }
    }

    public function updateProfilePic(Request $request)
    {
        $request->validate([
            "file" => "required|file|mimes:jpg,jpeg,png",
        ]);
        $user = auth()->user();
        $file_path = "usr/" . $user->id;
        $file_name = uniqid("img-") . "-" . $request->file->getClientOriginalName();
        $full_file_path = $file_path . "/" . $file_name;
        \Storage::disk("s3")->put(
            $full_file_path,
            file_get_contents($request->file->getRealPath())
        );
        $user->profile_picture = $full_file_path;
        $user->save();
        return response()->json(
            [
                "message" => "Profile Picture Updated Successfully",
                "status_code" => 200,
            ],
            200
        );
    }
    protected function verifyPin(Request $request)
    {
        $userID = auth()->id();
        $request->validate([
                'pin' => 'required|exists:users,pin,id,'.$userID,
            ]);
        return response()->json(
            [
                'message'     => 'Verified Successfully',
                'status_code' => 200,
            ],
            200
        );

    }
    protected function respondWithToken($token)
    {
        return response()->json([
            "access_token" => $token,
            "token_type" => "bearer",
            "expires_in" =>
                auth("api")
                    ->factory()
                    ->getTTL() * 60,
        ]);
    }
}
