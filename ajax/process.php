<?php
require_once('../settings.php');
ini_set('display_errors', 'On');
error_reporting(E_ALL);
ini_set("memory_limit", "2048M");
set_time_limit(0);
$domain = $_REQUEST['url'];

if (empty($_REQUEST['url'])) {
    message("URL can't be blank", false);
}

$base = realpath($downloadDir);
$dPath = $base . '/' . $domain;


header('Content-type: application/json');

function is_valid_domain_name($domain_name) {
    return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
            && preg_match("/^.{1,253}$/", $domain_name) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name) ); //length of each label
}

function Download($url,  $domain) {
    global $base, $dPath;

    if (!file_exists($dPath)) {
        mkdir($dPath, 0777, true);
    }
    $tsPath = $dPath . '/';
    if (!file_exists($tsPath)) {
        mkdir($tsPath, 0777, true);
    }
    $filename = basename($url);
    if (file_exists($tsPath . $filename)) {
        return true;
    }
    $fp = fopen($tsPath . $filename, 'w+');
    //Here is the file we are downloading, replace spaces with %20
    $ch = curl_init(str_replace(" ", "%20", $url));
    if (!$ch) {
        return false;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);
    // write curl response to file
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    // get curl response
    $content = curl_exec($ch);
    curl_close($ch);
    fwrite($fp, $content);
    fclose($fp);
}

function message($msg, $success, $payload = []) {
    $resp = [
        'success' => $success,
        'message' => $msg
    ];
    if (!empty($payload)) {
        foreach ($payload as $key => $vl) {
            $resp[$key] = $vl;
        }
    }
    echo json_encode($resp);
    die();
}

if (!is_valid_domain_name($domain)) {
    message("Invalid Domain : {$domain}", false);
}

function curls($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

/* First let's see what pages we have */
$webArchiveUrl = "https://web.archive.org/cdx/search?url={$domain}" .
        "%2F&matchType=prefix&collapse=urlkey&output=json" .
        "&fl=original%2Cmimetype%2Ctimestamp%2Cendtimestamp" .
        "%2Cgroupcount%2Cuniqcount";

$response = curls($webArchiveUrl);
$decodedResponse = json_decode($response, true);


/* if no pages.. die then and there. web.archive.org returns [] n case of empty */
if (empty($decodedResponse)) {
    message("Domain {$domain} has no archive.", false);
}
// we don't need the first header. we need result
array_shift($decodedResponse);
$allUrls = [];
foreach ($decodedResponse as $resp) {
    // now we take the domain pages and search for saved versions according to date
    $domainPage = $resp[0];
    $pageType = $resp[1];
    Download("https://web.archive.org/web/" . $resp[2] . "if_/{$domainPage}", $domain);
}
 
/*$zip = new ZipArchive();
$zipBase = realpath("../zip/");
if ($zip->open($zipBase . '/' . $filename, ZIPARCHIVE::CREATE) !== TRUE) {
    die("Could not open archive");
}
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dPath));
foreach ($iterator as $key => $value) {
    if (file_exists(realpath($key)) && is_file(realpath($key)))
        $zip->addFile(realpath($key), basename($key));
}
$zip->close();*/
message("Domain {$domain} scraped successfully.", true);
