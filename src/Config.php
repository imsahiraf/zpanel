<?php

declare(strict_types=1);

namespace ZPanel;

/**
 * Paths and switches of one panel installation. Everything lives under one data directory so a test can run the whole panel
 * in a temporary folder: ZPANEL_HOME (data), ZPANEL_WWW (web roots), ZPANEL_ETC (nginx / cron files) and
 * ZPANEL_DRY=1 (record system commands instead of running them).
 */
final class Config
{
    public static function home(): string
    {
        return rtrim(getenv('ZPANEL_HOME') ?: '/opt/zpanel/data', '/');
    }

    public static function www(): string
    {
        return rtrim(getenv('ZPANEL_WWW') ?: '/www/wwwroot', '/');
    }

    public static function etc(): string
    {
        return rtrim(getenv('ZPANEL_ETC') ?: '/etc', '/');
    }

    public static function dry(): bool
    {
        return getenv('ZPANEL_DRY') === '1';
    }

    public static function version(): string
    {
        return trim((string) @file_get_contents(dirname(__DIR__) . '/VERSION')) ?: '0.1.0';
    }

    public static function ensure(string $dir): string
    {
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new \RuntimeException("Cannot create $dir");
        }

        return $dir;
    }
}
