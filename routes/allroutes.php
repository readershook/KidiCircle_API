<?php
	$api->group(["middleware" => ["auth:api"]], function ($api) {
		$api->post("password/reset", "Users\UsersController@passwordReset");
	});

	$api->group(["prefix" => "user", "namespace" => "Users"], function ($api) {
		// $api->post('create','UsersController@create');
		$api->group(["middleware" => ["auth:api"]], function ($api) {
			// $api->get("detail", "UsersController@getUser");
			// $api->put("update", "UsersController@updateUser");
			$api->post("update-profile-pic", "UsersController@updateProfilePic");
			// $api->post("password_update", "UsersController@updatePassword");
			$api->post("verify_pin", "UsersController@verifyPin");
		});

		$api->group(["middleware" => ["guest:api"]], function ($api) {
			$api->post("login", "UsersController@login");
			// $api->post("create", "UsersController@create");
		});
		// $api->post("otp/send", "UsersController@sendOtp");
		// $api->post("otp/verify", "UsersController@verifyOTP");

		// $api->get('email/verfiy','KidiApp\Http\Controllers\Auth\VerificationApiController@verify')->name('verification.verify');
		// $api->get('email/verfiy','KidiApp\Mail\Userverification@build');
	});
	$api->group(["prefix" => "kids"], function ($api) {
		// Use this route group for v1
		$api->group(["middleware" => ["auth:api"]], function ($api) {
			$api->post("add", "KidsController@createKids");
			$api->get("list", "KidsController@listKids");
			$api->post("addcontent", "KidsController@addcontent");
			$api->post("removecontent", "KidsController@removecontent");

			//
			// $api->post('approve/mentee/{id}','UsersController@updateUser');
			// $api->elete('remove/mentee/{id}','UsersController@updateUser');

			// $api->get('list','UsersController@updateUser');
		});

		$api->group(["middleware" => ["check_parent:api"]], function ($api) {
			$api->get("mentors/{id}", "KidsController@kidsMentors");
			$api->post("update-avatar/{id}", "KidsController@updateAvatar");
			$api->put("{id}", "KidsController@updateKidInfo");
			$api->delete("{id}", "KidsController@deleteKid");
			$api->post("remove_mentor/{id}", "KidsController@removeMentor");
			$api->post("add_mentor/{id}", "KidsController@addMentor");
		});
	});

	$api->group(["prefix" => "content", "namespace" => "Contents"], function (
		$api
	) {
		// Use this route group for v1
		$api->group(["middleware" => ["auth:api"]], function ($api) {
			$api->post("create", "ContentController@contentCreateAction");
			$api->post("slides", "ContentController@createSlides");
			$api->post("s3_upload", "ContentController@upload");
			$api->get("my_content", "ContentController@MyLibrary"); //my librry api
		});

		$api->get("all", "ContentController@getAllContent");
		$api->get("show/{id}", "ContentController@show");
	});

	$api->group(["prefix" => "tracks"], function ($api) {
		$api->group(["middleware" => ["admin:api"]], function ($api) {
			$api->post("create", "TracksController@createTrack");
		});

		$api->get("list", "TracksController@listTracks");
		// $api->get('contents/{id}', 'TracksController@trackContents');
	});

	$api->group(["prefix" => "mentor"], function ($api) {
		$api->post("approve/mentee/{id}", "MentorController@approveMentee");
		$api->post("reject/mentee/{id}", "MentorController@rejectMentee");
	});

	$api->group(["prefix" => "group", "namespace" => "Groups"], function ($api) {
		// Use this route group for v1
		$api->group(["middleware" => ["auth:api"]], function ($api) {
			$api->post("create", "GroupsController@create");
			$api->group(["middleware" => ["group:api"]], function ($api) {
				$api->put("{id}", "GroupsController@update");
				$api->post("logo/update/{id}", "GroupsController@upategroupIcon");
				$api->delete("{id}", "GroupsController@deleteGroup");
				$api->post("add_content/{id}", "GroupsController@add_content");
				$api->post(
					"remove_content/{id}",
					"GroupsController@remove_content"
				);
			});

			$api->group(["middleware" => ["check_parent:api"]], function ($api) {
				$api->post("add_member", "GroupsController@addMember");
			});
			$api->post("remove_member", "GroupsController@removeMember"); //parent and group owner can remove a kid from group

			$api->get("list", "GroupsController@listGroups");

			//

			// $api->get('show/{id}','GroupsController@create');//group_id
			//  		$api->get('list/members/{id}','GroupsController@create');//group_id
			//  		$api->get('list/content/{id}','GroupsController@create');//group_id
		});
	});

	// $api->get("languages/all", "GeneralController@getAllLanguages");
	// $api->get("categories/all", "GeneralController@getAllCategories");
	// $api->get("avatar/all", "GeneralController@getAllAvatar");

	$api->group(["prefix" => "internal", "namespace" => "Internal"], function (
		$api
	) {
		$api->post(
			"content/upload_bulk_sheet",
			"ContentController@uploadBulkContentSheet"
		);
		// $api->post('reject/mentee/{id}', 'MentorController@rejectMentee');
	});
