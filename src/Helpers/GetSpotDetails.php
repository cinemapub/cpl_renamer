<?php

namespace Brightfish\CplRenamer\Helpers;

use Dotenv\Dotenv;

class GetSpotDetails
{
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../..");
        $dotenv->safeLoad();
    }

    public function get(string $uuid): array
    {
        //   <Id>urn:uuid:5e8271bf-ce27-416a-b432-6295fb99af8e</Id>
        $uuid = str_replace("urn:uuid:", "", $uuid);
        $cache_file = "$uuid.json";
        $url = "https://lookup.spottix.app/api/reel/$uuid";
        return $this->getCachedApi($url);
    }

    private function getCachedUrl(string $url)
    {
        $cache_folder = "cache";
        if (!is_dir($cache_folder)) {
            mkdir($cache_folder);
        }
        $host = parse_url($url, PHP_URL_HOST);
        $cached_file = sprintf("%s/%s.%s.%s", $cache_folder, $host, substr(sha1($url), 0, 10), "html");
        if (file_exists($cached_file) && $contents = file_get_contents($cached_file)) {
            return $contents;
        }
        $contents = $this->doCurl($url);
        if ($contents) {
            file_put_contents($cached_file, $contents);
        }
        return $contents;
    }

    private function getCachedApi(string $url): array
    {
        $cache_folder = "cache";
        if (!is_dir($cache_folder)) {
            mkdir($cache_folder);
        }
        $host = parse_url($url, PHP_URL_HOST);
        $hash = substr(sha1($url), 0, 10);
        $sub_folder = sprintf("%s/%s", $cache_folder, substr($hash, 0, 1));
        if (!is_dir($sub_folder)) {
            mkdir($sub_folder);
        }
        $cached_file = sprintf("%s/%s.%s.%s", $sub_folder, $host, $hash, "data");
        if (file_exists($cached_file) && $contents = unserialize(file_get_contents($cached_file))) {
            return $contents;
        }
        sleep(1);
        $contents = $this->doCurl($url);
        if ($contents) {
            $array = json_decode($contents, true);
            if ($array) {
                file_put_contents($cached_file, serialize($array));
                return $array;
            } else {
                return [];
            }
        }
        return [];
    }

    private function doCurl(string $url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $_ENV["lookup_token"]
            ),
        ));

        $contents = curl_exec($curl);
        curl_close($curl);
        return $contents;
    }
}