<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Dingo\Api\Exception\StoreResourceFailedException;
use App\Models\{Otp, User, LinkedSocialAccount};
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    const LOG_PREFIX = 'User Controller';

  


    public function create(Request $request)
    {
        $data = $request->all();
        $validator = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
        ]);
        
        try {
            $data['password'] = bcrypt(mt_rand(999, 9999));

            $data['password'] = bcrypt(123456);
            $user = User::create($data);
            // generate_mentor_code($user->id);//move this to job
            if ($user->email) {
                $getOtpCode = (new Otp)->generateOtp($user->id, $user->email, 'email', '3600');
                $email_data = array(
                    "name" => $user->name,
                    "email" => $user->email,
                    "otp"=>$getOtpCode
                );

                /*
                    move otp code to Job 
                //  */
                Mail::send('mails.userverification', $email_data, function ($message) use ($email_data) 
                {
                    $message->to($email_data['email'], $email_data['name'])
                        ->subject('Verify Email Address');
                });
            }
        } catch (\Exception $th) {

            \Log::error(self::LOG_PREFIX .'Error while creating user' ,['trace' => $th->__toString()]);
            return \Response::make([
                'message'     => 'Internal server error',
                'status_code' => 500,
            ], 500);
        }
        
        if ($user) {
            // $success['token'] =  $user->createToken('token')->accessToken;
            $success["message"] = "Registration successfull..";
            $success["status_code"] = 200;
        
            return \Response::make($success, 200);
        }
        
    }



    public function login(Request $request)
    {
        $data = $request->all();
        if(!empty($data['provider']))
        {
            $validator = $request->validate([
                'provider' => 'required|in:facebook,google,linkedin,twitter',
                'provider_metadata' => 'required',
                'provider_metadata.email' => 'required|string|email',
                'provider_metadata.id' => 'required|alpha_num|max:30',
                'provider_metadata.name' => 'required|string',
                'provider_access_token'=>'required|min:100'
            ]);
            
            // if($validator->fails())
            //     throw new StoreResourceFailedException('Input Params are incorrect',$validator->errors());
            
            $linkedSocialAccount = LinkedSocialAccount::where('provider_name', $data['provider'])
                ->where('provider_id', $data['provider_metadata']['id'])
                ->first();
            if ($linkedSocialAccount) {
                $user = $linkedSocialAccount->user;
            }
            else {

                $user = null;

                if ($email = $data['provider_metadata']['email']) {
                    $user = User::where('email', $email)->first();
                }

                if (! $user && $email) {
                    
                    $user = User::create([
                        'name' => $data['provider_metadata']['name'],
                        'email' => $data['provider_metadata']['email'],
                    ]);

                    $user->email_verified_at = date("Y-m-d g:i:s");
                    
                    switch($data['provider'])
                    {
                        case 'facebook':
                            $user->fb_data = json_encode($data['provider_metadata']);
                            break;
                        case 'google':
                            $user->google_data = json_encode($data['provider_metadata']);
                            break;
                        case 'twitter':
                            $user->twitter_data = json_encode($data['provider_metadata']);
                            break;
                    }
                    $user->save();
                }
                $user->linkedSocialAccounts()->create([
                    'provider_id' => $data['provider_metadata']['id'],
                    'provider_name' => $data['provider'],
                ]);
            }

            $success['token'] =  $user->createToken('token')->accessToken;
            return \Response::make($success
                , 200);
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
        //         return \Response::make($success
        //             , 200);
        //     }
        //     else
        //     {
        //         return \Response::make([
        //             'message'     => 'Invalid OTP',
        //             'status_code' => 401,
        //         ], 401);
        //     }
        // }
        else
        {
            $validator = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required'
            ]);
            
            // if($validator->fails()){
            //     throw new StoreResourceFailedException('Input Params are incorrect',$validator->errors());
            // }
    
            $credentials = request(['email', 'password']);
            
            if(!$token = auth()->attempt($credentials)){
                return \Response::make([
                    'message'     => 'Wrong credentials.',
                    'status_code' => 401,
                ], 401);
            }

  // return $this->respondWithToken($token);
  //           echo $token;die;
            $user = auth()->user();
            if($user->email_verified_at !== NULL)
            {
                $success['token'] =  $token;
                return \Response::make($success
                    , 200);
            }
            else{
                return \Response::make([
                    'message'     => 'Please Verify Email.',
                    'status_code' => 401,
                ], 401);
            }
        }
    }


     protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }



}
