<?php
namespace ND\core\tool;

use ND\exceptions as e;

class util {

    public static function parse_yaml_file( $_afi)
    {
        if( ! $x= \yaml_parse_file( $_afi)){
            throw new e\not_found_e( 'not parsable YAML file: %s', [$_afi]);
        }
        return $x;
    }
}
