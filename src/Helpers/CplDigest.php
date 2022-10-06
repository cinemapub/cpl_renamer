<?php

namespace Brightfish\CplRenamer\Helpers;

class CplDigest
{
    public static function file(string $filename): string
    {
        if (!file_exists($filename)) return "";
        $binary = hash_file("sha1", $filename, true);
        return base64_encode($binary);
    }

    public static function text(string $text): string
    {
        $binary = hash("sha1", $text, true);
        return base64_encode($binary);
    }
    public static function size(string $filename): string
    {
        return filesize($filename);
    }



}