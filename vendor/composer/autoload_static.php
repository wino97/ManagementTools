<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9ac05203b29e0d18b672d052ee29913a
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'CloudTechSolutions\\ManagementTools\\' => 35,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'CloudTechSolutions\\ManagementTools\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'CloudTechSolutions\\ManagementTools\\Api\\Api' => __DIR__ . '/../..' . '/src/Api/Api.php',
        'CloudTechSolutions\\ManagementTools\\Backup\\Backup' => __DIR__ . '/../..' . '/src/Backup/Backup.php',
        'CloudTechSolutions\\ManagementTools\\Config\\Config' => __DIR__ . '/../..' . '/src/Config/Config.php',
        'CloudTechSolutions\\ManagementTools\\Helpers\\Helpers' => __DIR__ . '/../..' . '/src/Helpers/Helpers.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9ac05203b29e0d18b672d052ee29913a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9ac05203b29e0d18b672d052ee29913a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9ac05203b29e0d18b672d052ee29913a::$classMap;

        }, null, ClassLoader::class);
    }
}
