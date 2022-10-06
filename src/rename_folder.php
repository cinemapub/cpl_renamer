<?php
include __DIR__ . "/../vendor/autoload.php";

use Brightfish\CplRenamer\CplRenamer;

$input_folder=$argv[1] ?? "";
$output_folder=$argv[2] ?? "";
$sitecode=$argv[3] ?? "";
if(!$input_folder || !$output_folder || !$sitecode){
    print "Usage: $argv[0] [input_folder] [output_folder] [sitecode]\n";
    print "Example: $argv[0] week40/orig/ADV-PUB2022-10-05Avatar1OV2DS1 week40/renamed/KBXL-PUB2022-10-05Avatar1OV2DS1 KBXL\n";
    exit(1);
}
$renamer = new CplRenamer($sitecode);
$renamer->rename($input_folder,$output_folder);