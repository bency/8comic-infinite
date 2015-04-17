<?php
include 'init.inc.php';
header("content-type: text/javascript");
$callback = $_GET['callback'];
$url = $_GET['url'];
$method = $_GET['method'];
$content = iconv('big5', 'UTF-8', file_get_contents($url));
foreach (explode(';', $content) as $data) {
    if (preg_match("/var cs='(.*)'/i", $data, $matches)) {
        $cs = $matches[1];
        break;
    }
    if (preg_match("#\<title\>(.*)\<\/title\>#i", $data, $matches)) {
        $title = explode(' ', $matches[1])[0];
    }
}
$comic = new Comics($cs, $url, $title);
switch($method) {
    case 'vol':
        foreach (explode(';', $content) as $data) {
            if (preg_match("/var cs='(.*)'/i", $data, $matches)) {
                $cs = $matches[1];
                break;
            }
            if (preg_match("#\<title\>(.*)\<\/title\>#i", $data, $matches)) {
                $title = explode(' ', $matches[1])[0];
            }
        }
        echo $callback . "(" . json_encode($comic) . ");";
    break;
    case '8comic':
        echo $callback . "({$comic->series_id}, {$comic->total_hash}, {$comic->vol});";
    break;
}
