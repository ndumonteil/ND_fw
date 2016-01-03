<?php namespace o\sec;


/**
 * @author Romain Giacalone <rgiacalone@orchestra.fr>
 * @package o\sec
 */

interface sg_firewall_i{

/**
 * Parcours les Super Globales, les vides en fonction du comportement défini par le flag et filtre celles déclarées par $_allowed_idxx.
 * @param array   $_allowed_idxx tableau associatif associant méthode, index autorisé et filtre à appliquer par index, ex: 'get'=> ['param1'=>[liste de filtres]].
 * @param boolean $_unset_sg     flag définissant le comportement du firewall, true si les Super Globales doivent être vidée.
 */

    public function __construct( array $_allowed_idxx= [], $_unset_sg );


/**
 * Renvoi la méthode http utilisée pour adresser la requête http en minuscule.
 * @return string
 */

    public function get_server_method();


/**
 * Renvoi la valeur filtrée correspond à l'index déclaré $_idx de la Super Globale de type $_sg_type.
 * @param get|post|cookie|session|files $_sg_type type indiquant la Super Globale à filtrer.
 * @param string|null                   $_idx     index de la Super Globale pour laquelle il faut renvoyer une valeur, null pour les valeurs de tous les index.
 * @return mixed
 */

    public function access( $_sg_type= 'get', $_idx= null );


/**
 * Renvoi la valeur non filtrée correspond à l'index $_idx de la Super Globale de type $_sg_type.
 * @param get|post|cookie|session|files $_sg_type type indiquant la Super Globale à filtrer.
 * @param string|null                   $_idx     index de la Super Globale pour laquelle il faut renvoyer une valeur, null pour les valeurs de tous les index.
 * @return mixed
 */

    public function access_raw( $_sg_type= 'get', $_idx=null );
}
?>
