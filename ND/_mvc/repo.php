<?php
namespace ND\mvc;

abstract class Repo {
/*
    selon les arguments :
    arg1 : $_by
    arg3 : $_order
    arg4 : $_limit
*/
    public function fetch_one(){}

    public function fetch(){}

    public function save( Dro $_dro){}

    public function delete_by_pk(){}

    public function delete_by_dro(){}
}
