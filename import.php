<?php

function createIniFile($path) {

    $configFile = new SplFileObject($path . DIRECTORY_SEPARATOR . 'theme', 'w');
    $base = array(
        'Name' => 'Twitch Icons via SpazTwitch',
        'Description' => 'Thanks to twitchemotes.com for the API.',
        'Icon' => '25.png',
        'Author' => 'Omnifox'
    );

    foreach ($base as $k => $v) {
        $configFile->fwrite("{$k}={$v}" . PHP_EOL);
    }

    $configFile->fwrite(PHP_EOL);

    return $configFile;
}

define('PIDGIN_HOME', getenv('HOME') . '/.purple/smileys');

$images = array();
$json = file_get_contents('http://twitchemotes.com/api_cache/v2/global.json');
$jso = json_decode($json);

$templateUrl = $jso->template->small;

foreach ($jso->emotes as $text => $image) {
    $images[$text] = $image->image_id;
}

/*
 * If our container folder already exists, remove the old theme file. To spare
 * bandwidth, we will only re-download images if they do not exist.
 */
if (is_dir(PIDGIN_HOME . '/SpazTwitch')) {

    if (file_exists(PIDGIN_HOME . '/SpazTwitch/theme')) {
        unlink(PIDGIN_HOME . '/SpazTwitch/theme');
    }
} elseif (!mkdir(PIDGIN_HOME . '/SpazTwitch', 0755, true)) {
    die('Failed to create location.');
}
$dirPath = realpath(PIDGIN_HOME . '/SpazTwitch');
$conf = createIniFile($dirPath);

$conf->fwrite('[default]' . PHP_EOL);
foreach ($images as $keyword => $imageID) {
    $line = "{$imageID}.png\t\t{$keyword}" . PHP_EOL;
    $conf->fwrite($line);


    /*
     * Download the image file.
     */
    $imgFile = preg_replace('/{image_id}/', $imageID, $templateUrl);

    /*
     * PHP cannot handle URIs without protocol, so add http.
     */
    if (substr($imgFile, 0, 2) == '//') {
        $imgFile = 'http:' . $imgFile;
    }

    /*
     * If the image was not downloaded, download it.
     */
    if (!file_exists($dirPath . DIRECTORY_SEPARATOR . $imageID . '.png')) {
        echo 'Downloading ' . $imageID . PHP_EOL;
        file_put_contents($dirPath . DIRECTORY_SEPARATOR . $imageID . '.png', file_get_contents($imgFile));
    }
    
}
echo 'Done';
$conf->fwrite(PHP_EOL);
$conf = null;   //close file
?>