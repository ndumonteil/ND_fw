<?php
namespace ND\MVC;

interface Entity{
/*
    selon les arguments :
    arg1 : $_where
    arg3 : $_order
    arg4 : $_limit
*/
    public function fetch_one();

    public function fetch();

    public function save( MDO $_MDO);

    public function delete_by_pk();

    public function delete_by_MDO();

}
