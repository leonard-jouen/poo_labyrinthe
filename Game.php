<?php
require_once ('./Item.php');
require_once ('./LevelData.php');
require_once ('./Player.php');
require_once ('./CaseMap.php');

class Game{

    private static $map;
    private static $player;
    private static $ennemies;
    private static $level = 1;

    public static function init($_player){
        if(self::$level == 1){
            echo "\nBienvenue dans le jeu";
        }
        self::$ennemies = [];
        $_levelData = LevelData::getLevelData(self::$level);
        self::$player = $_player;
        self::$player->resetItems();
        self::init_map($_levelData[0], $_levelData[1]);

        for($i = 0;$i<$_levelData[2];$i++){
            self::add_ennemy();
        }
    }

    private static function add_ennemy(){
        $_case = self::get_random_available_case();
        if($_case != null){
            $ennemy = new Player(true);
            $ennemy->setPosition($_case);
            self::$ennemies[] = $ennemy;
        }
    }

    private static function get_distance_between_cases($case1, $case2){
        $x1 = $case1->getX();
        $x2 = $case2->getX();
        $y1 = $case1->getY();
        $y2 = $case2->getY();
        $dist = 0;
        if($x1 > $x2){
            $dist += $x1 - $x2;
        }
        else{
            $dist += $x2 - $x1;
        }
        if($y1 > $y2){
            $dist += $y1 - $y2;
        }
        else{
            $dist += $y2 - $y1;
        }
        return $dist;
    }

    private static function get_case_for_ennemy_move($ennemy){
        $_case = $ennemy->getPosition();
        $available_cases = [];
        $_newCase = self::getValidCase($_case->getX() - 1, $_case->getY(), false);
        if($_newCase != null){
            $available_cases[] = $_newCase;
        }
        $_newCase = self::getValidCase($_case->getX() + 1, $_case->getY(), false);
        if($_newCase != null){
            $available_cases[] = $_newCase;
        }
        $_newCase = self::getValidCase($_case->getX(), $_case->getY() - 1, false);
        if($_newCase != null){
            $available_cases[] = $_newCase;
        }
        $_newCase = self::getValidCase($_case->getX(), $_case->getY() + 1, false);
        if($_newCase != null){
            $available_cases[] = $_newCase;
        }

        if(count($available_cases) > 0){
            $_selectedCase = $available_cases[0];
            $lastDist = self::get_distance_between_cases($_selectedCase, self::$player->getPosition());
            if(count($available_cases) > 1) {
                foreach ($available_cases as $av_case) {
                    $dist = self::get_distance_between_cases($av_case, self::$player->getPosition());
                    if($dist < $lastDist){
                        $_selectedCase = $av_case;
                        $lastDist = $dist;
                    }
                }
            }
            return $_selectedCase;
        }
        return null;
    }

    private static function ennemy_move(){
        foreach(self::$ennemies as $ennemy){
            $_newCase = self::get_case_for_ennemy_move($ennemy);
            if($_newCase != null){
                $ennemy->setPosition($_newCase);
            }
            $_playerPos = self::$player->getPosition();
            $_newPos = $ennemy->getPosition();
            if(!self::$player->isDead() && $_newPos->getX() === $_playerPos->getX() && $_newPos->getY() == $_playerPos->getY()){
                self::$player->kill();
            }
        }
    }

    private static function init_map($lig, $col){
        $lig--;
        $col--;
        self::$map = [];
        $start = self::get_border_case($lig, $col);
        $end = self::get_border_case($lig, $col);
        $key = self::get_random_case($lig, $col);
        while($end[0] === $end[1] && $start[0] === $start[1]){
            $end = self::get_border_case($lig, $col);
        }
        while(($key[0] === $end[0] && $key[1] === $end[1]) || ($key[0] === $start[0] && $key[1] === $start[1])){
            $key = self::get_random_case($lig, $col);
        }
        $chemin_valide = self::determine_chemin($lig, $col, $start, $end);

        for($x = 0; $x <= $lig; $x++){
            self::$map[$x] = [];
            for($y = 0; $y <= $col; $y++){
                if($x == $start[0] && $y == $start[1]){
                    $_case = new CaseMap('S', $x, $y);
                    self::$map[$x][] = $_case;
                    self::setPlayerPosition(self::$player, $_case);
                }
                elseif($x == $end[0] && $y == $end[1]){
                    self::$map[$x][] = new CaseMap('E', $x, $y);
                }
                elseif($x == $key[0] && $y == $key[1] && self::$level < LevelData::getMaxLevel()){
                    self::$map[$x][] = new CaseMap('K', $x, $y);
                }
                elseif(self::is_part_of_chemin($chemin_valide, $x, $y)){
                    self::$map[$x][] = new CaseMap(0, $x, $y);
                }
                else{
                    $rd = rand(0, 4);
                    if($rd == 1) {
                        self::$map[$x][] = new CaseMap(1, $x, $y);
                    }
                    else{
                        self::$map[$x][] = new CaseMap(0, $x, $y);
                    }
                }
            }
        }
    }

    private static function is_part_of_chemin($chemin, $x, $y): bool{
        for($i = 0; $i < count($chemin); $i++){
            if($chemin[$i][0] === $x && $chemin[$i][1] === $y){
                return true;
            }
        }
        return false;
    }

    private static function get_border_case($lig, $col){
        $x = rand(0, $lig);
        $y = rand(0, $col);
        while($x != 0 && $x != $lig && $y != 0 && $y != $col){
            $x = rand(0, $lig);
            $y = rand(0, $col);
        }
        return [$x, $y];
    }

    private static function get_random_available_case(){
        $count = 0;
        for($i=0;$i<count(self::$map);$i++){
            for($j=0;$j<count(self::$map[$i]);$j++){
                if(self::$map[$i][$j]->getValue() !== 'E' && self::$map[$i][$j]->getValue() !== 'S' && self::$map[$i][$j]->getValue() !== 'K'){
                    $count++;
                }
            }
        }

        if($count > 0){
            $rd = rand(0, $count);
            $count = 0;
            for($i=0;$i<count(self::$map);$i++){
                for($j=0;$j<count(self::$map[$i]);$j++){
                    if(self::$map[$i][$j]->getValue() !== 'E' && self::$map[$i][$j]->getValue() !== 'S' && self::$map[$i][$j]->getValue() !== 'K'){
                        if($count === $rd){
                            return self::$map[$i][$j];
                        }
                        else{
                            $count++;
                        }
                    }
                }
            }
        }
        return null;
    }

    private static function get_random_case($lig, $col){
        return [rand(0, $lig), rand(0, $col)];
    }

    private static function determine_chemin($lig, $col, $start, $end){
        $actual_pos = [$start[0], $start[1]];
        $chemin = [];
        $chemin_index = 0;
        $limit = 0;
        while(($actual_pos[0] != $end[0] || $actual_pos[1] != $end[1]) && $limit < 100){
            $possible_dir = [false, false, false, false]; // x gauche, x droite, y bas, y haut
            $limit++;

            if($actual_pos[0] - 1 >= 0){
                $possible_dir[0] = true;
            }
            if($actual_pos[0] + 1 <= $col){
                $possible_dir[1] = true;
            }
            if($actual_pos[1] + 1 <= $lig){
                $possible_dir[2] = true;
            }
            if($actual_pos[1] - 1 >= 0){
                $possible_dir[3] = true;
            }

            if($actual_pos[0] < $end[0]){ // x gauche
                if(self::count_possible_dir($possible_dir) > 2){
                    $possible_dir[0] = false;
                }
            }
            if($actual_pos[0] > $end[0]){ // x droite
                if(self::count_possible_dir($possible_dir) > 2){
                    $possible_dir[1] = false;
                }
            }
            if($actual_pos[1] < $end[1]){ // y bas
                if(self::count_possible_dir($possible_dir) > 2){
                    $possible_dir[2] = false;
                }
            }
            if($actual_pos[1] > $end[1]){ // y haut
                if(self::count_possible_dir($possible_dir) > 2){
                    $possible_dir[3] = false;
                }
            }

            $dir = rand(0, count($possible_dir));
            $found = false;
            $cpt = 0;
            while(!$found && $cpt < 100){
                for($i = 0; $i < count($possible_dir); $i++){
                    if($possible_dir[$i] && $cpt == $dir){
                        $dir = $i;
                        $found = true;
                        break;
                    }
                    else if($possible_dir[$i]){
                        $cpt++;
                    }
                }
            }

            if($found){
                $new_pos = $actual_pos;
                if($dir == 0){ // x gauche
                    if($chemin_index > 0){
                        $new_pos = [$chemin[$chemin_index-1][0] - 1, $chemin[$chemin_index-1][1]];
                    }
                    else{
                        $new_pos = [$start[0] - 1, $start[1]];
                    }
                }
                elseif($dir == 1){ // x droite
                    if($chemin_index > 0){
                        $new_pos = [$chemin[$chemin_index-1][0] + 1, $chemin[$chemin_index-1][1]];
                    }
                    else{
                        $new_pos = [$start[0] + 1, $start[1]];
                    }
                }
                elseif($dir == 2){ // y haut
                    if($chemin_index > 0){
                        $new_pos = [$chemin[$chemin_index-1][0], $chemin[$chemin_index-1][1] - 1];
                    }
                    else{
                        $new_pos = [$start[0], $start[1] - 1];
                    }
                }
                elseif($dir == 3){ // y bas
                    if($chemin_index > 0){
                        $new_pos = [$chemin[$chemin_index-1][0], $chemin[$chemin_index-1][1] + 1];
                    }
                    else{
                        $new_pos = [$start[0], $start[1] + 1];
                    }
                }

                if(!self::is_part_of_chemin($chemin, $new_pos[0], $new_pos[1])) {
                    $chemin[] = [$new_pos[0], $new_pos[1]];
                    $actual_pos = [$new_pos[0], $new_pos[1]];
                    $chemin_index++;
                }
            }
        }
        return $chemin;
    }

    private static function count_possible_dir($possible_dir){
        $cpt = 0;
        for($i = 0; $i<count($possible_dir);$i++){
            if($possible_dir[$i]){
                $cpt++;
            }
        }
        return $cpt;
    }

    private static function isEnnemyAt($x, $y){
        for($i=0;$i<count(self::$ennemies);$i++){
            $_case = self::$ennemies[$i]->getPosition();
            if($_case->getX() === $x && $_case->getY() === $y){
                return true;
            }
        }
        return false;
    }

    private static function showMap(){
        echo "\nNiveau " . self::$level."/".LevelData::getMaxLevel();
        echo "\n";
        for($line = 0; $line < count(self::$map); $line++){
            for($cell = 0; $cell < count(self::$map[$line]); $cell++){
                if(self::$player->getPosition()->getX() === self::$map[$line][$cell]->getX() && self::$player->getPosition()->getY() === self::$map[$line][$cell]->getY()){
                    echo "\033[34mP\033[0m ";
                }
                elseif(self::isEnnemyAt($line, $cell)){
                    echo "\033[31mA\033[0m ";
                }
                elseif(self::$map[$line][$cell]->getValue() === 'E'){
                    if(self::$level < LevelData::getMaxLevel()){
                        echo "\033[32mD\033[0m ";
                    }
                    else{
                        echo "\033[32mE\033[0m ";
                    }
                }
                elseif(self::$map[$line][$cell]->getValue() === 'K'){
                    echo "\033[33mK\033[0m ";
                }
                elseif(self::$map[$line][$cell]->getValue() === 1){
                    echo "\033[90m1\033[0m ";
                }
                else{
                    echo self::$map[$line][$cell]->getValue().' ';
                }
            }
            if($line != count(self::$map) - 1){
                echo "\n";
            }
        }
    }

    private static function getValidCase($x, $y, $ennemy_possible = true){
        if($x >= 0 && $y >= 0 && !empty(self::$map[$x][$y])){
            if(self::$map[$x][$y]->getValue() === 1){
                return null;
            }
            elseif(!$ennemy_possible && self::$map[$x][$y]->getValue() === 'A'){
                return null;
            }
            else{
                return self::$map[$x][$y];
            }
        }
        return null;
    }

    private static function playerChoice(){
        echo "\nChoix  (z,q,s,d) (r : recharger le niveau) : ";
        $choix = mb_strtolower(rtrim(fgets(STDIN)));
        if(mb_strlen($choix) > 0) {
            $_playerPos = self::$player->getPosition();
            if($choix[0] === 'r'){
                self::init(self::$player);
            }
            else{
                if (self::checkDest($choix)) {
                    if ($choix[0] === 'z') {
                        $_case = self::getValidCase($_playerPos->getX() - 1, $_playerPos->getY());
                        if ($_case != null) {
                            self::$player->setPosition($_case);
                        }
                    } elseif ($choix[0] === 's') {
                        $_case = self::getValidCase($_playerPos->getX() + 1, $_playerPos->getY());
                        if ($_case != null) {
                            self::$player->setPosition($_case);
                        }
                    } elseif ($choix[0] === 'q') {
                        $_case = self::getValidCase($_playerPos->getX(), $_playerPos->getY() - 1);
                        if ($_case != null) {
                            self::$player->setPosition($_case);
                        }
                    } elseif ($choix[0] === 'd') {
                        $_case = self::getValidCase($_playerPos->getX(), $_playerPos->getY() + 1);
                        if ($_case != null) {
                            self::$player->setPosition($_case);
                        }
                    }
                }
            }
        }
    }

    private static function checkDest($dir){
        $_case = self::$player->getPosition();
        if($dir[0] === 'z'){
            if(self::getValidCase($_case->getX() - 1, $_case->getY()) == null){
                return false;
            }
        }
        elseif($dir[0] === 's'){
            if(self::getValidCase($_case->getX() + 1, $_case->getY()) == null){
                return false;
            }
        }
        elseif($dir[0] === 'q'){
            if(self::getValidCase($_case->getX(), $_case->getY() - 1) == null){
                return false;
            }
        }
        elseif($dir[0] === 'd'){
            if(self::getValidCase($_case->getX(), $_case->getY() + 1) == null){
                return false;
            }
        }
        else{
            return false;
        }
        return true;
    }

    private static function setPlayerPosition($player, $caseMap){
        $player->setPosition($caseMap);
    }

    private static function checkEndGame(){
        $_case = self::$player->getPosition();
        if($_case != null){
            if($_case->getValue() === 'E'){
                if(self::$level < LevelData::getMaxLevel()){
                    if(self::$player->hasItem('Clef')){
                        return true;
                    }
                    else{
                        echo "\nVous ne possédez pas la clef pour sortir!";
                        return false;
                    }
                }
                else{
                    return true;
                }
            }
        }
        return false;
    }

    public static function launch(){
        while(true){
            self::showMap();
            if(!self::$player->isDead()){
                self::playerChoice();
            }

            if(self::$player->isDead()){
                echo "\nVous êtes mort! (Niveau " . self::$level . ")";
                break;
            }
            if(self::checkEndGame()){
                if(self::$level + 1 > LevelData::getMaxLevel()){
                    self::showMap();
                    echo "\nFin de la partie! Vous avez gagné";
                    break;
                }
                else{
                    self::$level++;
                    self::init(self::$player);
                }
            }
            else{
                self::ennemy_move();
            }
        }
    }
}

Game::init(new Player());
Game::launch();