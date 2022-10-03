<?php

namespace src;

use Exception;
use tools\Logger;
use tools\Md5File;
use tools\XML;

class Replace
{
    private string $template_path;
    private string $source_path;
    private string $output_path;
    private string $translate_path;

    public function __construct()
    {
        $this->template_path = config('template_path');
        $this->source_path = config('source_path');
        $this->output_path = config('output_path');
        $this->clearDir($this->output_path);
        $this->translate_path = config('translate_path');
        $this->clearDir($this->translate_path);
    }

    private function deletePath($path)
    {
        if (!is_dir($path)) {
            return;
        }
        $dirs = scandir($path);
        foreach ($dirs as $dir) {
            if ($dir == '.' || $dir == '..') {
                continue;
            }
            $file_path = $path . DIRECTORY_SEPARATOR . $dir;
            if (is_file($file_path)) {
                unlink($file_path);
            } else {
                $this->deletePath($file_path);
                rmdir($file_path);
            }
        }
        rmdir($path);
    }

    private function clearDir($path)
    {
        if (is_dir($path)) {
            $this->deletePath($path);
        }
        mkdir($path);
    }

    private function getDiffFiles(): array
    {
        return Md5File::getDiffFiles($this->template_path, $this->source_path);
    }

    private function getFileContent($file): array
    {
        try {
            return XML::read($file);
        } catch (Exception $e) {
            return [];
        }
    }

    private function move($src_path, $dst_path)
    {
        copy($src_path, $dst_path);
    }

    private function noChange($files)
    {
        if (!$files) {
            return;
        }
        Logger::success("没有改变的文件: " . implode(', ', $files));
        foreach ($files as $key => $file) {
            Logger::debug(sprintf("正在移动文件: %s, %s/%s", $file, ($key + 1), count($files)));
            $this->move($this->template_path . $file, $this->output_path . $file);
        }
    }

    private function covetToPlain(&$content): array
    {
        $cells = [];
        foreach ($content as $item) {
            $cells[$item['var']] = [$item['lang'], $item['trans']];
        }
        return $cells;
    }

    private function compareAndReplace($file): array
    {
        $ret_compare = [];
        $to_translate = [];
        $template_content = $this->getFileContent($this->template_path . $file);
        $source_content = $this->getFileContent($this->source_path . $file);
        $src = $this->covetToPlain($template_content);
        $dst = $this->covetToPlain($source_content);
        unset($template_content, $source_content);

        foreach ($src as $var => $item) {
            if (!(isset($dst[$var]) && config('follow_source'))) {
                $ret_compare[] = [
                    'var' => $var,
                    'lang' => $item[0],
                    'trans' => $item[1],
                ];
                continue;
            }
            $ret_compare[] = [
                'var' => $var,
                'lang' => $dst[$var][0],
                'trans' => $dst[$var][1],
            ];
            $to_translate[$var] = $dst[$var][0];
        }
        foreach ($dst as $var => $item) {
            if (!isset($src[$var])) {
                $ret_compare[] = [
                    'var' => $var,
                    'lang' => $item[0],
                    'trans' => $item[1],
                ];
                $to_translate[$var] = $item[0];
            }
        }
        return ['compare' => $ret_compare, 'translate' => $to_translate];
    }

    private function buildXMLStructure(&$content): array
    {
        $structure = [];
        foreach ($content as $cells) {
            $_cell = [];
            foreach ($cells as $cell) {
                $_cell[] = [
                    'Cell' => $cell,
                ];
            }
            $structure[] = [
                'Row' => $_cell,
            ];
        }
        return $structure;
    }

    private function writeToXmlFile($content, $file): void
    {
        $content = XML::write($content);
        file_put_contents($file, $content);
    }

    private function writeToJsonFile($content, $file)
    {
        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        file_put_contents($file, json_encode($content, $flags));
    }

    private function change($files)
    {
        Logger::error("改变的文件: " . implode(', ', $files));
        foreach ($files as $key => $file) {
            // TODO: 1. 进度条控件; 2. 多线程处理
            Logger::debug(sprintf("正在处理文件: %s, %s/%s", $file, ($key + 1), count($files)));
            $ret = $this->compareAndReplace($file);

            // 写到输出目录
            Logger::debug("正在将输出文件写入: " . $this->output_path . $file);
            $structure_content = $this->buildXMLStructure($ret['compare']);
            $this->writeToXmlFile($structure_content, $this->output_path . $file);
            // 写到待翻译目录
            if ($ret['translate']) {
                Logger::debug("正在将待翻译文件写入: " . $this->translate_path . $file . '.json');
                $this->writeToJsonFile($ret['translate'], $this->translate_path . $file . '.json');
            }
        }
    }

    private function notExists($files)
    {
        if (!$files) {
            return;
        }
        Logger::info("目标目录不存在的文件: " . implode(', ', $files));
        foreach ($files as $key => $file) {
            Logger::debug(sprintf("正在移动文件: %s, %s/%s", $file, ($key + 1), count($files)));
            $this->move($this->template_path . $file, $this->output_path . $file);
        }
    }

    private function surplus($files)
    {
        if (!$files) {
            return;
        }
        Logger::warning("目标目录新增的文件: " . implode(', ', $files));
        foreach ($files as $key => $file) {
            Logger::debug(sprintf("正在移动文件: %s, %s/%s", $file, ($key + 1), count($files)));
            $this->move($this->source_path . $file, $this->output_path . $file);
        }
    }

    public function run()
    {
        $diff_files = $this->getDiffFiles();
        // TODO: 多进程处理
        $this->noChange($diff_files['no_change']);
        $this->change($diff_files['change']);
        $this->notExists($diff_files['not_exists']);
        $this->surplus($diff_files['surplus']);
    }
}
