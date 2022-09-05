<?php

namespace Pages;

class Utils
{
    public static function getFullPath($fileName){

        return codecept_output_dir("debug").DIRECTORY_SEPARATOR.$fileName.'.png';
    }
}