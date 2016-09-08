<?php

    require_once("./api_base.php");

    class StatsEndpoint extends APIClass {
                
        public function getRank($game, $points) {
            $cmd = [ 'count' => 'Players', 'query' => [ "rating" => [ '$gt' => $points ] ] ]; 
            $query = new MongoDB\Driver\Command($cmd);
            $cursor = $this->getMongo()->executeCommand($game, $query);
            $result = $cursor->toArray()[0];
            return $result->n + 1;
        }
        
        public function getDefaultArenas($game) {
            $q = '';
            if ($game == 'SPLEEF') {
                $q = 'default_arenas_spleef';
            } else if ($game == 'SUPERJUMP') {
                $q = 'default_arenas_jump';
            } else {
                return [];
            }
            $filter = [ 'key' => $q ];
            $options = [];
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->getMongo()->executeQuery($this->getCollection('MAIN', 'SETTINGS'), $query)->toArray();
            return $cursor[0]->value;
        }
        
        public function getMissingArenas($game, $player) {
            $names = $this->getArenaNames($game);
            $defaultArenas = $this->getDefaultArenas($game);
            $names2 = [];
            foreach ($names as $a) {
                if (!in_array($a, $defaultArenas)) {
                    array_push($names2, $a);
                }
            }
            if (property_exists($player, "visitedArenas")) {
                $names3 = [];
                foreach ($names2 as $a) {
                    if (!in_array($a, $player->visitedArenas)) {
                        array_push($names3, $a);
                    }
                }
                return $names3;
            }
            return $names2;
        }
        
        public function getArenaNames($game) {
            $filter = [ 'paused' => false ];
            $options = [
                'projection' => [
                    'name' => true,
                    '_id' => false
                ]
            ];
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->getMongo()->executeQuery($this->getCollection($game, 'ARENAS'), $query)->toArray();
            $names = [];
            foreach ($cursor as $a) {
                array_push($names, $a->name);
            }
            return $names;
        }
        
        public function run() {
            if (!isset($_GET['path'])) {
                $this->dieError("no argument specified");
            }
			
            $options = [
                'projection' => [
                    'username' => true,
                    'uuid' => true,
                    'rating' => true,
                    'swcRating' => true,
                    'visitedArenas' => true,
                    '_id' => false
                ]
            ];
            
            $player = null;
			
			$requestUrl = explode('/', $_GET['path']);
            if (count($requestUrl) < 2) {
                $this->dieError('incomplete arguments');   
            }
            if (strtolower($requestUrl[0]) == 'uuid') {
                $player = $this->getPlayerById(strtolower($requestUrl[1]));
            } else if (strtolower($requestUrl[0]) == 'name') {
                $player = $this->getPlayerByName($requestUrl[1]);
            }
            if ($player == null) {
                $this->dieError('player not found');
            }
            
            $filter = [
                'uuid' => $player->uuid
            ];
            
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursorss = $this->getMongo()->executeQuery($this->getCollection('SPLEEF', 'PLAYERS'), $query);
            $cursorsj = $this->getMongo()->executeQuery($this->getCollection('SUPERJUMP', 'PLAYERS'), $query);
            $objss = $cursorss->toArray()[0];
            $objsj = $cursorsj->toArray()[0];
            $missingss = $this->getMissingArenas('SPLEEF', $objss);
            $missingsj = $this->getMissingArenas('SUPERJUMP', $objsj);
            $rankss = $this->getRank("SuperSpleef", $objss->rating);
            $ranksj = $this->getRank("SuperJump", $objsj->rating);
            $data = [
                "uuid" => $player->uuid,
                "username" => $player->username,
                "rank" => null,
                "superspleef" => [
                    "rating" => $objss->rating,
                    "rank" => $rankss,
                    "visitedArenas" => [],
                    "missingArenas" => $missingss
                ],
                "superjump" => [
                    "rating" => $objsj->rating,
                    "rank" => $ranksj,
                    "visitedArenas" => [],
                    "missingArenas" => $missingsj
                ]
            ];
            if (property_exists($objss, "swcRating")) {
                $data["swc"]["rating"] = $objss->swcRating;
            }
            if (property_exists($objss, "visitedArenas")) {
                $data["superspleef"]["visitedArenas"] = $objss->visitedArenas;
            }
            if (property_exists($objsj, "visitedArenas")) {
                $data["superjump"]["visitedArenas"] = $objsj->visitedArenas;
            }
            if (!$player->isHiddenStaff()) {
                $data["rank"] = $this->disguiseRank($player->rank);
            } else {
                unset($data["rank"]);
            }
            echo json_encode($data, JSON_PRETTY_PRINT);
        }
        
    }

    $page = new StatsEndpoint();
    $page->run();

?>