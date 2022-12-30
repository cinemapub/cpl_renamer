<?php

namespace Brightfish\CplRenamer\Helpers;

use Dotenv\Dotenv;
use RuntimeException;

class GetSpotDetails
{
    private string $cache_folder;

    public function __construct(string $cache_folder = 'cache')
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../..");
        $dotenv->safeLoad();
        $this->cache_folder = $cache_folder;
        if (!is_dir($this->cache_folder) && !mkdir($this->cache_folder) && !is_dir($this->cache_folder)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->cache_folder));
        }
    }

    /**
     * @throws \JsonException
     */
    final public function get(string $uuid): array
    {
        //   <Id>urn:uuid:5e8271bf-ce27-416a-b432-6295fb99af8e</Id>
        $uuid = str_replace("urn:uuid:", "", $uuid);
        $url = "https://lookup.spottix.app/api/reel/$uuid";
        return $this->getCachedApi($url);
    }

    private function getCachedUrl(string $url): string
    {
        $cached_file = $this->cacheFileName($url);
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
        $cached_file = $this->cacheFileName($url,"data");
        if (file_exists($cached_file) && $contents = unserialize(file_get_contents($cached_file),[ "allowed_classes" => false])) {
            return $contents;
        }
        sleep(1); // to avoid overloading the API
        $contents = $this->doCurl($url);
        if ($contents) {
            $array = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
            if ($array) {
                file_put_contents($cached_file, serialize($array));
                return $array;
            }

            return [];
        }
        return [];
    }

    private function doCurl(string $url): string
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
                'Authorization: Bearer ' . $_ENV["SPOTTIX_LOOKUP_TOKEN"]
            ),
        ));

        $contents = curl_exec($curl);
        curl_close($curl);
        return $contents;
    }

    private function cacheFileName(string $url,string $extension="html"): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        $hash = substr(sha1($url), 0, 10);
        $sub_folder = sprintf("%s/%s", $this->cache_folder, $hash[0]);
        return sprintf("%s/%s.%s.%s", $sub_folder, $host, $hash, $extension);

    }
}