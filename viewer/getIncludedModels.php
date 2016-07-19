<?php

	function getIncludedModels(){
		session_start();
		require_once('../php/config.inc.php');

		$dorms = array("barh","barton","rahp_apt","blitman","bray","bryckwyck","cary","colonie","commons","crockett","davison","e_complex","hall","nason","north","nugent","quad",
                  "sharp","rahp_single","stacwyck","warren");
		$dorm_models = array();
		foreach($dorms as $dorm){
			$query = "SELECT id FROM getLatestModelsByDorm($1)";
			$res = pg_query_params($query, array($dorm));
			$models = array();
			while($row = pg_fetch_row($res)){
				array_push($models, $row[0]);
			}
			$dorm_models[$dorm] = $models;
		}

		
		return $dorm_models;
	}

	$models = getIncludedModels();
	echo json_encode(array("data" => $models));
?>