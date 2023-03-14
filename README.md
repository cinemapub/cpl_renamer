# Brightfish CPL renamer

![](assets/cpl_renamer.jpg)

## Problem

Advertising Accord now generates weekly CPL playlists that do not include the site code in the CPL name/folder name. This makes it impossible to ingest playlists for multiple sites (cinemas) in 1 repository. Site 1 would have a playlist called `ADV-PUB2022-10-05Avatar1OV2DS1-MAIN-P2` (for "Avatar 1" movie) and another site would have a different playlist for Avatar1 with exactly the same name, but with a different UUID. This makes it impossible to see which playlists are for which site.

## Solution

This script makes it possible to rename all playlists to include the site code, while keeping the CPLs valid (so adapting UUID and file hashes in PackingList and Assetmap)

## Procedure

The whole operation can be done with the `cpl_renamer.sh` scripts, which calls the PHP modules when it's required.

* choose a root folder for all your playlists, let's call the `$PLAYLIST_ROOT`
* create a separate folder per week, e.g. `$PLAYLIST_ROOT/2022W41`

### 1. `cpl_renamer.sh -i [week_folder] dropbox`
* put all received ZIP files in the folder e.g. 
  * `$PLAYLIST_ROOT/2022W41/playlists-2022-10-05-KANT.zip`
  * `$PLAYLIST_ROOT/2022W41/playlists-2022-10-05-KBXL.zip` 
  * ...

### 2. `cpl_renamer.sh -i [week_folder] unzip`
* run `cpl_renamer.sh --input "$PLAYLIST_ROOT/2022W41" unzip` 
* this will unzip all ZIP files into folders like 
    * `$PLAYLIST_ROOT/2022W41/orig/playlists-2022-10-05-KANT/ADV-PUB2022-10-05Avatar1OV2DS1`
  * `$PLAYLIST_ROOT/2022W41/orig/playlists-2022-10-05-KANT/ADV-PUB2022-10-05DontWorryDarlingOV2DS1`
  * `$PLAYLIST_ROOT/2022W41/orig/playlists-2022-10-05-KBXL/ADV-PUB2022-10-05Avatar1OV2DS1`
  * ...

### 3. `cpl_renamer.sh -i [week_folder] rename`
* then run `cpl_renamer.sh --input "$PLAYLIST_ROOT/2022W41" rename`
* this will rename all playlists and save them into folders :
  * `$PLAYLIST_ROOT/2022W41/renamed/KANT/KANT-PUB2022-10-05Avatar1OV2DS1`
  * `$PLAYLIST_ROOT/2022W41/renamed/KANT/KANT-PUB2022-10-05DontWorryDarlingOV2DS1`
  * `$PLAYLIST_ROOT/2022W41/renamed/KBXL/KBXL-PUB2022-10-05Avatar1OV2DS1`
* so 1 folder per site and then a subfolder per playlist, with the site code added in the new name.

### 4. `cpl_renamer.sh -i [week_folder] rezip`
* then run `cpl_renamer.sh --input "$PLAYLIST_ROOT/2022W41" rezip`
* this will create a ZIP file per site, with all renamed playlists
  * `$PLAYLIST_ROOT/2022W41/renamed/renamed_2022W41_KANT.zip`
  * `$PLAYLIST_ROOT/2022W41/renamed/renamed_2022W41_KBXL.zip`
* this will also create an Excel export `export.all.csv` with all sessions and all spots

## Script Usage

```bash
Program : cpl_renamer.sh  by p.forret@brightfish.be
Version : v0.5.3 (2023-02-21 14:47)
Purpose : process folder with Brightfish playlists
Usage   : cpl_renamer.sh [-h] [-q] [-v] [-f] [-l <log_dir>] [-t <tmp_dir>] [-D <DROPBOX_FOLDER>] [-S <MAILGUN_SENDER>] [-R <MAILGUN_RECEIVERS>] [-M <MAILGUN_DOMAIN>] [-i <input>] [-z <zip_prefix>] [-c <cpl_prefix>] <action>
Flags, options and parameters:
    -h|--help        : [flag] show usage [default: off]
    -q|--quiet       : [flag] no output [default: off]
    -v|--verbose     : [flag] also show debug messages [default: off]
    -f|--force       : [flag] do not ask for confirmation (always yes) [default: off]
    -l|--log_dir <?> : [option] folder for log files   [default: /home/pforret/log/cpl_renamer]
    -t|--tmp_dir <?> : [option] folder for temp files  [default: /tmp/cpl_renamer]
    -D|--DROPBOX_FOLDER <?>: [option] Dropbox root folder
    -S|--MAILGUN_SENDER <?>: [option] From: address for email
    -R|--MAILGUN_RECEIVERS <?>: [option] To: address for email
    -M|--MAILGUN_DOMAIN <?>: [option] Mailgun sender domain
    -i|--input <?>   : [option] input folder with the playlist zips
    -z|--zip_prefix <?>: [option] zip file prefix  [default: playlists-]
    -c|--cpl_prefix <?>: [option] playlist folder prefix  [default: ADV-]
    <action>         : [choice] action to perform  [options: dropbox,unzip,rename,rezip,send,check,env,update]
```

## PHP Library usage

The PHP library can also be called directly from another PHP script. An example of this can be found in the wrapper script `rename_folder.php`, as called from the `process_folder.sh` script.

```php
<?php
include __DIR__ . "/../vendor/autoload.php";

use Brightfish\CplRenamer\CplRenamer;

$input_folder=$argv[1] ?? "";
$output_folder=$argv[2] ?? "";
$site_code=$argv[3] ?? "";
if(!$input_folder || !$output_folder || !$site_code){
    print "------ rename all playlists of a site\n";
    print "Usage: $argv[0] [input_folder] [output_folder] [sitecode]\n";
    print "Example: $argv[0] week40/orig/KBXL week40/renamed/KBXL KBXL\n";
    exit(1);
}
$renamer = new CplRenamer($site_code);
$renamer->renameMoviePlaylists($input_folder,$output_folder);
```
## Requirements

### Bash requirements
* awk
* unzip / zip (`sudo apt install zip`)

### PHP Requirements
* PHP 8.0 or higher
* PHP ext-curl
* PHP ext-dom
* PHP ext-json
* PHP ext-simplexml
* `sudo apt install php8.0-xml` should do the trick