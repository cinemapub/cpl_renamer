<?php
include __DIR__ . "/../vendor/autoload.php";

use Brightfish\CplRenamer\CplRenamer;
use Brightfish\CplRenamer\Exceptions\InputMissingException;

$input_site_folder=$argv[1] ?? "";
$output_site_folder=$argv[2] ?? "";
$site_code=$argv[3] ?? "";
if(!$input_site_folder || !$output_site_folder || !$site_code){
    print "Usage: $argv[0] [input_folder] [output_folder] [sitecode]\n";
    print "Example: $argv[0] week40/orig/KBXL/ADV-PUB2022-10-05Avatar1OV2DS1 week40/renamed/KBXL KBXL\n";
    exit(1);
}
$renamer = new CplRenamer($site_code);

try {
    $renamer->renameSitePlaylists($input_site_folder, $output_site_folder);
} catch (InputMissingException $e) {
}
