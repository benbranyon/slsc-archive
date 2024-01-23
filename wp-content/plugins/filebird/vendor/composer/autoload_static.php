<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2f92fe4cf9d9975fcb59b5a432755a58
{
    public static $prefixLengthsPsr4 = array (
        'e' => 
        array (
            'enshrined\\svgSanitize\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'enshrined\\svgSanitize\\' => 
        array (
            0 => __DIR__ . '/..' . '/enshrined/svg-sanitize/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2f92fe4cf9d9975fcb59b5a432755a58::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2f92fe4cf9d9975fcb59b5a432755a58::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2f92fe4cf9d9975fcb59b5a432755a58::$classMap;

        }, null, ClassLoader::class);
    }
}
