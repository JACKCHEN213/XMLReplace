<?php

namespace tools;

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
            'no_change' => [], // 没有改变的文件
            'change' => [], // 改变了的文件
            'not_exists' => [], // 目前目录不存在的源文件
            'surplus' => [], // 目标目录多余的文件
        ];
        foreach ($template_files as $file => $code) {
            if (!isset($source_files[$file])) {
                $compare['not_exists'][] = $file;
            } else {
                if ($code != $source_files[$file]) {
                    $compare['change'][] = $file;
                } else {
                    $compare['no_change'][] = $file;
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
