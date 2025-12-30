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


if (!function_exists('convertCardToSoyalFormat')) {
    function convertCardToSoyalFormat($cardNumber)
    {
        if (!$cardNumber || !is_numeric($cardNumber)) {
            return '';
        }

        $number = (int)$cardNumber;
        $facilityCode = str_pad(intdiv($number, 65536), 5, '0', STR_PAD_LEFT); // Bagian depan
        $cardId = str_pad($number % 65536, 5, '0', STR_PAD_LEFT);           // Bagian belakang

        return $facilityCode . ':' . $cardId;
    }
}

if (!function_exists('formatSoyalDate')) {
    function formatSoyalDate($date)
    {
        return $date ? \Carbon\Carbon::parse($date)->format('m-d-Y') : null;
    }
}

if (!function_exists('formatSoyalTime')) {
    function formatSoyalTime($date)
    {
        return $date ? \Carbon\Carbon::parse($date)->format('H:i') : '00:00';
    }
}