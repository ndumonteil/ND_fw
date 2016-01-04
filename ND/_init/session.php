<?php
namespace ND\session;

use ND\exception as e;

class Session{

    public static $cookie_lifetime  = 0;
    public static $cookie_secure    = true;
    public static $cookie_domain    = '.orchestra.fr';
    // ATTENTION : Le répertoire où on dépose les cookies de session doit être un répertoire existant sur le projet
    public static $cookie_path      = '/';
    public static $cookie_httponly  = false;

    /**
     * Ouvre une session et place un cookie basé sur la conf tirée du registre
     * @throw e\runtime_e si les propriétés sensibles du cookie sont modifiées
     * @throw e\sys_e     si on échoue a créer ou regénérer la session
     */
    public static function start(){
        if( self::is_started() ) return;
        $params= self::get_conf();
        session_set_cookie_params($params['lifetime'],$params['path'],$params['domain'],$params['secure'],$params['httponly']);
        if( session_status() == PHP_SESSION_NONE ){
            if( ! session_start() ) throw new e\sys_e('Failed to start session');
        } else throw new e\runtime_e('Session should not be started more than once'); // start ne doit être appelé qu'une seule fois
        if( ! self::fetch('ip_address') ) self::store('ip_address',mvc\ctrl_a::access_global(INPUT_SERVER,'REMOTE_ADDR',sec\filters::$text),sec\filters::$text);
        if( ! self::fetch('user_agent') ) self::store('user_agent',mvc\ctrl_a::access_global(INPUT_SERVER,'HTTP_USER_AGENT',sec\filters::$text),sec\filters::$text);
        try{
            self::check_consistency();
        }catch(e\runtime_e $e){
            if( ! self::regenerate() ) throw new e\sys_e('Failed to start session'); // si il y a un changement de navigateur ou d'adresse IP, on génère une nouvelle session
        }
    }

    public static function is_started(){
        return (bool)session_id();
    }

    public static function open(){
        if( session_status() == PHP_SESSION_NONE ){
            if( ! session_start() ) throw new e\sys_e('Failed to start session');
        } else return true; // déjà ouverte
        try{
            self::check_consistency();
        }catch(e\runtime_e $e){
            if( ! self::regenerate() ) throw new e\sys_e('Failed to start session'); // si il y a un changement de navigateur ou d'adresse IP, on génére une nouvelle session
        }
    }

    public static function regenerate(){
        if( session_status() == PHP_SESSION_NONE ) if( ! session_start() ) throw new e\sys_e('Failed to start session');
        return session_regenerate_id(true);
    }


    /**
     * Récupère la conf pour les cookies à partir du registre
     */
    public static function get_conf(){
        $params= [];
        $params['lifetime']= self::$cookie_lifetime;
        $params['path']=     self::$cookie_path;
        $params['domain']=   self::$cookie_domain;
        $params['secure']=   self::$cookie_secure;
        $params['httponly']= self::$cookie_httponly;
        return $params;
    }


    /**
     * Vérifie la consistence de la session en comparant l'@IP et le user-agent enregistrés lors de la création de la session
     * @throw e\runtime_e levée si la session semble corrompue
     */
    public static function check_consistency(){
        $ip_address= mvc\ctrl_a::access_global(INPUT_SERVER,'REMOTE_ADDR',sec\filters::$text);
        if( isset($_SESSION['ip_address']) and $_SESSION['ip_address']!=$ip_address ){
            warn('possible ip spoofing attempt', [[$_SESSION['ip_address'],$ip_address]]);
        }
    }


    /**
     * Retourne une variable stoquée en session
     * @param string|null $_key clef de $_SESSION à interroger
     * @return mixed toute la session si $_key= null, sinon la valeur stockée dans la clef $_key et si elle n'existe pas, null'
     */
    public static function fetch( $_key = null){
        if( null === $_key )            return $_SESSION;
        if( isset($_SESSION[$_key]) )   return $_SESSION[$_key];
        return null;
    }


    /**
     * Ecrit une donnée de session
     * @param string $_key   clef de $_SESSION dans laquelle écrire
     * @param string $_value valeur à écrire dans la session
     * @param array  $_rule  règle sec\$filters permettant de valider la valeur
     * @throw e\bad_arg_e levée si le paramètre ou la clef ne sont pas valides
     */
    public static function store( $_key, $_value, array $_rule ){
        self::open();
        if( ! $filtered_key= filter_var($_key,FILTER_SANITIZE_STRING,['flags'=>[FILTER_FLAG_NO_ENCODE_QUOTES],'options'=>[]]) ){
            throw new e\bad_arg_e('key %s is invalid',[$_key]);
        }
        if( $filtered_value= filter_var($_value,$_rule['filter'],$_rule['parameters']) ){
            if( null===$filtered_value and isset($_rule['parameters']['flags']) and in_array(FILTER_NULL_ON_FAILURE,$_rule['parameters']['flags']) ){
                throw new e\bad_arg_e('value %s is invalid',[$_value]);
            }
        }
        $_SESSION[$filtered_key]= $filtered_value;
        session_commit();
    }

    /**
     * Retire une variable de la session
     * @param String $_key
     * @throws e\bad_arg_e
     */
    public static function unstore( $_key){
        self::open();
        if( ! $filtered_key= filter_var($_key,FILTER_SANITIZE_STRING,['flags'=>[FILTER_FLAG_NO_ENCODE_QUOTES],'options'=>[]]) ){
            throw new e\bad_arg_e('key %s is invalid',[$_key]);
        }
        if( isset($_SESSION[$filtered_key]) ) unset($_SESSION[$filtered_key]);
        session_commit();
    }


    public static function close(){
        self::open();
        $params= session_get_cookie_params();
        setcookie(session_name(),'',time() - 42000,$params["path"], $params["domain"],$params["secure"], $params["httponly"]);
        session_unset();
        session_destroy();
    }
}
