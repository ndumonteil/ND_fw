<?php namespace o\sec;

// calcul de PHP_INT_MIN à partir de PHP_INT_MAX pour future référence
if( ! defined('PHP_INT_MIN') ) define ('PHP_INT_MIN', ~PHP_INT_MAX);

/**
 * Librairie contenant des filtres précalculés et humainement identifiables pour les méthode natives PHP filter_*.
 * ex:  static public $canevas= [
 *      'parameters'=>['flags'=>[<flag1, flag2, flag3>],'options'=>[<liste d'options>]],
 *      'filter'=> <filtre>,
 *    ];
 *
 * @author Romain Giacalone <rgiacalone@orchestra.fr>
 * @package o\sec
 */

class filters{


/**
 * @var array $is_positive_int filtre permettant la validation d'un entier positif.
 * @see http://www.php.net/manual/fr/filter.filters.validate.php
 */

    static public $is_positive_int= [
        'parameters'=>['flags'=>[/*'FILTER_FLAG_ALLOW_OCTAL', 'FILTER_FLAG_ALLOW_HEX', */],'options'=>['max_range'=> PHP_INT_MAX,'min_range'=> 0]],
        'filter'=>    FILTER_VALIDATE_INT,
    ];


/**
 * @var array $is_int filtre permettant la validation d'un entier.
 * @see http://www.php.net/manual/fr/filter.filters.validate.php
 */

    static public $is_int= [
        'parameters'=>['flags'=>[/*'FILTER_FLAG_ALLOW_OCTAL', 'FILTER_FLAG_ALLOW_HEX', */],'options'=>['max_range'=> PHP_INT_MAX,'min_range'=> PHP_INT_MIN]],
        'filter'=>    FILTER_VALIDATE_INT,
    ];


/**
 * @var array $is_boolean filtre permettant la validation d'un booléen.
 * @see http://www.php.net/manual/fr/filter.filters.validate.php
 */

    static public $is_boolean= [
        'parameters'=>['flags'=>FILTER_NULL_ON_FAILURE,'options'=>[]],
        'filter'=>    FILTER_VALIDATE_BOOLEAN,
    ];


/**
 * @var array $is_float filtre permettant la validation d'un flottant.
 * @see http://www.php.net/manual/fr/filter.filters.validate.php
 */

    static public $is_float= [
        'parameters'=>['flags'=>[],'options'=>[]],
        'filter'=>    FILTER_VALIDATE_FLOAT,
    ];


    static public $upper= [
        'parameters'=>['flags'=>[],'options'=> '\o\sec\filters::upper'],
        'filter'=>    FILTER_CALLBACK,
    ];


    static public $lower= [
        'parameters'=>['flags'=>[],'options'=> '\o\sec\filters::lower'],
        'filter'=>    FILTER_CALLBACK,
    ];


    static public $is_int_or_empty= [
        'parameters'=>['flags'=>[],'options'=> '\o\sec\filters::validate_int_or_empty'],
        'filter'=>    FILTER_CALLBACK,
    ];

/**
 * @var array $is_float_or_empty filtre permettant la validation d'un flottant ou d'une chaîne vide.
 * @see http://www.php.net/manual/fr/filter.filters.validate.php
 */

    static public $is_float_or_empty= [
        'parameters'=>['flags'=>[],'options'=> '\o\sec\filters::validate_float_or_empty'],
        'filter'=>    FILTER_CALLBACK,
    ];


/**
 * @var array $is_date_m_endian filtre permettant la validation d'une date au format middle endian  (mm-dd-yyyy).
 * @see http://en.wikipedia.org/wiki/Date_format_by_country
 */

    static public $is_date_m_endian= [
        'parameters'=>['flags'=>[],'options'=> ['regexp'=>'@^([1-9]|0[1-9]|1[012])[- /.]([1-9]|0[1-9]|[12][0-9]|3[01])[- /.](19|20)\d\d$@']],
        'filter'=>    FILTER_VALIDATE_REGEXP,
    ];


    static public $is_datetime_m_endian= [
        'parameters'=>['flags'=>[],'options'=> ['regexp'=>'@^([1-9]|0[1-9]|1[012])[- /.]([1-9]|0[1-9]|[12][0-9]|3[01])[- /.](19|20)\d\d ([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9])$@']],
        'filter'=>    FILTER_VALIDATE_REGEXP,
        ];


    static public $is_datetime_b_endian= [
        'parameters'=>['flags'=>[],'options'=> ['regexp'=>'@^((19|20)\d\d)[- /.]([1-9]|0[1-9]|1[012])[- /.]([1-9]|0[1-9]|[12][0-9]|3[01]) ([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9])$@']],
        'filter'=>    FILTER_VALIDATE_REGEXP,
        ];
/**
 * @var array $is_date_l_endian filtre permettant la validation d'une date au format little endian (dd-mm-yyyy).
 * @see http://en.wikipedia.org/wiki/Date_format_by_country
 */
    static public $is_date_l_endian= [
        'parameters'=>['flags'=>[],'options'=> ['regexp'=>'@^([1-9]|0[1-9]|[12][0-9]|3[01])[- /.]([1-9]|0[1-9]|1[012])[- /.](19|20)\d\d$@']],
        'filter'=>    FILTER_VALIDATE_REGEXP,
    ];

/**
 * @var array $is_url filtre permettant la validation d'une url.
 * @see http://www.php.net/manual/fr/filter.filters.validate.php
 */

    static public $is_url= [
        'parameters'=>['flags'=>[FILTER_FLAG_SCHEME_REQUIRED, FILTER_FLAG_HOST_REQUIRED, FILTER_FLAG_PATH_REQUIRED, FILTER_FLAG_QUERY_REQUIRED],'options'=>[]],
        'filter'=>    FILTER_VALIDATE_URL,
    ];


    static public $request_uri= [
        'parameters'=>['flags'=>[],'options'=>[]],
        'filter'=>    FILTER_VALIDATE_URL,
    ];


/**
 * @var array $is_email filtre permettant la validation d'un email, à noter que root@host est invalide.
 * @see http://www.php.net/manual/fr/filter.filters.validate.php
 */

    static public $is_email= [
        'parameters'=>['flags'=>[],'options'=>[]],
        'filter'=>    FILTER_VALIDATE_EMAIL,
    ];

    static public $is_email_or_empty= [
        'parameters'=>['flags'=>[],'options'=>'\o\sec\filters::validate_email_or_empty'],
        'filter'=>    FILTER_CALLBACK,
    ];

    static public $is_phone_number= [
        'parameters'=>['flags'=>[],'options'=>'\o\sec\filters::validate_phone_number'],
        'filter'=>    FILTER_CALLBACK,
    ];

    static public $is_phone_number_or_empty= [
        'parameters'=>['flags'=>[],'options'=>'\o\sec\filters::validate_phone_number_or_empty'],
        'filter'=>    FILTER_CALLBACK,
    ];

/**
 * @var array $is_ip filtre permettant la validation d'une ip.
 * @see http://www.php.net/manual/fr/filter.filters.validate.php
 */

    static public $is_ip= [
        'parameters'=>['flags'=>[FILTER_FLAG_IPV4, FILTER_FLAG_IPV6, FILTER_FLAG_NO_RES_RANGE, FILTER_FLAG_NO_PRIV_RANGE],'options'=>[]],
        'filter'=>    FILTER_VALIDATE_IP,
    ];


/**
 * @var array $text filtre permettant le nettoyage d'un texte.
 * @see http://www.php.net/manual/fr/filter.filters.validate.php
 */

    static public $text= [
        'parameters'=>['flags'=>[FILTER_FLAG_NO_ENCODE_QUOTES],'options'=>[]],
        'filter'=>    FILTER_SANITIZE_STRING,
    ];

/**
 * @var array $html filtre permettant le nettoyage de paramètres en html en vue du stockage en base.
 * @see http://www.php.net/manual/fr/filter.filters.validate.php
 */

    static public $html= [
        'parameters'=>['flags'=>[FILTER_FLAG_NO_ENCODE_QUOTES],'options'=>'\o\sec\filters::html_decode'],
        'filter'=>    FILTER_CALLBACK,
    ];


/**
 * @var array $raw texte brut.
 * @see http://www.php.net/manual/fr/filter.filters.validate.php
 */

    static public $raw= [
        'parameters'=>['flags'=>[],'options'=>[]],
        'filter'=>    FILTER_UNSAFE_RAW,
    ];


/**
 * Passe le paramètre passé en argument en majuscule.
 * @param mixed $_string valeur à passer en majuscules.
 * @return string
 */

    static public function upper(
        $_string
    ){
        if( empty($_string)) return $_string;
        return strtoupper($_string);
    }


    static public function lower(
        $_string
    ){
        if( empty($_string)) return $_string;
        return strtolower($_string);
    }




/**
 * Vérifie si le paramètre passé en argument est un flottant OU une chaîne vide.
 * @param mixed $_float valeur à contrôler.
 * @return boolean
 */

    static public function validate_float_or_empty(
        $_float
    ){
        if( empty($_float)) return $_float;
        if ( false !== filter_var($_float, FILTER_VALIDATE_FLOAT) ) return $_float;
        return false;
    }

    static public function validate_int_or_empty(
        $_int
    ){
        if( empty($_int)) return $_int;
        if ( false !== filter_var($_int, self::$is_int['filter'], self::$is_int['parameters']) ) return $_int;
        return false;
    }

    static public function validate_email_or_empty( $_v ){
        if( empty($_v)) return $_v;
        if ( false !== filter_var($_v, FILTER_VALIDATE_EMAIL) ) return $_v;
        return false;
    }

    static public function validate_phone_number_or_empty( $_v ){
        if( empty($_v)) return $_v;
        return \o\sec\filters::validate_phone_number( $_v);
    }
    static public function validate_phone_number( $_v ){
        if ( false !== filter_var($_v, FILTER_SANITIZE_STRING,['flags'=>[FILTER_FLAG_NO_ENCODE_QUOTES]]) ) return $_v;
        return false;
    }

/**
 * Fait un html_entity_decode de la chaîne passée en paramètre et l'encode en utf8'.
 * @param string $_html_string valeur à contrôler.
 * @return chaîne encodée utf8
 */

    static public function html_decode(
        $_html_string
    ){
        return ( html_entity_decode( $_html_string, ENT_QUOTES, 'UTF-8'));
    }
}
?>
