<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit61892aa1399b7730bd1c35c2861364ea
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Msdev2\\Shopify\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Msdev2\\Shopify\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit61892aa1399b7730bd1c35c2861364ea::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit61892aa1399b7730bd1c35c2861364ea::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit61892aa1399b7730bd1c35c2861364ea::$classMap;

        }, null, ClassLoader::class);
    }
}
