<?PHP
namespace ND\core\connexion;

use ND\exception as e;
use ND\core\Services_registry as SR;

/**
 * This class is based on the singleton patern
 * It provides an access to the unique definition of available contexts for yo storage services
 *
 * @package \o\yo
 * @author JB <jbourgeais@orchestra.fr>
 */
final class Contexts extends \ND\core\Singleton {
    const R = 'READ';
    const W = 'WRITE';

    /**
     * stores the loaded context from conf file contexts
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
    protected function __construct(){
        $this->_contextx= SR::get_service( SR::CORE_SERVICE_NAME__CONFIGURATION)->get( 'contexts');
        var_dump( $this->_contextx);
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
        if( ! defined('ND_ENV_NAME') ){
            throw new e\not_found_e("Env type:not defined");
        }
        if( ! array_key_exists( ND_ENV_NAME, $this->_contextx)){
            throw new e\non_runtime_e("Env type:".ND_ENV_NAME.":does not exist");
        }
        if( ! array_key_exists( $_code, $this->_contextx[ND_ENV_NAME])){
            throw new e\non_runtime_e("Code:$_code:does not exist for Env type:".ND_ENV_NAME);
        }
        if( ! array_key_exists( 'master', $this->_contextx[ND_ENV_NAME][$_code])){
            throw new e\non_runtime_e("Code:$_code:does not exist for Env type:".ND_ENV_NAME);
        }
        $this->_activated_contextx[$_code] = $this->_contextx[ND_ENV_NAME][$_code]['master'];
        
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
            throw new e\non_runtime_e("Context acces type WRITE and host type SLAVE forbiden");
        }
        $host_type = ($_is_master?'master':'slave');
        switch(  $_access_type) {
            case self::W :
                if( ! array_key_exists( $_code, $this->_activated_contextx)){                   
                    throw new e\non_runtime_e("Le contexte '$_code' n'est pas actif"); }
                return $this->_activated_contextx[$_code];
            case self::R :
                if( ! array_key_exists( ND_ENV_NAME  , $this->_contextx)){                       
                    throw new e\non_runtime_e("Env type:".ND_ENV_NAME.":does not exist"); }
                if( ! array_key_exists( $_code      , $this->_contextx[ND_ENV_NAME])){           
                    throw new e\non_runtime_e("Code:$_code:does not exist for Env type:".ND_ENV_NAME); }
                if( ! array_key_exists( $host_type  , $this->_contextx[ND_ENV_NAME][$_code])){   
                    throw new e\non_runtime_e("Access type:$host_type does not exist for the context code:$_code"); }
                return $this->_contextx[ND_ENV_NAME][$_code][$host_type];
        }
    }

    public function get_conn_x( $_conn_key){
        return @$this->_conn_x[ $_conn_key];
    }
    public function set_conn_x( $_conn_key, $_conn_x){
         $this->_conn_x[ $_conn_key]= $_conn_x;
    }

}
