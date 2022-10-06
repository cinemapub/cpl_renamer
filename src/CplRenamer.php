<?php

namespace Brightfish\CplRenamer;

use Brightfish\CplRenamer\Helpers\CplDigest;
use Brightfish\CplRenamer\Parsers\CplParser;
use Brightfish\CplRenamer\Writers\CplWriter;
use Brightfish\CplRenamer\Writers\MapWriter;
use Brightfish\CplRenamer\Writers\PklWriter;
use Brightfish\CplRenamer\Writers\VolWriter;

class CplRenamer
{
    private string $site_code;

    public function __construct(string $site_code)
    {
        $this->site_code = $site_code;
    }

    public function rename(string $playlist_folder, string $output_folder): bool
    {
        $success = true;
        if (!is_dir($output_folder)) {
            mkdir($output_folder, 0777, true);
        }
        $cpls = glob("$playlist_folder/ADV*.xml");
        foreach ($cpls as $cpl) {
            $success = $success && $this->rename_cpl($cpl, $output_folder);
        }
        return $success && $this->create_packlist($output_folder) && $this->create_volindex($output_folder) && $this->create_assetmap($output_folder);
    }

    private function rename_cpl(string $filename, string $output_folder): bool
    {
        if (!file_exists($filename)) {
            return false;
        }
        // ADV-PUB2022-10-05Avatar1OV2DS1 -> KANT-PUB2022-10-05Avatar1OV2DS1
        $new_name = str_replace("ADV-", "$this->site_code-", basename($filename));
        $cpl = new CplWriter();
        $cpl->loadFromFile($filename);
        $cpl->rename(basename($new_name,".xml"));
        return $cpl->saveToFile("$output_folder/$new_name");
    }

    private function create_packlist(string $output_folder): bool
    {
        if (!is_dir($output_folder)) {
            return false;
        }
        $pkl_name = "PKL.xml";
        $cpl_name = basename($output_folder);
        $pkl = new PklWriter($cpl_name);

        $cpls = glob("$output_folder/*.xml");
        foreach ($cpls as $cpl_file) {
            if (basename($cpl_file) == $pkl_name) continue;
            $pkl->addAsset($cpl_file, (new CplParser($cpl_file))->Id(), CplDigest::file($cpl_file), CplDigest::size($cpl_file));
        }
        return $pkl->saveToFile("$output_folder/$pkl_name");
    }

    private function create_volindex(string $output_folder): bool
    {
        $vol = new VolWriter();
        return $vol->saveToFile("$output_folder/VOLINDEX");
    }

    private function create_assetmap(string $output_folder): bool
    {
        $map = new MapWriter();
        foreach (glob("$output_folder/*.xml") as $xml_file) {
            $map->addAsset($xml_file);
        }
        return $map->saveToFile("$output_folder/ASSETMAP");
    }

}