<?php

namespace App\Support;

use App\Models\User;

class ActivationCodeGenerator
{
    public static function generate(): string
    {
        $prefix = 'ACT-';
        $code = $prefix.strtoupper(substr(md5(uniqid().time().rand()), 0, 12));

        while (User::where('activation_code', $code)->exists()) {
            $code = $prefix.strtoupper(substr(md5(uniqid().time().rand()), 0, 12));
        }

        return $code;
    }
}
