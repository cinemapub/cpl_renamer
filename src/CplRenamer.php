<?php

namespace Brightfish\CplRenamer;

use SimpleXMLIterator;

class CplRenamer
{
    public function __construct()
    {
    }

    public function rename(string $playlist_folder, string $output_folder){
        if(!is_dir($output_folder)){
            mkdir($output_folder);
        }
        $cpls = glob("$playlist_folder/*.xml");
        foreach($cpls as $cpl){
            $this->rename_cpl($cpl,$output_folder);
        }
        $this->
    }

    private function rename_cpl(string $filename, string $output_folder){
    }

    private function create_packlist(string $output_folder, string $output_file="PKL.xml"){

    }

    private function create_volindex(string $output_folder, string $output_file="VOLINDEX"){

    }

    private function create_assetmap(string $output_folder, string $output_file="ASSETMAP"){

    }

}