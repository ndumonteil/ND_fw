<?php
class NDAutoloader {

    private $_dirs= [
        ND_PATH__FW_CORE,
        ND_PATH__FW_MVC,
        ND_PATH__FW_SERVICE,
        ND_PATH__FW_TOOL,
        ND_PATH__APP,
        ND_PATH__APP_SERVICE,
        ND_PATH__APP_MODEL_DRO,
        ND_PATH__APP_MODEL_REPO,
        ND_PATH__APP_TOOL,
    ];

    public function __construct() {
        spl_autoload_register( array( $this, '_search_in_all'));
    }

    private function _search_in_all( $_class){
        foreach( $this->_dirs as $dir){
            $included= $this->_search_in_dir( $_class, $dir);
            if( $included){ return;}
        }
    }

    private function _search_in_dir( $_class, $_dir){
        foreach( scandir( $_dir ) as $file){
            if( is_dir( $_dir . $file ) && substr( $file, 0, 1 ) !== '.'){
                return $this->_search_in_dir( $_class, $_dir . $file . DIRECTORY_SEPARATOR);
            }
            if( substr( $file, 0, 2) !== '._'
                && preg_match( "/.php$/i" , $file)
                && str_replace( '.php', '', $file) == $_class
            ){
                include $_dir . $file;
                return true;
            }
        }
    }
}

$autoloader = new ClassAutoloader();
