<?php

class Player{

    private $pos;
    private $items;
    private $dead;
    private $ennemy;

    function __construct($_ennemy = false){
        $this->items = [];
        $this->dead = false;
        $this->ennemy = $_ennemy;
    }

    public function resetItems(){
        $this->items = [];
    }

    public function getPosition(){
        return $this->pos;
    }

    public function addItem($name, $qte = 1){
        $_item = $this->getItem($name);
        if($_item == null){
            $_item = new Item($name);
            $this->items[] = $_item;
        }
        echo "\nVous ramassez " . $qte . "x " . $name;
        $_item->addQte($qte);
    }

    public function hasItem($name){
        $_item = $this->getItem($name);
        if($_item != null){
            if($_item->getQte() > 0){
                return true;
            }
        }
        return false;
    }

    public function getItem($name){
        for($i = 0;$i<count($this->items); $i++){
            if($this->items[$i]->getName() === $name){
                return $this->items[$i];
            }
        }
        return null;
    }

    public function isDead(){
        return $this->dead;
    }

    public function kill(){
        $this->dead = true;
    }

    public function setPosition($_pos){
        $this->pos = $_pos;

        if(!$this->ennemy){
            if($this->pos->getValue() === 'K'){
                $this->pos->setValue(0);
                $this->addItem('Clef', 1);
            }
            elseif($this->pos->getValue() === 'A'){
                $this->dead = true;
            }
        }
    }
}