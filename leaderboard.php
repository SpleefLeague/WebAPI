<?php

    require_once("./api_base.php");

    class LeaderboardEndPoint extends APIClass {
        
        public function getRank($game, $points) {
            $cmd = [ 'count' => 'Players', 'query' => [ "rating" => [ '$gt' => $points ] ] ]; 
            $query = new MongoDB\Driver\Command($cmd);
            $cursor = $this->getMongo()->executeCommand($game, $query);
            $result = $cursor->toArray()[0];
            return $result->n + 1;
        }
        
        public function runPlayerCenter($game, $p) {
            $slp = $this->getGamePlayerById($game, $p->uuid);
            if ($slp == null) {
                $this->dieError('Player not found');
            }
            $rank = $this->getRank($game, $slp->rating);
            $limit = 100;
            $low = $rank - 50;
            if ($low < 0) { $limit-= $low; $low = 0; }
            $filter = [];
            $options = [
                'projection' => [
                    '_id' => false,
                    'username' => true,
                    'uuid' => true,
                    'rating' => true
                ],
                'skip' => $low,
                'limit' => $limit,
                'sort' => [ "rating" => -1 ]
            ];
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->getMongo()->executeQuery($game.".Players", $query);
            $output = [];
            $curRank = $low;
            foreach ($cursor as $doc) {
                $curRank++;
                $output["players"][] = [
                    "username" => $doc->username,
                    "uuid" => $doc->uuid,
                    "rating" => $doc->rating,
                    "rank" => $curRank
                ];
            }
            $outputJson = json_encode($output, JSON_PRETTY_PRINT);
            echo $outputJson;
        }
        
        public function run() {
            if (!isset($_GET['path'])) {
                $this->dieError("no argument specified");
            }
            $requestUrl = explode('/', $_GET['path']);
            $game = null;
            if ($this->isValidGameName($requestUrl[0])) {
                $game = $this->fixGameName($requestUrl[0]);
            }
            if (count($requestUrl) > 2) {
                if (strtolower($requestUrl[1]) == 'player') {
                    $player = $requestUrl[2];
                    $p = $this->getPlayerByName($player);
                    if ($p == null) {
                        $p = $this->getPlayerById($player);
                    }
                    if ($p == null) {
                        $this->dieError('player not found');   
                    }
                    $this->runPlayerCenter($game, $p);
                    return;
                }
            }
            $page = 1;
            if (count($requestUrl) > 1) {
                if (!is_numeric($requestUrl[1]) && !empty($requestUrl[1])) {
                    $this->dieError('Invalid argument for page');
                }
                if (!empty($requestUrl[1])) {
                    $page = intval($requestUrl[1]);
                }
            }
            $count = 100;
            $filter = [];
            $skip = $page;
            $skip--;
            $skip = $skip * $count;
            $options = [
                'projection' => [
                    '_id' => false,
                    'username' => true,
                    'uuid' => true,
                    'rating' => true
                ],
                'skip' => $skip,
                'limit' => $count,
                'sort' => [ "rating" => -1 ]
            ];
            $query = new MongoDB\Driver\Query($filter, $options);
            $output = [ "page" => $page, 'age' => 0, "players" => [] ];
            $cursor = $this->getMongo()->executeQuery($game.".Players", $query);
            $curRank = $count * ($page-1);
            foreach ($cursor as $doc) {
                $curRank++;
                $output["players"][] = [
                    "username" => $doc->username,
                    "uuid" => $doc->uuid,
                    "rating" => $doc->rating,
                    "rank" => $curRank
                ];
            }
            $outputJson = json_encode($output, JSON_PRETTY_PRINT);
            echo $outputJson;
        }
        
    }

    $page = new LeaderboardEndPoint();
    $page->run();

?>
