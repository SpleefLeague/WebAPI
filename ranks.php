<?php

    require_once("./api_base.php");

    class RanksEndpoint extends APIClass {
		
        private $cacheTime = 120;
        private $staffFileName = "_cache_ranks.json";
        
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
                'secret' => false
            ]; 
            $options = [
                'projection' => [
                    'name' => true,
					'displayName' => true,
					'ladder' => true,
					'hasOp' => true,
					'color' => true,
                    '_id' => false
                ],
                'sort' => [
                    'ladder' => -1   
                ]
            ];
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->getMongo()->executeQuery($this->getCollection('MAIN', 'RANKS'), $query);
            $output = array();
            foreach ($cursor as $doc) {
                array_push(
					$output, 
					array(
						'name' => $doc->name,
						'displayName' => $doc->displayName,
						'ladder' => $doc->ladder,
						'op' => $doc->hasOp,
						'color' => $doc->color
					)
				);
            }
            $outputObject = array("age" => 0, "source" => "database", "ranks" => $output, "length" => count($output));
            $outputJson = json_encode($outputObject, JSON_PRETTY_PRINT);
            echo $outputJson;
            $outputObject["source"] = "cache";
            $outputJson = json_encode($outputObject, JSON_PRETTY_PRINT);
            $this->saveCache($outputJson);
        }
        
    }
	
    $page = new RanksEndpoint();
    $page->run();
	
?>