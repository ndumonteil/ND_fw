<?php
use \ND;

if( ! defined( 'ND_PATH__ROOT')){
    define('ND_PATH__ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
    define('ND_PATH__FW', ND_PATH__ROOT . 'ND' .DIRECTORY_SEPARATOR);
}

// TODO eradiquer
if( ! defined( 'ND_ENV_NAME')){ define('ND_ENV_NAME', 'dev');}
//
dirname(__DIR__) . DIRECTORY_SEPARATOR . 'ND' . DIRECTORY_SEPARATOR .
require_once ND_PATH__FW . 'kernel.php';

$init_conf= [
    'conf_filetype'=> 'json',
];


ND\Kernel::get_instance()->init( $init_conf, 'front');
ND\Kernel::get_instance()->run();