<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class Step5ReviewData extends Data
{
    public function __construct(
        public bool $confirmation
    ) {}
}
