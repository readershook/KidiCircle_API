<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\StaticMappers\AvatarMapper;
use App\Models\{Kids, KidsCoach, ContentAccess};
use App\Http\Resources\KidsListCollection;

class KidsController extends Controller
{
    public function createKids(Request $request)
    {
        $request->validate([
            "name" => "required|max:30",
            "avatar" => "required_without:avatar_url|file|mimes:jpg,jpeg,png",
            "avatar_url" =>
                "required_without:avatar|url|in:" .
                implode(",", AvatarMapper::AVATAR),
            "dob" => "date|date_format:Y-m-d|before:today",
            "gender" => "in:F,M",
        ]);

        $user = auth()->user();
        \DB::beginTransaction();
        $kid = Kids::create([
            "name" => $request->name,
            "dob" => $request->dob ? $request->dob : null,
            "gender" => $request->gender ? $request->gender : null,
            "added_by" => $user->id,
        ]);

        if (isset($request->avatar)) {
            $file_path = "pp/" . $kid->id;
            $file_name =
                uniqid("img-") .
                "-" .
                $request->avatar->getClientOriginalName();
            $full_file_path = $file_path . "/" . $file_name;
            \Storage::disk("s3")->put(
                $full_file_path,
                file_get_contents($request->avatar->getRealPath())
            );
        } else {
            $full_file_path = $request->avatar_url;
            $full_file_path = str_replace(
                config("constants.s3_base_url"),
                "",
                $full_file_path
            );
        }
        $kid->avatar = $full_file_path;
        $kid->save();

        KidsCoach::create([
            "kid_id" => $kid->id,
            "coach_id" => $user->id,
            "role" => KidsCoach::PARENTS,
            "status" => KidsCoach::STATUS_ACCEPTED,
        ]);
        \DB::commit();

        return response()->json([
            "id" => $kid->id,
            "message" => "Kid created succesfully",
        ]);
    }

    public function updateKidInfo(Request $request, $id)
    {
        $request->validate([
            "name" => "sometimes|required|max:30",
            // 'avatar'=>'required|file|mimes:jpg,jpeg,png',
            "dob" => "sometimes|date|date_format:Y-m-d|before:today",
            "gender" => "sometimes|in:F,M",
            "avatar_url" =>
                "sometimes|url|in:" . implode(",", AvatarMapper::AVATAR),
        ]);

        $user = auth()->user();
        $data = collect($request->all());
        $filtered = $data->only(["name", "dob", "gender", "avatar_url"])->all();
        $filtered["avatar"] = str_replace(
            config("constants.s3_base_url"),
            "",
            $filtered["avatar_url"]
        );
        unset($filtered["avatar_url"]);
        if (!empty($filtered["name"])) {
            // $check_duplicate = KidsCoach::where(['kid_id'=>])
            $check_dupliate = Kids::join(
                "kids_coach",
                "kids_coach.kid_id",
                "kids.id"
            )
                ->where("kids.name", $filtered["name"])
                ->where("kids_coach.coach_id", $user->id)
                ->where("kid_id", "!=", $id)
                ->first();
            if ($check_dupliate) {
                return response()->json(
                    [
                        "message" =>
                            "You have already added a kid with the same name",
                        "status_code" => 400,
                    ],
                    400
                );
            }
        }
        Kids::find($id)->update($filtered);
        return response()->json([
            "status" => 200,
            "message" => "Kid info updated succesfully",
        ]);
    }

    public function updateAvatar(Request $request, $id)
    {
        $request->validate([
            "avatar" => "required|file|mimes:jpg,jpeg,png",
        ]);

    
        $user = auth()->user();

        $file_path = "pp/" . $id;
        $file_name =
            uniqid("img-") . "-" . $request->avatar->getClientOriginalName();
        $full_file_path = $file_path . "/" . $file_name;
        \Storage::disk("s3")->put(
            $full_file_path,
            file_get_contents($request->avatar->getRealPath())
        );
        Kids::find($id)->update(["avatar" => $full_file_path]);
        return response()->json(
            [
                "message" => 'Kid\'s avatar updated Successfully',
                "status_code" => 200,
            ],
            200
        );
    }

    public function deleteKid(Request $request, $id)
    {
        try {
            \DB::beginTransaction();
            /*remove from all the mentorships*/
            KidsCoach::where("kid_id", $id)->delete();

            /*remove from all the groups*/
            GroupMembers::where("member_id", $id)->delete();

            \DB::commit();
            return response()->json(["message" => "Kid deleted succesfully"]);
        } catch (Exception $e) {
            \DB::rollback();
            \Log::error("Error in deleting kid", [$e->__toString()]);
            return \Respomnse::make(["message" => "Internal sever error"]);
        }
    }

    public function listKids(Request $request)
    {
        $user = auth()->user();

        $data = KidsCoach::join("kids", "kids.id", "=", "kids_coach.kid_id")
            ->where("kids_coach.coach_id", $user->id)
            ->where("kids_coach.role", KidsCoach::PARENTS)
            ->select("kids.id", "kids.name", "kids.avatar", "kids.dob")
            ->simplePaginate($request->per_page)
            ->appends($request->all());
        // return $data;
        return new KidsListCollection($data);
    }

    public function addContent(Request $request)
    {
        $request->validate([
            "kid_id" => "required",
            "content_id" => "required|exists:contents,id",
        ]);

        
        $user = auth()->user();

        $checkMentor = false;
        if ($request->kid_id == "all") {
            $checkMentor = true;
            $getKidIds = [];
            $getKidIdData = KidsCoach::where([
                "coach_id" => $user->id,
                "published" => KidsCoach::PUBLISHED,
            ])->get();
            foreach ($getKidIdData as $value) {
                array_push($getKidIds, $value->kid_id);
            }
        } else {
            $getKidIds = explode(",", $request->kid_id);
        }

        foreach ($getKidIds as $value) {
            if (!$checkMentor) {
                $checkMentor = KidsCoach::where([
                    "kid_id" => $value,
                    "coach_id" => $user->id,
                ])->first();
            }

            if (!$checkMentor) {
                return response()->json(["message" => "Authorization Failed"], 401);
            }

            $check_already = ContentAccess::where([
                "kidid" => $value,
                "contentid" => $request->content_id,
            ])->first();
            if (!$check_already) {
                ContentAccess::create([
                    "kidid" => $value,
                    "contentid" => $request->content_id,
                    "givenby" => $user->id,
                    "published" => "1",
                ]);
            } elseif (!$check_already->published) {
                $check_already->published = 1;
                $check_already->save();
            }
        }
        return response()->json(["message" => "Success"]);
    }

    public function removeContent(Request $request)
    {
        $request->validate([
            "kid_id" => "required",
            "content_id" => "required|exists:contents,id",
        ]);

        $user = auth()->user();

        $checkMentor = KidsCoach::where([
            "kid_id" => $request->kid_id,
            "coach_id" => $user->id,
        ])->first();

        if (!$checkMentor) {
            return response()->json(["message" => "Kid does not exists"], 401);
        }

        $check_already = ContentAccess::where([
            "kidid" => $request->kid_id,
            "contentid" => $request->content_id,
        ])->first();

        if (!$check_already) {
            return response()->json(["message" => "Kid does not exists"], 401);
        }
        $check_already->delete();
        return response()->json(["message" => "Success"]);
    }

}
