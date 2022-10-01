<?php

namespace src;

class Replace
{
    private string $template_path;
    private string $source_path;
    private static Replace $instance;

    public function __construct()
    {
        $this->template_path = config('template_path');
        $this->source_path = config('source_path');
        self::setInstance($this);
    }

    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }

    public static function getInstance(): Replace
    {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function getDiffFiles(): array
    {
        return Md5File::getDiffFiles($this->template_path, $this->source_path);
    }

    public function run()
    {
        return $this->getDiffFiles();
    }
}
