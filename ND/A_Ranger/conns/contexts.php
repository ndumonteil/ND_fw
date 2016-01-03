<?PHP
namespace o\ctx;
use o\exceptions;

/**
 * This class is based on the singleton patern
 * It provides an access to the unique definition of available contexts for yo storage services
 *
 * @package \o\yo
 * @author JB <jbourgeais@orchestra.fr>
 */
class contexts{
    const R = 'READ';
    const W = 'WRITE';

    protected static $instance = null;

    protected $_conf_afi = null;

    /**
     * stores the loaded context from yaml conf file contexts
     *
     * @var array
     * @access protected
     */
    protected $_contextx = array();

    protected $_conn_x = array();

    /**
     * stores the activated and available contexts for a Write access
     *
     * @var array
     * @access protected
     */
    protected $_activated_contextx = array();

    /**
     * Loads the yaml context conf file
     *
     * @throws exceptions\not_found_e if the conf file is not found
     *
     * @author JB <jbourgeais@orchestra.fr>
     * @access public
     * @return void
     */
    public function __construct(){
        $conf_o= \o\utils\reg::get_instance()->get_conf();
        $this->_conf_afi = $conf_o->get_afi_from_rfi( ['contexts', 'conf_rfi']);
        if( ! $this->_contextx = yaml_parse_file($this->_conf_afi)){
            throw new exceptions\not_found_e("Conf file not found : ".$this->_conf_afi);
        }
    }

    /**
     * Returns the unique instance of the context class
     *
     * @static
     * @author JB <jbourgeais@orchestra.fr>
     * @access public
     * @return contexts the unique instance of the context class
     */
    public static function get_instance(){
        if( ! self::$instance){
            self::$instance = new contexts();
        }
        return self::$instance;
    }

    /**
     * Activates a context for a write access type acording to the given context code
     *
     * @param string $_code context code
     *
     * @throws o\exceptions\non_runtime_e
     * @author JB <jbourgeais@orchestra.fr>
     * @access public
     * @return void
     */
    public function activate_context( $_code){
        if( ! defined('O_ENV_TYPE') ){
            throw new exceptions\not_found_e("Env type:not defined");
        }
        if( ! array_key_exists( O_ENV_TYPE, $this->_contextx)){
            throw new exceptions\non_runtime_e("Env type:".O_ENV_TYPE.":does not exist");
        }
        if( ! array_key_exists( $_code, $this->_contextx[O_ENV_TYPE])){
            throw new exceptions\non_runtime_e("Code:$_code:does not exist for Env type:".O_ENV_TYPE);
        }
        if( ! array_key_exists( 'master', $this->_contextx[O_ENV_TYPE][$_code])){
            throw new exceptions\non_runtime_e("Code:$_code:does not exist for Env type:".O_ENV_TYPE);
        }
        $this->_activated_contextx[$_code] = $this->_contextx[O_ENV_TYPE][$_code]['master'];
        //note('Write context activated', $_code); // KTR: too abundant in logs
    }

    /**
     * returns true if the given context is activated
     * @param string $_code context code
     *
     * @author JB <jbourgeais@orchestra.fr>
     * @access public
     * @return void
     */
    public function is_context_activated( $_code ){
        return array_key_exists($_code, $this->_activated_contextx);
    }

    /**
     * Returns the context according the the given code, access type and host type
     *
     * Note that if a W access type is requested it will be provided through _activated_contextx.
     * So to use a Write context you have to activate it before. see activate_context()
     *
     * @param string $_code       context code
     * @param const $_access_type access type self::R (read), self::W (write)
     * @param bool $_is_master    host type
     *
     * @throws o\exceptions\non_runtime_e
     * @author JB <jbourgeais@orchestra.fr>
     * @access public
     * @return array context
     */
    public function get_context( $_code, $_access_type, $_is_master=false){
        if( ! $_is_master && $_access_type == self::W){
            throw new exceptions\non_runtime_e("Context acces type WRITE and host type SLAVE forbiden");
        }
        $host_type = ($_is_master?'master':'slave');
        switch(  $_access_type) {
            case self::W :
                if( ! array_key_exists( $_code, $this->_activated_contextx)){                   throw new exceptions\non_runtime_e("Le contexte '$_code' n'est pas actif"); }
                return $this->_activated_contextx[$_code];
            case self::R :
                if( ! array_key_exists( O_ENV_TYPE  , $this->_contextx)){                       throw new exceptions\non_runtime_e("Env type:".O_ENV_TYPE.":does not exist"); }
                if( ! array_key_exists( $_code      , $this->_contextx[O_ENV_TYPE])){           throw new exceptions\non_runtime_e("Code:$_code:does not exist for Env type:".O_ENV_TYPE); }
                if( ! array_key_exists( $host_type  , $this->_contextx[O_ENV_TYPE][$_code])){   throw new exceptions\non_runtime_e("Access type:$host_type does not exist for the context code:$_code"); }
                return $this->_contextx[O_ENV_TYPE][$_code][$host_type];
        }
    }

    public function get_conn_x( $_conn_key){
        return @$this->_conn_x[ $_conn_key];
    }
    public function set_conn_x( $_conn_key, $_conn_x){
        return @$this->_conn_x[ $_conn_key] = $_conn_x;
    }

}
