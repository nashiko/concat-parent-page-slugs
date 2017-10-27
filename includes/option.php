<?php

Class CPPS_Option
{
    private static $def_option = array(
        'delimiter' => '.',
        'use_pages' => 1,
        'use_single' => 0
    );

    public static function def()
    {
        return wp_parse_args(self::$def_option);
    }

    public static function get()
    {
        return wp_parse_args(get_option(CPPS_Config::OPTION_NAME), self::$def_option);
    }

    public static function set($arr)
    {
        return wp_parse_args($arr, self::$def_option);
    }

    public static function delete()
    {
        return delete_option(CPPS_Config::OPTION_NAME);
    }
}

