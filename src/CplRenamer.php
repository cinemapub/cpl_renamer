<?php

namespace Brightfish\CplRenamer;

use Brightfish\CplRenamer\Exceptions\InputInvalidException;
use Brightfish\CplRenamer\Exceptions\InputMissingException;
use Brightfish\CplRenamer\Exceptions\OutputFailedException;
use Brightfish\CplRenamer\Helpers\CplDigest;
use Brightfish\CplRenamer\Parsers\CplParser;
use Brightfish\CplRenamer\Writers\CplWriter;
use Brightfish\CplRenamer\Writers\MapWriter;
use Brightfish\CplRenamer\Writers\PklWriter;
use Brightfish\CplRenamer\Writers\VolWriter;
use League\Csv\Writer;
use Pforret\PhpMarkdownWriter\PhpMarkdownWriter;

class CplRenamer
{
    private string $site_code;
    /**
     * @var mixed|string
     */
    private string $weekNr;
    private PhpMarkdownWriter $doc;
    private array $required_spots;
    private array $export;

    public function __construct(string $site_code)
    {
        $this->site_code = $site_code;
        $this->required_spots = [];
        $this->export = [];
    }

    /**
     * @throws InputMissingException
     */
    public function renameSitePlaylists(string $input_site_folder, string $output_site_folder)
    {
        if (!is_dir($input_site_folder)) {
            // test/2022C41/orig/playlists-2022-10-05-KANT/ADV-PUB2022-10-05AfterEverHappyOV2DS1
            throw new InputMissingException("Input folder not found: [$input_site_folder]");
        }
        $this->weekNr = $this->guessWeekNr($input_site_folder);
        $this->doc = new PhpMarkdownWriter("$output_site_folder/report.$this->site_code.$this->weekNr.md");
        $this->doc->h1("WEEK $this->weekNr");
        $this->doc->bullet("Run at " . date("c"));
        $this->doc->bullet("Run on " . gethostname());
        $this->doc->bullet("Run by " . get_current_user());
        $this->doc->h2("PLID = PlayList ID");
        $this->doc->bullet("PLID = `999DDD` With `999` = # spots in playlist and `DDD` = Digest/Hash of all spot UUIDs. So `0005C80` = 5 spots, hash C80");
        $this->doc->bullet("If 2 playlists have the same PLID, they have the same contents (spots).");
        $this->doc->bullet("The PLID is not given to a movie, but to each individual CPL. This means that for Kinepolis, as there are 2 playlists for a movie (EXCH/MAIN), you will see 2 different PLIDs for 1 movie.");
        $movie_folders = glob("$input_site_folder/*", GLOB_ONLYDIR);
        $playlist_names = [];
        foreach ($movie_folders as $orig_movie_playlist) {
            $playlist_name = basename($orig_movie_playlist);
            $new_name = str_replace("ADV-PUB", "$this->site_code-", $playlist_name);
            $new_name = $this->replaceDateByWeek($new_name);
            $new_name = $this->replaceSuffixes($new_name);
            $this->doc->h2("FOLDER: `$new_name`");
            $new_movie_playlist = "$output_site_folder/$new_name";
            printf("* %-50s -> %-40s\n", basename($orig_movie_playlist), basename($new_movie_playlist));
            try {
                $names_with_digest = $this->renameMoviePlaylists($orig_movie_playlist, $new_movie_playlist);
                $playlist_names = array_merge($playlist_names, $names_with_digest);
            } catch (InputInvalidException|InputMissingException|OutputFailedException $e) {
            }
        }
        sort($playlist_names);
        $this->doc->h2("All playlists");
        $last_prefix = "";
        foreach ($playlist_names as $playlist_name) {
            $this_prefix = substr($playlist_name, 0, 10);
            if ($this_prefix <> $last_prefix) {
                $this->doc->fixed("---");
                $last_prefix = $this_prefix;
            }
            $this->doc->fixed($playlist_name);
        }
        $this->doc->h2("All required spots (in alphabetical order)");
        ksort($this->required_spots);
        foreach ($this->required_spots as $folder => $name) {
            $this->doc->fixed("$folder = $name");
        }
        $writer = Writer::createFromPath("$output_site_folder/export.$this->site_code.$this->weekNr.csv", 'w+');
        $writer->setDelimiter(";");
        $writer->insertOne(array_keys($this->export[0])); //using an array
        $writer->insertAll($this->export); //using an array

    }

    /**
     * @throws OutputFailedException
     * @throws InputMissingException
     * @throws InputInvalidException
     */
    public function renameMoviePlaylists(string $movie_playlists, string $output_site_folder): array
    {
        if (!is_dir($movie_playlists)) {
            throw new InputMissingException("Input folder not found: [$movie_playlists]");
        }
        if (!is_dir($output_site_folder)) {
            mkdir($output_site_folder, 0777, true);
        }
        $cpls = glob("$movie_playlists/ADV*.xml");
        $names_with_digest = [];
        foreach ($cpls as $cpl) {
            $cpl_name = basename($cpl, ".xml");
            $names_with_digest[] = $this->renameSinglePlaylist($cpl, $output_site_folder);
        }
        $this->createFolderPackList($output_site_folder);
        $this->createFolderVolIndex($output_site_folder);
        $this->createFolderAssetMap($output_site_folder);
        return $names_with_digest;
    }

    /**
     * @throws OutputFailedException
     * @throws InputMissingException
     * @throws InputInvalidException
     */
    private function renameSinglePlaylist(string $filename, string $output_folder): string
    {
        if (!file_exists($filename)) {
            throw new InputMissingException("Input file not found: [$filename]");
        }
        // ADV-PUB2022-10-05Avatar1OV2DS1 -> KANT-PUB2022-10-05Avatar1OV2DS1
        //                                   KANT-PUB2022-10-26DCSuperPetsNL2DS1-MAIN-P2
        $cpl = new CplWriter();
        $cpl->loadFromFile($filename);
        $digest = $cpl->calculateCplDigest();
        $needed_spots = $cpl->getSpotList();
        $orig_name = basename($filename, ".xml");
        $new_name = str_replace("ADV-PUB", "$this->site_code-", $orig_name);
        $new_name = $this->replaceDateByWeek($new_name);
        $new_name = $this->replaceSuffixes($new_name);
        $this->doc->h3("CPL: `$new_name`");
        // CPL: KANT-005C80-2022C44b-AfterEverHappy-OV-ECH-P1
        $parts = explode("-", $new_name);
        $cpl->rename($new_name);
        $cpl->saveToFile("$output_folder/$new_name.xml");
        $this->doc->bold("Required spots (in alphabetical order)");
        foreach ($needed_spots as $folder => $name) {
            $this->doc->fixed("$folder = $name");
            $this->export[] = [
                "site_code" => $this->site_code,
                "week_code" => $this->weekNr,
                "feature" => $this->cleanupTitle($parts[2]),
                "lang" => $parts[3],
                "segment" => $parts[4],
                "playlist_name" => $new_name,
                "playlist_id" => "ID:$digest",
                "spot_folder" => $folder,
                "spot_name" => $name,
            ];
        }
        $this->required_spots = array_merge($this->required_spots, $needed_spots);
        return $new_name;
    }


    /**
     * @throws OutputFailedException
     * @throws InputMissingException
     * @throws InputInvalidException
     */
    private function createFolderPackList(string $output_folder): bool
    {
        if (!is_dir($output_folder)) {
            throw new InputMissingException("Output folder not found: [$output_folder]");
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

    /**
     * @throws OutputFailedException
     * @throws InputInvalidException
     * @throws InputMissingException
     */
    private function createFolderVolIndex(string $output_folder): bool
    {
        if (!is_dir($output_folder)) {
            throw new InputMissingException("Output folder not found: [$output_folder]");
        }
        $vol = new VolWriter();
        return $vol->saveToFile("$output_folder/VOLINDEX");
    }

    /**
     * @throws OutputFailedException
     * @throws InputInvalidException
     * @throws InputMissingException
     */
    private function createFolderAssetMap(string $output_folder): bool
    {
        if (!is_dir($output_folder)) {
            throw new InputMissingException("Output folder not found: [$output_folder]");
        }
        $map = new MapWriter();
        foreach (glob("$output_folder/*.xml") as $xml_file) {
            if (basename($xml_file) == "PKL.xml") {
                $map->addAsset($xml_file, "PKL");
            } else {
                $map->addAsset($xml_file);
            }
        }
        return $map->saveToFile("$output_folder/ASSETMAP");
    }

    private function guessWeekNr(string $path): string
    {
        $parts = explode("/", $path);
        $response = "";
        foreach ($parts as $part) {
            if (substr($part, 0, 4) == date("Y")) {
                $response = $part;
            }
        }
        return $response;
    }

    private function replaceSuffixes(string $input): string
    {
        $suffixes = [
            "NL2DS1" => "-NL",
            "OV2DS1" => "-OV",
            "FR2DS1" => "-FR",
        ];
        return str_replace(array_keys($suffixes), array_values($suffixes), $input);
    }

    private function replaceDateByWeek(string $input): string
    {
        [$year, $shortWeek] = explode("C", $this->weekNr, 2);
        return preg_replace("/\d\d\d\d-\d\d-\d\d/", "$shortWeek-", $input);
    }

    private function cleanupTitle(string $title): string
    {
        $new = preg_replace("/^The/", "", $title);
        $new = preg_replace("/^De/", "", $new);
        $new = preg_replace("/^La/", "", $new);
        $new = preg_replace("/^Het/", "", $new);
        return $new;
    }
}