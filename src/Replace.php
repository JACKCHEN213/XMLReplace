<?php

namespace src;

use tools\Md5File;
use tools\XML;

class Replace
{
    private string $template_path;
    private string $source_path;

    public function __construct()
    {
        $this->template_path = config('template_path');
        $this->source_path = config('source_path');
    }

    public function getDiffFiles(): array
    {
        return Md5File::getDiffFiles($this->template_path, $this->source_path);
    }

    private function getFileContent($file)
    {
        return XML::read($file);
    }

    public function run()
    {
        $diff_files = $this->getDiffFiles();
        if (isset($diff_files['change'])) {
            $template_content = [];
            $source_content = [];
            foreach ($diff_files['change'] as $file) {
                # TODO: 读取内存超了，考虑读一个处理一下，或者可以建一个任务队列queue
                $template_content[$file] = $this->getFileContent($this->template_path . $file);
                $source_content[$file] = $this->getFileContent($this->source_path . $file);
            }
            var_dump(array_keys($template_content));
        }
    }
}
