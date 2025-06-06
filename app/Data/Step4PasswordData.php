<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class Step4PasswordData extends Data
{
    public function __construct(
        public string $password
    ) {}
}
