<?php
// Silence is golden

else{
    if(!defined('ABSPATH')) define('ABSPATH', dirname(__FILE__) . '/');
    $api = new Api();
    $str = parse_url($_SERVER['REQUEST_URI'], 6);
    parse_str($str, $result);
    print_r($api->apiConnection($result));
}
$helper = new Helpers();
$helper->backupsDir();