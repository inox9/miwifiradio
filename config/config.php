<?php
if (!defined("_KATE_MAIN")) die("You have no access to this file");
    $dbhost = "localhost";
    $dbuname = "miradio";
    $dbpass = "";
    $dbname = "miradio";
    $global_path = "/var/www/miradio/";
    $ffbin = "/usr/bin/ffmpeg";
    $domain = "xmr.trustno.one";
    $uploads_path = $global_path . 'uploads/playing/';
    // Sync with Master - send your playlist to masterserver
    $sync=0;
    $master_host="ximiraga.ru";
    $ident="myvps";
    $sync_pwd="mypathforsync";

?>
