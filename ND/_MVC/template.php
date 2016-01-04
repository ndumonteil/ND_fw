<?php
namespace ND\MVC;

class Template {

    const ND_FW__TEMPLATE_EXT= '.tmpl.php';

    const ND_TEMPLATE__OPT_OVERWRITE= 1;
    const ND_TEMPLATE__OPT_SKIP= 2;

    /**
     * Layout absolute file path
     * @var string
     */
    private $_layout_af;

    /**
     * Referenced templates mapping
     * [ template_name => template_af]
     * @var array
     */
    private $_tmplx;

    /**
     * Variables mapping transmitted to layout
     * @var array
     */
    private $_varx= [];

    /**
     * Rendered view
     * @var string
     */
    private $_view;

    /**
     * Layout, templates and variables transmitted can be referenced at instanciation or with methods
     * @param string $_layout_rf Layout path relative to template directory, without extension
     * @param array $_tmplx Mapping of template_name => template_path (relative to template directory, without extension)
     * @param array $_varx Variables mapping transmitted to layout
     * @param array $_file_ext Extension of layout and templates filenames
     */
    public function __construct( $_layout_rf= null, array $_tmplx= [], array $_varx= [], $_file_ext= self::ND_FW__TEMPLATE_EXT){
        if( $_layout_rf){
            $this->set_layout( $_layout_rf, $_file_ext);
        }
        if( ! empty( $_tmplx)){
            $this->add_templates( $_tmplx,$_file_ext);
        }
        if( ! empty( $_varx)){
            $this->add_varx( $_varx);
        }
    }

    /**
     *
     * @param array $_tmplx Mapping of template_name => template_path (relative to template directory, without extension)
     * @param string $_file_ext
     * @param string $_opt
     */
    public function add_templates( array $_tmplx, $_file_ext= self::ND_FW__TEMPLATE_EXT, $_opt= self::ND_TEMPLATE__OPT_OVERWRITE){
        foreach( $_tmplx as $name=> $rf){
            $this->add_template( $name, $rf, $_file_ext, $_opt);
        }
    }

    /**
     *
     * @param string $_name
     * @param string $_rf
     * @param string $_file_ext
     * @param string $_opt
     */
    public function add_template( $_name, $_rf, $_file_ext= self::ND_FW__TEMPLATE_EXT, $_opt= self::ND_TEMPLATE__OPT_OVERWRITE){
        if( isset( $this->_tmplx[ $_name])){
            if( $_opt == self::ND_TEMPLATE__OPT_SKIP){
                // log template skipped
                return;
            } else {
                // log template overwrited
            }
        }
        $this->_tmplx[ $_name]= $this->_get_tmpl_afi( $_rf, $_file_ext);
    }

    /**
     *
     * @param string $_rf
     * @param string $_ext
     */
    public function set_layout( $_rf, $_ext= self::ND_FW__TEMPLATE_EXT){
        $this->_layout_af= $this->_get_tmpl_afi( $_rf, $_ext);
    }

    public function add_varx( array $_varx, $_force= true){
        if( $_force){
            $this->_varx= array_merge( $this->_varx, $_varx);
        } else {
            $this->_varx += $_varx;
        }
    }

    /**
     * Render layout view
     */
    public function render(){
        extract( $this->_varx);
        return require_once( $this->_layout_af);
    }

    /**
     * Draw a template referenced
     * @param string $_tmpl Template name referenced in $this->_templatex
     * @param mixed $_varx Variables extracted in template
     * @example draw( 'foo') (null) : all layout variables are extracted into template
     * @example draw( 'foo', []) (empty array) : extract nothing
     * @example draw( 'foo', [ 'foo'=> 'bar']) (associative array) : template has $foo variable with 'bar' value
     */
    public function draw( $_tmpl, $_varx= null){
        if( isset( $_varx) && is_array( $_varx)){
            extract( $_varx);
        } else {
            extract( $this->_varx);
        }
        echo require_once $this->_tmplx[ $_tmpl];
    }

    private function _get_tmpl_afi( $_rfe, $_ext){
        if( file_exists( ND_PATH__APP_TEMPLATE . $_rfe . $_ext)){
            return ND_PATH__APP_TEMPLATE . $_rfe . $_ext;
        }
        if( file_exists( ND_PATH__FW_TEMPLATE . $_rfe . ND_FW__TEMPLATE_EXT)){
            return ND_PATH__FW_TEMPLATE . $_rfe . $_ext;
        }
        // throw
    }

}
