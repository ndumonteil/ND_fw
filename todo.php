<?php
/// Hack if can't define ND_ENV_NAME on server
$env_infos['dev']= [
    'ips'=> [
        '127.0.0.1',
        '::1',
    ]
];
$env_infos['prod']= [
    'ips'=> [
        '213.186.33.40',
    ]
];



$ip= filter_input( INPUT_SERVER, 'SERVER_ADDR', FILTER_SANITIZE_STRING);
foreach( $env_infos as $name=> $infos){
    if(in_array( $ip, $infos[ 'ips'])){
        $env_name= $name;
    }
}
if( ! defined( 'ND_ENV_NAME')){ define('ND_ENV_NAME', $env_name);}
///


 /*
        if( ND_ENV_NAME==='dev'){
            // Afficher les erreurs à l'écran
            ini_set('display_errors', 1);
            // Afficher les erreurs et les avertissements
            error_reporting(E_ALL);
        }
    */    