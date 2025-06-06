<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class UploadProfilePictureDateData extends Data
{
    public function __construct(
        public string $profile_picture,
    ) {}
}
