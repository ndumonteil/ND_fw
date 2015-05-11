<?php
use ND\kernel as ND;

require_once 'init.php';
require_once ND_PATH__FW . 'kernel.php';

$ND_kernel= new ND\Kernel( $init_conf);
$ND_kernel->run( 'back');