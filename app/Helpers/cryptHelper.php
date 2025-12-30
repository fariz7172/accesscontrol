<?php

use Illuminate\Support\Facades\Crypt;

if (!function_exists('encryptId')) {
    function encryptId($id)
    {
        return Crypt::encrypt($id);
    }
}

if (!function_exists('decryptId')) {
    function decryptId($encryptedId)
    {
        return Crypt::decrypt($encryptedId);
    }
}
