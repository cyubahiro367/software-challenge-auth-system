<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class TwoFactorRequestData extends Data
{
    public function __construct(
        public string $code
    ) {}
}
