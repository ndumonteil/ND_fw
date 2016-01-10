<?php namespace o\sec;
require_once SEC_ROOT_ADI . 'apis/libs/filters.php';
require_once SEC_ROOT_ADI . 'interfaces/sg_firewall_i.php';

use o\exceptions as oe;

/**
 * Accesseur Super Globale, son comportement par défaut est de les vider ( cf. $_unset_sg ) et de rendre la valeur non filtrée accessible explicitement via la méthode access_raw().
 *
 * @todo support de la notation --opt=val
 * @author Romain Giacalone <rgiacalone@orchestra.fr>
 * @package o\sec
 */

class sg_firewall implements sg_firewall_i{


/**
 * @const string REQUEST_METHOD index de la cage pour accèder à $_SERVER['REQUEST_METHOD'].
 */

    const REQUEST_METHOD=    'request_method';


/**
 * @const string RAW_DATA_IDX index de la cage pour accèder aux données brutes.
 */

    const RAW_DATA_IDX=      'raw';


/**
 * @const string FILTERED_DATA_IDX index de la cage pour accèder aux données déclarées et filtrées/validées.
 */

    const FILTERED_DATA_IDX= 'safe';


/**
 * @var boolean $_unset_sg flag indiquant le comportement du firewall, par défaut les Super Globales sont vidées.
 */

    protected $_unset_sg=    true;


/**
 * @var array $_cage conteneur pour les valeurs des Super Globales.
 */

    protected $_cage=        false;


/**
 * @var array $_supported_http_sg listes des Super Globales suportées liées à http ( en minuscule ).
 */

    private static $_supported_http_sg= [ 'post', 'get', 'cookie', 'session', 'files' ];


/**
 * Parcours les Super Globales, les vides en fonction du comportement défini par le flag et filtre celles déclarées par $_allowed_idxx.
 * @param array   $_allowed_idxx tableau associatif associant méthode, index autorisé et filtre à appliquer par index, ex: 'get'=> ['param1'=>[liste de filtres]].
 * @param boolean $_unset_sg     flag définissant le comportement du firewall, true si les Super Globales doivent être vidée.
 */

    public function __construct(
        array $_allowed_idxx= [],
        $_unset_sg=           true
    ){

        $this->_unset_sg= (boolean)$_unset_sg;
        $this->_cage= [];

        if( ! isset($_allowed_idxx['cli']) or ! is_array($_allowed_idxx['cli']) ) $_allowed_idxx['cli']= array();
        self::_cage_argv($_allowed_idxx['cli']);
        if( isset($_SERVER['REQUEST_METHOD']) ) $this->_cage[self::REQUEST_METHOD]= $_SERVER['REQUEST_METHOD'];
        foreach( self::$_supported_http_sg as $idx ){
            if( ! isset($_allowed_idxx[$idx]) or ! is_array($_allowed_idxx[$idx]) ) $_allowed_idxx[$idx]= array();
            self::_cage_sg($idx, $_allowed_idxx[$idx]);
        }
    }


/**
 * Renvoi la méthode http utilisée pour adresser la requête http en minuscule.
 * @return string
 */

    public function get_server_method(){

        return isset($this->_cage[self::REQUEST_METHOD])?strtolower($this->_cage[self::REQUEST_METHOD]):false;
    }


/**
 * Renvoi la valeur filtrée correspond à l'index déclaré $_idx de la Super Globale de type $_sg_type.
 * @param get|post|cookie|session|files|cli|null $_sg_type type indiquant les valeurs filtrées à récupérér.
 * @param string|null                            $_idx     index de la Super Globale pour laquelle il faut renvoyer une valeur, null pour les valeurs de tous les index.
 * @return mixed
 */

    public function access(
        $_sg_type= null,
        $_idx=     null
    ){

        return $this->_access(self::FILTERED_DATA_IDX,$_sg_type,$_idx);
    }


/**
 * Renvoi la valeur non filtrée correspond à l'index $_idx de la Super Globale de type $_sg_type.
 * @param get|post|cookie|session|files|cli|null $_sg_type type indiquant les valeurs non filtrées à récupérer.
 * @param string|null                            $_idx     index de la Super Globale pour laquelle il faut renvoyer une valeur, null pour les valeurs de tous les index.
 * @return mixed
 */

    public function access_raw(
        $_sg_type= null,
        $_idx=     null
    ){

        return $this->_access(self::RAW_DATA_IDX,$_sg_type,$_idx);
    }


/**
 * Renvoi la valeur filtrée ou non filtrée correspondant à l'index $_idx de la Super Globale de type $_sg_type.
 * @param get|post|cookie|session|files|cli|null $_sg_type type indiquant les valeurs à récupérer.
 * @param string|null                            $_idx     index de la Super Globale pour laquelle il faut renvoyer une valeur, null pour les valeurs de tous les index.
 * @return mixed
 */

    protected function _access(
        $_access_type,
        $_sg_type= null,
        $_idx=     null
    ){

        if( ! isset($this->_cage[$_access_type]) ) return;
        if( null===$_sg_type ) return $this->_cage[$_access_type];
        if( $_sg_type != 'cli' and ! in_array($_sg_type,self::$_supported_http_sg) ) return;


        if( ! isset($this->_cage[$_access_type]['http'][$_sg_type]) ) return;

        if( $_idx ){
            if( $_sg_type == 'cli' ) return $this->_cage[$_access_type][$_sg_type][$_idx];
            if( ! isset($this->_cage[$_access_type]['http'][$_sg_type][$_idx]) )  return;
            return $this->_cage[$_access_type]['http'][$_sg_type][$_idx];
        }

        if( $_sg_type == 'cli' ) return $this->_cage[$_access_type][$_sg_type];
        return $this->_cage[$_access_type]['http'][$_sg_type];
    }


/**
 * Extrait les valeurs de la Super Globale correspondante à $_sg_type et vide cette Super Globale en fonction du comportement souhaité.
 * @param get|post|cookie|session|files $_sg_type type indiquant la Super Globale à filtrer.
 * @return array
 */

    private function _take_sg(
        $_sg_type= 'get'
    ){

        $sg= null;
        switch( $_sg_type ){

            case 'get':
                $sg= $_GET;
                if( $this->_unset_sg and isset($_GET) ) $_GET= null;
            break;
            case 'post':
                $sg= $_POST;
                if( $this->_unset_sg and isset($_POST) ) $_POST= null;
            break;
            case 'cookie':
                if( $this->_unset_sg and isset($_COOKIE) ) $_COOKIE= null;
            break;
            case 'session':
                if( $this->_unset_sg and isset($_SESSION) ) $_SESSION= null;
            break;
            case 'files':
                if( $this->_unset_sg and isset($_FILES) ) $_FILES= null;
            break;
            default:
            break;
        }

        if( $this->_unset_sg and isset($_SERVER) )  $_SERVER=  null;
        if( $this->_unset_sg and isset($_REQUEST) ) $_REQUEST= null;
        return $sg;
    }


/**
 * Stocke les valeurs filtrées et autorisées et les valeurs non filtrées dans le container $this->_cage.
 * @param get|post|cookie|session|files $_sg_type type indiquant la Super Globale à filtrer.
 * @param array                         $_allowed_idxs appareillage index autorisé=>filtres à appliquer.
 */

    private function _cage_sg(
        $_sg_type= 'get',
        array $_allowed_idxs
    ){

        if( ! ($sg= $this->_take_sg($_sg_type)) ) return;
        foreach( $sg as $idx => $val ){
            $this->_cage[self::RAW_DATA_IDX]['http'][$_sg_type][$idx]= $val;

            if( ! isset($_allowed_idxs[$idx]) or ! is_array($_allowed_idxs[$idx]) ) continue;
            self::_recursively_filter($val, $idx, $_allowed_idxs[$idx], $this->_cage[self::FILTERED_DATA_IDX]['http'][$_sg_type][$idx]);
        }
    }


/**
 * Stocke récursivement les valeurs filtrées et autorisées dans le container $this->_cage.
 * @param mixed  $_val           valeur ou tableau de valeurs.
 * @param string $_key           index courant.
 * @param array  $_allowed_idxs  appareillage index autorisé=>filtres à appliquer.
 * @param mixed  &$_cursor       pointeur vers conteneur ( $this->_cage ) à alimenter.
 * @throw o\exceptions\bad_arg_e exception levée si le paramètre est invalide.
 */

    private static function _recursively_filter(
        $_val,
        $_key,
        array $_allowed_idxs,
        &$_cursor
    ){

        if( is_array($_val) ){
            foreach( $_val as $k => $v ){
                if( ! isset($_allowed_idxs['*']) and ! isset($_allowed_idxs[$k]) ) throw new oe\bad_arg_e('key %s: should not exists as no filter(s) has been defined',[$k]);
                $_cursor[$k]= null;
                self::_recursively_filter($v, $k, isset($_allowed_idxs['*'])?$_allowed_idxs['*']:$_allowed_idxs[$k], $_cursor[$k]);
            }
            return;
        }

        // val n'est pas un array, on lui applique les filtres
        if( empty($_allowed_idxs) ){ // pas de filtre défini
            if( $_val ) throw new oe\bad_arg_e('key %s: should not exists as no filter(s) has been defined',[$_key]); // pas de filtre défini et une valeur fournie, exception
            $_val= true;
        } else foreach( $_allowed_idxs as $rule ){
            $error= false;
            if( isset($rule['parameters']['flags']) and in_array(FILTER_NULL_ON_FAILURE, $rule['parameters']['flags']) ) $error= null;
            if( $error === ($_val= filter_var($_val,$rule['filter'],$rule['parameters'])) ) throw new oe\bad_arg_e('%s: value: %s is invalid',[$_key,$_val]); // si un des filtres renvoit false, on renvoit une exception
        }
        $_cursor= $_val;
        return;
    }


/**
 * Stocke les valeurs filtrées et autorisées et les valeurs non filtrées de l'environnement CLI dans le container $this->_cage.
 * Pour le moment, seule la syntaxe --option valeur est supportée.
 * @param array $_allowed_idxs appareillage index autorisé=>filtres à appliquer.
 * @throw o\exceptions\bad_arg_e exception levée si le paramètre est invalide.
 * @throw o\exceptions\missing_arg_e exception levée si le paramètre est manquant.
 */

    private function _cage_argv(
        array $_allowed_cli_idxs
    ){

        if( ! isset($_SERVER['argv']) ) return;
        if( count($_SERVER['argv'])<2 ) return;
        array_shift($_SERVER['argv']);
        foreach( $_SERVER['argv'] as $idx => $val ){

            if( 0 === strpos($val, '-') ){ // si la chaîne commence par -, c'est une option.
                // on lève les - car ils sont spécifiques au mode cli.
                $val= ltrim($val,'-');

                if( isset($_allowed_cli_idxs[$val]) ) {

                    // si aucun filtre n'est défini alors c'est que l'option cli ne prend pas de valeur.
                    if( empty($_allowed_cli_idxs[$val]) or ! is_array($_allowed_cli_idxs[$val]) ){
                        if( isset($_SERVER['argv'][($idx+1)]) and 0!==strpos($_SERVER['argv'][($idx+1)], '-') ) throw new oe\bad_arg_e('%s value: %s: is invalid',[$val, $_SERVER['argv'][($idx+1)]]);
                        $this->_cage[self::RAW_DATA_IDX]['cli'][$val]= $this->_cage[self::FILTERED_DATA_IDX]['cli'][$val]= true;
                    } else { // si un des filtres renvoit false, on ne stocke pas la valeur.
                        $opt_val= true;
                        foreach( $_allowed_cli_idxs[$val] as $rule ){ // si un filtre est défini mais qu'il n'y a pas de valeur
                            if( ! isset($_SERVER['argv'][($idx+1)]) ) throw new oe\missing_arg_e('%s: is missing',[$val]);
                            $opt_val= $_SERVER['argv'][($idx+1)];
                            if( false === ($opt_val= filter_var($opt_val,$rule['filter'],$rule['parameters'])) ) throw new oe\bad_arg_e('%s value: %s: is invalid',[$val, $_SERVER['argv'][($idx+1)]]);
                        }
                        $this->_cage[self::FILTERED_DATA_IDX]['cli'][$val]= $opt_val;
                    }
                }
                if( isset($_SERVER['argv'][($idx+1)]) ){
                    if ( 0 === strpos($_SERVER['argv'][($idx+1)], '-') ){ // si la chaîne suivant un option commence par -, on considère que c'est une nouvelle option.
                        $this->_cage[self::RAW_DATA_IDX]['cli'][$val]=    null;
                    } else $this->_cage[self::RAW_DATA_IDX]['cli'][$val]= $_SERVER['argv'][($idx+1)];
                } else $this->_cage[self::RAW_DATA_IDX]['cli'][$val]=     null;
            }
        }
    }
}
?>
