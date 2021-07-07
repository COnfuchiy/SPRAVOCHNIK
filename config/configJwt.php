<?php

date_default_timezone_set('Europe/Moscow');

class ConfigJwt
{
    /**
     * secret JWT key
     * @var string
     */
    public static $key = "";
    public static $iss = "";
    public static $aud = "";
    public static $exp = 2*3600;
}
