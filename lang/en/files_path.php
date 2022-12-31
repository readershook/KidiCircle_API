<?php

return [
    "content" => [
        "cover_image" => "content/cover/:content_id/" . uniqid() . "-:file_name",
        "temp_assets" => "content/temp_assets/:content_id/" . uniqid() . "-:file_name",
        
    ],
    "imports" => [
        "admin_content_file" => "admin/imports/content/". uniqid() . "-:file_name",
    ]

];