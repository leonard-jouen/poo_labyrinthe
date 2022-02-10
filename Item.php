<?php
class Item{
    private $name;
    private $qte;

    function __construct($_name){
        $this->name = $_name;
        $this->qte = 0;
    }

    public function getName(){
        return $this->name;
    }

    public function addQte($_qte = 1){
        if($this->qte + $_qte < 0){
            $this->qte = 0;
        }else{
            $this->qte += $_qte;
        }
    }

    public function getQte(){
        return $this->qte;
    }
}