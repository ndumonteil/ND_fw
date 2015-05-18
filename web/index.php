<?php
use \ND;

if( ! defined( 'ND_PATH__ROOT')){ define('ND_PATH__ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);}
if( ! defined( 'ND_PATH__FW')){ define('ND_PATH__FW', ND_PATH__ROOT . 'ND' .DIRECTORY_SEPARATOR);}

require_once ND_PATH__FW . 'kernel.php';

// TODO eradiquer
if( ! defined( 'ND_ENV_NAME')){ define('ND_ENV_NAME', 'dev');}
//

$init_conf= [
    'conf_filetype'=> 'json',
];

$kernel= ND\Kernel::get_instance();
$kernel->init( $init_conf, 'front');
$kernel->run();