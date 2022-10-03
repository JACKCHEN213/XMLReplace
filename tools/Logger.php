<?php

namespace tools;

/**
 * TODO: 持久化策略
 */
class Logger
{
    public const DEBUG = 'debug';
    public const INFO = 'info';
    public const SUCCESS = 'success';
    public const PRIMARY = 'primary';
    public const WARING = 'warning';
    public const ERROR = 'error';

    private static string $level = self::INFO;
    private static bool $set_level = false;
    private static array $level_permission = [
        self::DEBUG => ['debug', 'info', 'success', 'primary', 'warning', 'error'],
        self::INFO => ['info', 'success', 'primary', 'warning', 'error'],
        self::SUCCESS => ['info', 'success', 'primary', 'warning', 'error'],
        self::PRIMARY => ['info', 'success', 'primary', 'warning', 'error'],
        self::WARING => ['warning', 'error'],
        self::ERROR => ['error'],
    ];

    public static function setLevel($level)
    {
        $level = strtolower($level);
        self::$level = in_array($level, array_keys(self::$level_permission)) ? $level : self::INFO;
        self::$set_level = true;
    }

    public static function getLevel(): string
    {
        if (config('level') && !self::$set_level) {
            // 自动设置级别
            self::setLevel(config('level'));
        }
        return self::$level;
    }

    private static function canOutput($method): bool
    {
        if (!class_exists(self::class, $method)) {
            return false;
        }
        if (in_array($method, self::$level_permission[self::getLevel()])) {
            return true;
        }
        return false;
    }

    private static function color(int $r, int $g, int $b): string
    {
        return "38;2;{$r};{$g};{$b}";
    }

    public static function debug($text)
    {
        if (!self::canOutput(__FUNCTION__)) {
            return;
        }
        $color = self::color(0x90, 0x93, 0x99);
        echo "\e[{$color}m{$text}\e[0m" . PHP_EOL;
    }

    public static function info($text)
    {
        if (!self::canOutput(__FUNCTION__)) {
            return;
        }
        $color = self::color(0xff, 0xff, 0xff);
        echo "\e[{$color}m{$text}\e[0m" . PHP_EOL;
    }

    public static function primary($text)
    {
        if (!self::canOutput(__FUNCTION__)) {
            return;
        }
        $color = self::color(0x40, 0x9e, 0xff);
        echo "\e[{$color}m{$text}\e[0m" . PHP_EOL;
    }

    public static function success($text)
    {
        if (!self::canOutput(__FUNCTION__)) {
            return;
        }
        $color = self::color(0x60, 0xc2, 0x3a);
        echo "\e[{$color}m{$text}\e[0m" . PHP_EOL;
    }

    public static function warning($text)
    {
        if (!self::canOutput(__FUNCTION__)) {
            return;
        }
        $color = self::color(0xe6, 0xa2, 0x3c);
        echo "\e[{$color}m{$text}\e[0m" . PHP_EOL;
    }

    public static function error($text)
    {
        if (!self::canOutput(__FUNCTION__)) {
            return;
        }
        $color = self::color(0xf5, 0x6c, 0x6c);
        echo "\e[{$color}m{$text}\e[0m" . PHP_EOL;
    }

    public static function custom($text, $r, $g, $b)
    {
        $color = self::color($r, $g, $b);
        echo "\e[{$color}m{$text}\e[0m" . PHP_EOL;
    }
}
