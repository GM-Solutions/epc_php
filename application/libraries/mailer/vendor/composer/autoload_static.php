<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6bf4a1e46e00b39a9ab0ccf32f7a14bd
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6bf4a1e46e00b39a9ab0ccf32f7a14bd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6bf4a1e46e00b39a9ab0ccf32f7a14bd::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}