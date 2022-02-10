<?php

class CaseMap{
    private $value;
    private $x;
    private $y;

    function __construct($_value, $_x, $_y){
        $this->value = $_value;
        $this->x = $_x;
        $this->y = $_y;
    }

    public function getX(){
        return $this->x;
    }

    public function getY(){
        return $this->y;
    }

    public function getValue(){
        return $this->value;
    }

    public function setValue($_value){
        $this->value = $_value;
    }
}