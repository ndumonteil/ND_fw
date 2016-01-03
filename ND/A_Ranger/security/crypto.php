<?php namespace o\sec\crypto;
/**
 * Lib providing some encryption and decryption functions  
 * @author JB <jbourgeais@orchestra.fr> 
 */

const SECRET_KEY = '!Fuck1ng+s3c43t3';

/**
 * Encrypts the given value, dedicated to an url params value (returns a base64 string)
 * Using symetric encryption AES (RIJNDAEL)
 * Uses ECB mode to ignore unique initialisation vector 
 * see url_param_decrypt() for decryption
 *
 * NOTE : should not be used to crypt critical information. Use CBC instead of ECB
 *
 * @param string $_value 
 * @param string $_secret_key
 * @author JB <jbourgeais@orchestra.fr> 
 * @access public
 * @return string a base64 encoded string
 */
function url_param_encrypt( $_value, $_secret_key = null ){
    $string_key = ( ! empty( $_k) ? $_secret_key : SECRET_KEY);
    $ch = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_ECB, '');
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($ch), MCRYPT_RAND);
    $bin_key = hash("SHA256", $string_key, true); // ECB needs a 32byte key, bin is better
    mcrypt_generic_init($ch, $bin_key, $iv);
    $encrypted = mcrypt_generic($ch, $_value);
    mcrypt_generic_deinit($ch);
    mcrypt_module_close($ch);
    return rawurlencode(base64_encode($encrypted));
}

function url_param_decrypt( $_value, $_secret_key = null ){
    $string_key = ( ! empty( $_k) ? $_secret_key : SECRET_KEY);
    $ch = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_ECB, '');
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($ch), MCRYPT_RAND);
    $bin_key = hash("SHA256", $string_key, true);
    mcrypt_generic_init($ch, $bin_key, $iv);
    $value = base64_decode(rawurldecode($_value));
    if( empty( $value)) return null;
    $decrypted = mdecrypt_generic($ch, $value);
    if( ! mb_check_encoding($decrypted, 'UTF-8')) return null;
    mcrypt_generic_deinit($ch);
    mcrypt_module_close($ch);
    return rtrim($decrypted); // rtrim encrypted null padding (due to cipher fixed block size)
}
?>
