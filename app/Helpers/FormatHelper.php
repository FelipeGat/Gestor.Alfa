<?php

namespace App\Helpers;

class FormatHelper
{
    public static function cpfCnpj($value)
    {
        $v = preg_replace('/\D/', '', $value);

        if (strlen($v) === 11) {
            return preg_replace(
                "/(\d{3})(\d{3})(\d{3})(\d{2})/",
                "$1.$2.$3-$4",
                $v
            );
        }

        if (strlen($v) === 14) {
            return preg_replace(
                "/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/",
                "$1.$2.$3/$4-$5",
                $v
            );
        }

        return $value;
    }
}