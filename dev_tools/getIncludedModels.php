<?php
	session_start();

	function getIncludedModels(){
		require_once('../php/config.inc.php');
		$query = "SELECT id FROM getAllIncludedModelsLatestRevision()";
		$res = pg_query($query);
		$models = array();
		while($row = pg_fetch_row($res)){
			array_push($models, $row[0]);
		}
		return $models;
	}

	$models = getIncludedModels();
	echo json_encode(array("data" => $models));
?>