<?php
header("content-type: text/javascript");
$callback = $_GET['callback'];
$url = $_GET['url'];
$method = $_GET['method'];
$content = file_get_contents($url);
switch($method) {
    case 'vol':
        foreach (explode(';', $content) as $data) {
            if (preg_match("/var cs='(.*)'/i", $data, $matches)) {
                //echo ($matches[1]) . "<br>";
                $obj->cs = $matches[1];
                break;
            }
        }
        echo $callback . "(" . json_encode($obj) . ");";
    break;
    case 'intro':
    break;
}
//echo $callback . '(' . json_encode($urls) . ');';
