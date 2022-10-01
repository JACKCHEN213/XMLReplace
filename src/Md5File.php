<?php

namespace src;

class Md5File
{
    private static function getFilesMd5($path): array
    {
        $ret_files = [];
        if (!is_dir($path)) {
            return $ret_files;
        }
        $dirs = scandir($path);
        foreach ($dirs as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $file_path = $path . $file;
            if (is_file($file_path)) {
                $ret_files[$file] = md5_file($file_path);
            }
        }
        return $ret_files;
    }

    private static function getCompareFiles($template_files, $source_files): array
    {
        $compare = [
            'not_exists' => [],
            'change' => [],
            'surplus' => [],
        ];
        foreach ($template_files as $file => $code) {
            if (!isset($source_files[$file])) {
                $compare['not_exists'][] = $file;
            } else {
                if ($code != $source_files[$file]) {
                    $compare['change'][] = $file;
                }
            }
        }
        foreach ($source_files as $file => $code) {
            if (!isset($template_files[$file])) {
                $compare['surplus'][] = $file;
            }
        }
        return $compare;
    }

    public static function getDiffFiles($template_path, $source_path): array
    {
        $template_files = self::getFilesMd5($template_path);
        $source_files = self::getFilesMd5($source_path);
        return self::getCompareFiles($template_files, $source_files);
    }
}
