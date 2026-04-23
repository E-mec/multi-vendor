<?php

namespace Modules\Authentication\dto;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,

    ){}
}
