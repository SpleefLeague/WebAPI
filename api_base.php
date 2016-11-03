<?php

    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    require_once('./config.php');
    
    class Player {
        public $username;
        public $uuid;
        public $rank;
        public $hiddenStaff;
        
        public function __construct($username, $uuid, $rank = "DEFAULT") {
            $this->username = $username;
            $this->uuid = $uuid;
            $this->rank = $rank;
            $this->hiddenStaff = false;
        }
        
        public function isHiddenStaff() {
            return $this->hiddenStaff;
        }
        
        public static function constructPlayer($data) {
            $rank = property_exists($data, 'rank') ? $data->rank : "DEFAULT";
            $p = new Player($data->username, $data->uuid, $rank);
            if (property_exists($data, "hiddenStaff")) {
                $p->hiddenStaff = $data->hiddenStaff;
            }
            return $p;
        }
    }

    class GamePlayer extends Player {
        public $rating;
        public function __construct($username, $uuid, $rating, $rank = "DEFAULT") {
            parent::__construct($username, $uuid, $rank);
            $this->rating = $rating;
        }
        
        public static function constructPlayer($data) {
            $rank = property_exists($data, 'rank') ? $data->rank : "DEFAULT";
            $p = new GamePlayer($data->username, $data->uuid, $data->rating, $rank);
            return $p;
        }
    }

    abstract class APIClass {
        
        private $database = array(
            'MAIN' => 'SpleefLeague',
            'SPLEEF' => 'SuperSpleef',
            'SUPERJUMP' => 'SuperJump'
        );
        
        private $collection = array(
            'PLAYERS' => 'Players',
            'ARENAS' => 'Arenas',
            'SETTINGS' => 'Settings',
			'RANKS' => 'Ranks'
        );
        
        private $playerProjection = array('projection' => array(
                '_id' => false,
                'username' => true,
                'rank' => true,
                'uuid' => true,
                'hiddenStaff' => true,
                'rating' => true
            )
        );
        
        private $mongoConnection;
        
        public function getCollection($db, $col) {
            $db = $this->database[$db];
            $col = $this->collection[$col];
            return $db.".".$col;
        }
        
        public function getMongo() {
            if ($this->mongoConnection == null) {
                $this->mongoConnection = new MongoDB\Driver\Manager(Config::get()['dbCon']);
            }
            return $this->mongoConnection;
        }
        
        public function dieError($message) {
            die(json_encode(['error' => $message], JSON_PRETTY_PRINT));
        }
        
        public function getPlayerById($uuid) {
            $query = new MongoDB\Driver\Query(['uuid' => $uuid], $this->playerProjection);
            $res = $this->getMongo()->executeQuery('SpleefLeague.Players', $query)->toArray();
            if (count($res) > 0) {
                return Player::constructPlayer($res[0]);
            }
            return null;
        }
        
        public function getPlayerByName($username) {
            $query = new MongoDB\Driver\Query(['lookupUsername' => strtolower($username)], $this->playerProjection);
            $res = $this->getMongo()->executeQuery('SpleefLeague.Players', $query)->toArray();
            if (count($res) > 0) {
                return Player::constructPlayer($res[0]);
            }
            return null;
        }
        
        public function getGamePlayerById($game, $uuid) {
            $query = new MongoDB\Driver\Query(['uuid' => $uuid], $this->playerProjection);
            $res = $this->getMongo()->executeQuery($game.'.Players', $query)->toArray();
            if (count($res) > 0) {
                return GamePlayer::constructPlayer($res[0]);
            }
            return null;
        }
        
        public function getGamePlayerByName($game, $username) {
            $query = new MongoDB\Driver\Query(['lookupUsername' => strtolower($username)], $this->playerProjection);
            $res = $this->getMongo()->executeQuery($game.'.Players', $query)->toArray();
            if (count($res) > 0) {
                return GamePlayer::constructPlayer($res[0]);
            }
            return null;
        }
        
        public function isValidGameName($game) {
            switch (strtolower($game)) {
                case "spleef":
                case "snowspleef":
                case "ss":
                case "superspleef":
                case "jump":
                case "superjump":
                case "sj":
                    return true;
                default: 
                    return false;
            }
        }
        
        public function fixGameName($game) {
            switch (strtolower($game)) {
                case "spleef":
                case "snowspleef":
                case "ss":
                case "superspleef":
                    return "SuperSpleef";
                case "jump":
                case "superjump":
                case "sj":
                    return "SuperJump";
                default: return null;
            }
        }
        
        public function disguiseRank($rank) {
            switch ($rank) {
                case "SENIOR_MODERATOR_BUILDER":
                    return "SENIOR_MODERATOR";
                case "HIDDEN_DEVELOPER":
                    return "DEFAULT";
                case "HIDDEN_COUNCIL":
                    return "DEFAULT";
                case "VIP_DEV":
                    return "VIP";
                case "TOPKEK":
                    return "VIP";
                case "TEMP_MOD":
                    return "MODERATOR";
                default:
                    return $rank;
            }
        }
        
    }
?>