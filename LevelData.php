<?php

class LevelData{

    public static function getMaxLevel(){
        return 5;
    }

    public static function getLevelData($_level){
        $lig = 7;
        $col = 10;
        $nbEnnemies = 0;
        if($_level == 2){ $lig = 7; $col = 9; $nbEnnemies = 0; }
        elseif($_level == 3){ $lig = 7; $col = 8; $nbEnnemies = 1; }
        elseif($_level == 4){ $lig = 6; $col = 6; $nbEnnemies = 1; }
        elseif($_level == 5){ $lig = 6; $col = 6; $nbEnnemies = 2; }
        return [$lig, $col, $nbEnnemies];
    }
}