# Brightfish CPL renamer

![](assets/unsplash.writer.jpg)

## Problem

Advertising Accord now generates weekly CPL playlists that do not include the site code in the CPL name/folder name. This makes it impossible to ingest playlists for multiple sites (cinemas) in 1 repository. Site 1 would have a playlist called `ADV-PUB2022-10-05Avatar1OV2DS1-MAIN-P2` (for "Avatar 1" movie) and another site would have a different playlist for Avatar1 with exactly the same name, but with a different UUID. This makes it impossible to see which playlists are for which site.

## Solution

This script makes it possible to rename all playlists to include the site code, while keeping the CPLs valid (so adapting UUID and file hashes in PackingList and Assetmap)

## Procedure

The whole operation can be done with the `process_folder.sh` scripts, which calls the PHP modules when it's required.

* choose a root folder for all your playlists, let's call the `$PLAYLIST_ROOT`
* create a separate folder per week, e.g. `$PLAYLIST_ROOT/2022W41`
* put all received ZIP files in the folder e.g. 
  * `$PLAYLIST_ROOT/2022W41/playlists-2022-10-05-KANT.zip`
  * `$PLAYLIST_ROOT/2022W41/playlists-2022-10-05-KBXL.zip` 
  * ...
* run `cpl_renamer.sh --input "$PLAYLIST_ROOT/2022W41" unzip` 
* this will unzip all ZIP files into folders like 
    * `$PLAYLIST_ROOT/2022W41/orig/playlists-2022-10-05-KANT/ADV-PUB2022-10-05Avatar1OV2DS1`
  * `$PLAYLIST_ROOT/2022W41/orig/playlists-2022-10-05-KANT/ADV-PUB2022-10-05DontWorryDarlingOV2DS1`
  * `$PLAYLIST_ROOT/2022W41/orig/playlists-2022-10-05-KBXL/ADV-PUB2022-10-05Avatar1OV2DS1`
  * ...
* then run `cpl_renamer.sh --input "$PLAYLIST_ROOT/2022W41" rename`
* this will rename all playlists and save them into folders :
  * `$PLAYLIST_ROOT/2022W41/renamed/KANT/KANT-PUB2022-10-05Avatar1OV2DS1`
  * `$PLAYLIST_ROOT/2022W41/renamed/KANT/KANT-PUB2022-10-05DontWorryDarlingOV2DS1`
  * `$PLAYLIST_ROOT/2022W41/renamed/KBXL/KBXL-PUB2022-10-05Avatar1OV2DS1`
* so 1 folder per site and then a subfolder per playlist, with the site code added in the new name.
* then run `cpl_renamer.sh --input "$PLAYLIST_ROOT/2022W41" rezip`
* this will create a ZIP file per site, with all renamed playlists
  * `$PLAYLIST_ROOT/2022W41/renamed/renamed_2022W41_KANT.zip`
  * `$PLAYLIST_ROOT/2022W41/renamed/renamed_2022W41_KBXL.zip`


## Script Usage

```bash
Program : cpl_renamer.sh  by p.forret@brightfish.be
Version : v0.2.0 (2022-10-10 17:28)
Purpose : process folder with Kinepolis playlists
Usage   : cpl_renamer.sh [-h] [-q] [-v] [-f] [-l <log_dir>] [-t <tmp_dir>] [-i <input>] [-z <zip_prefix>] [-c <cpl_prefix>] <action>
Flags, options and parameters:
    -h|--help        : [flag] show usage [default: off]
    -q|--quiet       : [flag] no output [default: off]
    -v|--verbose     : [flag] also show debug messages [default: off]
    -f|--force       : [flag] do not ask for confirmation (always yes) [default: off]
    -l|--log_dir <?> : [option] folder for log files   [default: /home/forretp/log/cpl_renamer]
    -t|--tmp_dir <?> : [option] folder for temp files  [default: /tmp/cpl_renamer]
    -i|--input <?>   : [option] input folder with the playlist zips  [default: .]
    -z|--zip_prefix <?>: [option] zip file prefix  [default: playlists-]
    -c|--cpl_prefix <?>: [option] playlist folder prefix  [default: ADV-]
    <action>         : [choice] action to perform  [options: unzip,rename,rezip,check,env,update]
```

## PHP Library usage

The PHP library can also be called directly from another PHP script. An example of this can be found in the wrapper script `rename_folder.php`, as called from the `process_folder.sh` script.

```php
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
```
## Requirements

### Bash requirements
* awk
* unzip

### PHP Requirements
* PHP 7.4 or higher
* PHP ext-simplexml
* PHP ext-dom
* `sudo apt install php-xml` should do the trick