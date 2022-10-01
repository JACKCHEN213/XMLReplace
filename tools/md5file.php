<?php

function build_file_md5($path, &$collection)
{
    if (!is_dir($path)) {
        return;
    }
    $dirs = scandir($path);
    foreach ($dirs as $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        $file_path = $path . $file;
        if (is_file($file_path)) {
            $collection[$file] = md5_file($file_path);
        }
    }
}

function compare($files1, $files2)
{
    foreach ($files1 as $file => $code) {
        if (!isset($files2[$file])) {
            echo "No File: {$file}\r\n";
        } else {
            if ($code != $files2[$file]) {
                echo "Not Equal: {$file}\r\n";
            }
        }
    }
}

$path1 = "C:\\Users\\REGMI G\\Desktop\\English_xml\\";
$path2 = "E:\\BaiduNetdiskDownload\\Kingdom Come Deliverence\\mods\\Black_Armor\\Localization\\Chineses_xml\\";
$md5_files1 = [];
$md5_files2 = [];

build_file_md5($path1, $md5_files1);
build_file_md5($path2, $md5_files2);

compare($md5_files1, $md5_files2);
