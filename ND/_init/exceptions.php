<?php
namespace ND\exception;

use Exception;

class all_e extends Exception {
    private $_abusive_excs= ['ND\exception\not_found_e', 'ND\exception\duplicate_entry']; // these exc are often abusive & too freq to show the stack each time they occur

    public function __construct( $_msg= 'failed', array $_sprintf_params= [], Exception $_e= null){
        $s= [];
        foreach( $_sprintf_params as $v){
            if( is_array( $v) or is_object( $v)){
                $s[]= json_encode( $v);
            } else {
                $s[]= $v;
            }
        }
        $msg= vsprintf( $_msg, $s);
        parent::__construct( $msg, 0, $_e);
        //error_log( $this);
    }

    public function __toString(){
        $e= $this;
        $msg= sprintf(
            "\"%s\" in %s at %s\n                                %s",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $this->build_formatted_trace( $e->getTrace(), "\n                                ")
        );
        return $msg;
    }

    private function build_formatted_trace( $_ts, $_cr = PHP_EOL, $_popped = null){
        $trace= 'Stack:';
        $ts= array_reverse( $_ts);
        if( ! empty( $_popped)){
            array_pop( $ts);
        }
        foreach( $ts as $it => $t){
            $trace .= sprintf(
                "%s#%s %s::%s(%s) in %s:%s",
                $_cr,
                $it,
                ( @$t[ 'class'] ?: ''),
                ( @$t['function'] ?: ''),
                ( @json_encode( $t['args']) ?: ''),
                ( @$t[ 'file'] ?: ''),
                ( @$t[ 'line'] ?: '')
            );
        }
        return $trace;
    }

}

/**
 * Thrown in case of wrong use of code : function args etc
 **/
class non_runtime_e extends all_e {}

/**
 * Thrown during code execution
 **/
class runtime_e extends all_e {}

/**
 * Thrown during code execution due to system error : allowed memory ...
 **/
class sys_e extends runtime_e {}

/**
 * Thrown during code execution but not system error : entry not found for example
 **/
class non_sys_e extends runtime_e {}

/**
 * Thrown during code execution : entry not found
 **/
class not_found_e extends non_sys_e {}
class duplicate_entry extends non_sys_e {}

/**
 * Thrown if the method called exists but is not implemented.
 **/
class not_implemented_e extends non_runtime_e {}

/**
 * Thrown on unsupported feature
 **/
class not_supported_e extends non_runtime_e {}

/**
 * Service is instancied twice or more time
 */
class duplicate_service_e extends non_runtime_e {}

/**
 * Thrown if arg is invalid.
 **/
class bad_arg_e extends non_runtime_e {}

/**
 * Thrown if some arg is missing (arg passed but holding NULL eg)
 **/
class missing_arg_e extends bad_arg_e {}

/**
 * Thrown if an name argument is reserved by core elements
 */
class name_reserved_e extends bad_arg_e {}

/**
 * Thrown on internal inconsistency
 **/
class inconsistency_e extends non_sys_e {}

/**
 * DRAFT business exception
 */
class not_allowed_action extends non_sys_e {}

// the processing of an item (among a batch) failed (du to many possible reasons)
class unit_processing_failed_e extends sys_e {}
