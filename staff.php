<?php

    require_once("./api_base.php");

    class StaffEndpoint extends APIClass {
        
        private $ranks = ["ADMIN","COUNCIL","DEVELOPER","SENIOR_MODERATOR_BUILDER","SENIOR_MODERATOR","MODERATOR"];
        private $cacheTime = 60;
        private $staffFileName = "_cache_staff.json";
        
        public function fromCache() {
            if (file_exists($this->staffFileName)) {
                if (filemtime($this->staffFileName) + ($this->cacheTime) > time()) {
                    $staffFile = fopen($this->staffFileName, "r");
                    $data = fread($staffFile, filesize($this->staffFileName));
                    $obj = json_decode($data);
                    $obj->age = time() - filemtime($this->staffFileName);
                    if ($obj->age < -1) {
                        $obj->age = -1;
                    }
                    $data = json_encode($obj, JSON_PRETTY_PRINT);
                    echo $data;
                    return true;
                }
            }
            return false;
        }
        
        public function saveCache($json) {
            $staffFile = fopen($this->staffFileName, "w");
            fwrite($staffFile, $json);
            fclose($staffFile);
        }
        
        public function run() {
            if ($this->fromCache()) {
                return;
            }
            $filter = [
                '$and' => [
                    ['rank' => ['$in' => $this->ranks]],
                    ['hiddenStaff' => ['$ne' => true]]
                ]
            ]; 
            $options = [
                'projection' => [
                    'rank' => true,
                    'username' => true,
                    'uuid' => true,
                    '_id' => false
                ]
            ];
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->getMongo()->executeQuery($this->getCollection('MAIN', 'PLAYERS'), $query);
            $output = array();
            foreach ($cursor as $doc) {
                array_push($output, array("rank" => $this->disguiseRank($doc->rank), "username" => $doc->username, "uuid" => $doc->uuid));
            }
            $outputObject = array("age" => 0, "source" => "database", "ranks" => $output, "length" => count($output));
            $outputJson = json_encode($outputObject, JSON_PRETTY_PRINT);
            echo $outputJson;
            $outputObject["source"] = "cache";
            $outputJson = json_encode($outputObject, JSON_PRETTY_PRINT);
            $this->saveCache($outputJson);
        }
        
    }

    $page = new StaffEndpoint();
    $page->run();

?>
