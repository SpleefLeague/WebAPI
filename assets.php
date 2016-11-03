<?php

    header('Content-Type: text/plain');

    $path = './assets/'.$_GET['path'];

    $exts = explode(".", $path);
    $extension = $exts[count($exts)-1];

    $type = 'application/json';

    $mimes = [
        'txt' => 'text/plain',
        'js' => 'application/javascript',
        'css' => 'text/css'
    ];
    
    $type = $mimes[strtolower($extension)];

    header('Content-Type: ' + $type);

    $f = fopen($path, 'r');
    $data = fread($f, filesize($path));
    fclose($f);

    echo $data;
?>