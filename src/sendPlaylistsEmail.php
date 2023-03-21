<?php
include __DIR__ . "/../vendor/autoload.php";

use Brightfish\CplRenamer\Email\SendMailgun;
use Mailgun\Message\Exceptions\TooManyRecipients;
use Psr\Http\Client\ClientExceptionInterface;

$subject = $argv[1] ?? "";
$outputFolder = $argv[2] ?? "";
if (!$subject) {
    print("Need subject!");
    exit;
}
$sender = new SendMailgun();
$attachments = array_merge(glob("$outputFolder/*.csv"), glob("$outputFolder/*.zip"));
$body = "";
$cinemaFolders = getSubfolders($outputFolder);
foreach ($cinemaFolders as $cinemaFolder) {
    $cinema = basename($cinemaFolder);
    $playlistFolders = getSubfolders($cinemaFolder);
    $countPlaylists = count($playlistFolders);
    $body .= "$cinema: $countPlaylists playlists\n";
}
$body .= "

_____
Peter Forret - p.forret@brightfish.be
";
printf("Send to      : %s\n", $_ENV["MAILGUN_RECEIVERS"]);
printf("Send subject : %s\n", $subject);
echo $body;
try {
    $sender->send($_ENV["MAILGUN_RECEIVERS"], $subject, $body, $attachments);
} catch (TooManyRecipients|ClientExceptionInterface $e) {
}


function getSubfolders(string $folder)
{
    $all = glob("$folder/*");
    $response = [];
    foreach ($all as $item) {
        if (basename($item) == ".") continue;
        if (basename($item) == "..") continue;
        if (!is_dir($item)) continue;
        $response[] = $item;
    }
    return $response;
}