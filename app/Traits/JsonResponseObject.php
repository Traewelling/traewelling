<?php

namespace App\Traits;

trait JsonResponseObject
{
    public function toArray(): array
    {
        $array = [];
        foreach (get_object_vars($this) as $key => $value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                $array[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $array[$key] = $value;
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
