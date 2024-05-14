<?php

namespace Northrook\Symfony\Core;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;use Symfony\Component\Cache\Adapter\PhpArrayAdapter;

class Cache {

    private static string $cacheDir;
    private static bool $needsWarmup = false;

    public static function setCacheDir(string $cacheDir) : void {
        Cache::$cacheDir = $cacheDir;
    }

    public static function signalWarmupNeeded() : void{
        Cache::$needsWarmup = true;
    }

    public static function needsWarmup() : bool {
        return Cache::$needsWarmup;
    }

    public static function staticArray() : PhpArrayAdapter {
        return new PhpArrayAdapter(
            Cache::$cacheDir,
            new FilesystemAdapter()
        );
    }

}