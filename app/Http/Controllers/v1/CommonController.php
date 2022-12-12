<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\StaticMappers\{LanguageMapper, AvatarMapper};
use App\Models\{Categories};

class CommonController extends Controller
{
    public function getAllLanguages()
    {
        return ["data" => LanguageMapper::LANGUAGES];
    }

    public function getAllCategories()
    {
        $all_categories = Categories::where("published", 1)
            ->get(["id", "name", "description"])
            ->toArray();
        return ["data" => $all_categories];
    }

    public function getAllAvatar()
    {
        return ["data" => AvatarMapper::AVATAR];
    }
}
