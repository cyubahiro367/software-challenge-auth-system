<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class Step1PersonalInfoData extends Data
{
    public function __construct(
        public ?string $honorific,
        public string $first_name,
        public string $last_name,
        public string $gender,
        public string $date_of_birth,
        public string $email,
        public string $nationality,
        public string $phone_number,
        public mixed $profile_picture = null
    ) {}
}
