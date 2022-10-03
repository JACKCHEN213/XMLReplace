<?php

namespace tools;

use Exception;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class XML
{
    private static ?Service $service = null;

    private static function getService(): Service
    {
        if (!self::$service) {
            self::$service = new Service();
        }
        return self::$service;
    }
    /**
     * @throws Exception
     */
    public static function read($file)
    {
        if (is_file($file)) {
            $file = file_get_contents($file);
        }
        $service = new Service();
        $content = $service->parse($file);
        $result = [];
        foreach ($content as $item) {
            $row = $item['value'];
            if (count($row) < 3) {
                var_dump($row);
                throw new \Exception('错误的结构');
            }
            $result[] = [
                'var' => $row[0]['value'],
                'lang' => $row[1]['value'],
                'trans' => $row[2]['value'],
            ];
        }
        return $result;
    }

    public static function write($content)
    {
        return self::getService()->write('Table', $content);
    }
}
