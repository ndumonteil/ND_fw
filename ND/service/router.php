<?php
namespace ND\core\service;

use ND\exception as e;

class Router {

    private $_confx;
    private $_request;
    private $_requested_url_name;

    public function __construct( $_conf){
        $app_name= \ND\Kernel::get_instance()->get_app_name();
        $this->_confx= array_merge( $_conf[ '_core'], $_conf[ $app_name]);
    }

    public function analyze_url( $_request){
        if( isset( $this->_request)){ return;}
        $this->_request= $_request;
        if( ! $this->_requested_url_name= $this->_find_url_name( $_request->get_requested_url())){
            $this->redirect( 'error404');
        }
    }

    /**
     * @param string $_url_name Optionnel. Defaut : url_name correspondant à la requête actuelle.
     * @return string Nom du contrôleur
     */
    public function get_controller_name( $_url_name= null){
        $url_name= @$_url_name ?: $this->_requested_url_name;
        return $this->_confx[ $url_name][ 'controller'];
    }

    /**
     * @param string $_url_name (optionnel) Defaut : url_name correspondant à la requête actuelle.
     * @return string Nom de la méthode exécutée dans le contrôleur
     */
    public function get_action_name( $_url_name= null){
        $url_name= @$_url_name ?: $this->_requested_url_name;
        return $this->_confx[ $url_name][ 'action'];
    }

    /**
     * Crée une URL
     * @param string $_name Nom de l'URL référencée dans la conf router
     * @param array $_argx Arguments de l'URL (optionnel)
     * @return string URL
     * @throws e\missing_arg_e
     * @throws e\bad_arg_e
     */
    public function make_url( $_name, $_argx= []){
        $url= $this->_sanitize_extremities( $this->_confx[ $_name][ 'url']);
        $datax= @$this->_confx[ $_name][ 'url_argx'];
        preg_match_all( '#\{\w+\}#', $url, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = substr( $match[0][0], 1, -1);
            $regexp= @$datax[ $key] ?: '[A-Za-z0-9_-]*';
            if( ! isset( $_argx[ $key])){
                throw new e\missing_arg_e( 'Can\'t make url "%s" without argument "%s"', [ $_name, $key]);
            }
            if( ! preg_match( $regexp, $_argx[ $key])){
                throw new e\bad_arg_e( 'Can\'t make url %s (argument : "%s", regexp : "%s")', [ $_name, $key, $regexp]);
            }
            str_replace( '{' . $key . '}', $_argx[ $key], $url);
        }
        return $url;
    }

    /**
     * Effectue une redirection
     * @param string $_url_name Nom de l'URL référencée dans la conf router
     * @param array $_argx Arguments de l'URL (optionnel)
     */
    public function redirect( $_url_name, $_argx= []){
        $url= $this->make_url( $_url_name, $_argx);
        header( 'Location: ' . $url);
    }

    private function _find_url_name( $_requested_url){
        $_requested_url= $this->_sanitize_extremities( $_requested_url);
        foreach( $this->_confx as $name=> $confx){
            $regexp_url= $this->_sanitize_extremities( $confx[ 'url']);
            $nb= preg_match_all( '#\{\w+\}#', $regexp_url, $matches);
            if( ! $nb){
                if( $regexp_url == $_requested_url){ return $name;}
                continue;
            }
            $keys= [];
            foreach ($matches[0] as $match) {
                $key= substr( $match, 1, -1);
                $keys[]= $key;
                $regexp= @$confx[ 'url_argx'][ $key] ?: '[A-Za-z0-9_-]*';
                $regexp_url= preg_replace( '#(\{' . $key . '\})#', '(' . $regexp . ')', $regexp_url);
            }
            if( preg_match( '#^' . $regexp_url . '$#', $_requested_url, $matches)){
                $argx= array_combine( $keys, array_slice( $matches, 1));
                $this->_request->set_url_argx( $argx);
                return $name;
            }
        }
    }

    private function _sanitize_extremities( $_url){
        if( substr( $_url, 0, 1) !== '/'){ $_url= '/' . $_url;}
        return rtrim( $_url, '/');
    }

}
