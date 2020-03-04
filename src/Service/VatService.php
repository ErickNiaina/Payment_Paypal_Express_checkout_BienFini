<?php

namespace App\Service;

class VatService{

    public static function getVatPrice($price,$rate){
        return round($price * $rate * 100) / 100;
    }
}