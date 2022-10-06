# Brightfish CPL renamer

## Problem

Advertising Accord now generates weekly CPL playlists that do not include the site code in the CPL name/folder name. This makes it impossible to ingest playlists for multiple sites (cinemas) in 1 repository. Site 1 would have a playlist called `ADV-PUB2022-10-05Avatar1OV2DS1-MAIN-P2` (for "Avatar 1" movie) and another site would have a different playlist for Avatar1 with exactly the same name, but with a different UUID. This makes it impossible to see which playlists are for which site.

## Solution

This script makes it possible to rename all playlists to include the site code, while keeping the CPLs valid (so adapting UUID and file hashes in PackingList and Assetmap)

## Procedure

* imagine all playlists will be saved/rename in a certain folder, let's call the `$PLAYLIST_ROOT`
* create a separate folder per week, e.g. `$PLAYLIST_ROOT/2022W40`
* put all received ZIP files in the folder e.g. `playlists-2022-10-05-KANT.zip` ...
* run `process_folders.sh --input "$PLAYLIST_ROOT/2022W40" unzip` 
* this will unzip all ZIP files into folders like `$PLAYLIST_ROOT/2022W40/orig/playlists-2022-10-05-KANT/ADV-PUB2022-10-05Avatar1OV2DS1` 
* then run `process_folders.sh --input "$PLAYLIST_ROOT/2022W40" rename`
* this will rename all playlists and save them into folders `$PLAYLIST_ROOT/2022W40/renamed/KANT/KANT-PUB2022-10-05Avatar1OV2DS1`, so 1 folder per site and then a subfolder per playlist, with the site code als in the new name.

## Script Usage
```
Program : process_folder.sh  by p.forret@brightfish.be
Version : v0.1.1 (2022-10-05 22:08)
Purpose : process folder with Kinepolis playlists
Usage   : process_folder.sh [-h] [-q] [-v] [-f] [-l <log_dir>] [-t <tmp_dir>] [-i <input>] [-i <prefix>] <action>
Flags, options and parameters:
    -h|--help        : [flag] show usage [default: off]
    -q|--quiet       : [flag] no output [default: off]
    -v|--verbose     : [flag] also show debug messages [default: off]
    -f|--force       : [flag] do not ask for confirmation (always yes) [default: off]
    -l|--log_dir <?> : [option] folder for log files   [default: /home/forretp/log/process_folder]
    -t|--tmp_dir <?> : [option] folder for temp files  [default: /tmp/process_folder]
    -i|--input <?>   : [option] input folder with the playlist zips  [default: .]
    -i|--prefix <?>  : [option] zip file prefix  [default: playlists-]
    <action>         : [choice] action to perform  [options: unzip,rename,check,env,update]
```

## Requirements
* PHP 7.4 or higher
* PHP ext-simplexml
* PHP ext-dom
* `sudo apt install php-xml` should do the trick