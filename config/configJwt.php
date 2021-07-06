<?php

date_default_timezone_set('Europe/Moscow');

class ConfigJwt
{
    public static $key = "yura_genius";
    public static $iss = "http://3.122.244.77";
    public static $aud = "http://3.122.244.77";
    public static $exp = 2*3600;
}
