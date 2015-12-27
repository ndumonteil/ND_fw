<?php
namespace ND\mvc;

abstract class Repo {
    /*
     * lien vers autres tables
     */
    private $links;
/*
    selon les arguments :
    arg1 : $_where
    arg3 : $_order
    arg4 : $_limit
*/
    public function fetch_one(){}

    public function fetch(){}

    public function save( Dro $_dro){}

    public function delete_by_pk(){}

    public function delete_by_dro(){}
    
}
