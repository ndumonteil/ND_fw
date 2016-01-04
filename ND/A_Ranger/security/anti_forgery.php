<?php
namespace o\sec;

class anti_forgery{

    public static $passwd_validation_regex = '@(?=^.{8,128}$)((?=.*\d)|(?=.*[\W_]+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$@';

    public static function generate_blowfish_salt(){
        $blow_fish_accepted_chars= './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $cost= str_pad(rand(4,31),'0',STR_PAD_LEFT); // 04-31
        $salt_prefix= '$2x$'; // $2x$ ou $2y$
        $digits= '';
        for($i=0;$i<22;$i++) $digits.= $blow_fish_accepted_chars[rand(0,63)];
        return sprintf('%s%02d$%s',$salt_prefix,$cost,$digits);
    }

    public static function validate_p( $_string){
        return preg_match(self::$passwd_validation_regex,$_string);
    }

    public static function encrypt( $_string, $_salt){
        $hashed= hash('sha256',$_string);
        // crypt et blowfish prennent trop de temps :(
        if( ! ($salted= hash('sha256',$hashed.$_salt)) ) return false;
        return $salted;
    }
}
