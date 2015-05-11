<?php



var_dump('ok');
try {
$a= \yaml_parse_file('pouet.yml');
} catch ( Exception $e){
    var_dump('heu');
}
var_dump($a);
var_dump('hum');