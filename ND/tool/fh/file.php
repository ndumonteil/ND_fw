<?php
namespace ND\core\tool;

use ND\exceptions as e;

class fh {

    /**
     * Récupère la liste des fichiers disponibles à partir de l'url locale $_url_adi.
     * @throws e\non_sys_e levée si le répertoire n'est pas lisible
     * @param string $_url_adi url du répertoire source.
     * @return mixed liste de fichier
     */
    public static function list_local_directory( $_url_adi){

        $dir= @opendir($_url_adi);
        if( false===$dir ) throw new e\non_sys_e('failed to open directory: %s',[$_url_adi]);
        $list= [];

        while($file= readdir($dir)){
            if( false === $file ) throw new e\non_sys_e('failed to parse directory: %s',[$_url_adi]);
            if( $file != '.' and $file != '..' and !is_dir($_url_adi.$file) ) $list[]= $file;
        }
        closedir($dir);
        return $list;
    }

    /**
     * Récupère le contenu du fichier disponible à partir de l'url locale $_url
     * @throws e\non_sys_e levée si le fichier n'est pas lisible
     * @param string $_url url du fichier source.
     * @return FALSE or string
     */
    public static function get_local_file( $_url, $_throwable= true){
        $content= @file_get_contents( $_url);
        if( false === $content){
            if( $_throwable){
                throw new e\non_sys_e( 'failed to get file: %s', [$_url_afi]);
            } else {
                // TODO logguer
            }
        }
        return $content;
    }

}
