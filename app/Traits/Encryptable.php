<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait Encryptable
{
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable ?? []) && !empty($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable ?? []) && !empty($value)) {
            $value = Crypt::encryptString($value);
        }

        return parent::setAttribute($key, $value);
    }

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($this->encryptable ?? [] as $key) {
            if (isset($attributes[$key])) {
                try {
                    $attributes[$key] = Crypt::decryptString($attributes[$key]);
                } catch (\Exception $e) {
                    // Silencieux
                }
            }
        }

        return $attributes;
    }
}