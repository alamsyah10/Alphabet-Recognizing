<?php
	set_time_limit(3600);
	$conn = mysqli_connect('localhost', 'root', '', 'datasetjst');

	if (!$conn) {
    	die("Connection failed: " . $conn->connect_error);
	}


	$successTrain = 0;
	$idData = 1;

	while ($successTrain!=6) {
		if ($idData == 7) {
			$idData = 1;
		} 

		$x = loadInputData($idData);
		$weight = loadWeightData(1);
		$target = loadTargetData($idData);

		$sgn = 0;
		for ($i=0; $i <64 ; $i++) { 
			$sgn = $sgn + ($x[$i]*$weight[$i]);
		}
		if ($sgn >= 0) {
			$d = 1;
		} else {
			$d = -1;
		}

		if ($d == $target) {
			$successTrain++;
		} else {
			$successTrain = 0;
			//echo $idData;
			updateWeight(1);
			//var_dump($tes); exit;
		}
	//	echo $successTrain." ";
		$idData++;
	}
	echo $successTrain;

	function loadInputData($dataId)
	{
	 	global $conn;
		$sql = "SELECT x FROM datadetails WHERE idData =  '$dataId'" ;
		$result = $conn->query($sql);

		while ($row = $result->fetch_assoc() ){
			$data[] = (int)$row["x"];
		}

		return $data;
	}

	function loadWeightData($dataId)
	{
		global $conn;
		$sql = "SELECT weight FROM datadetails WHERE idData =  '$dataId'" ;
		$result = $conn->query($sql);

		while ($row = $result->fetch_assoc() ){
			$data[] = (double)$row["weight"];
		}

		return $data;
	}

	function loadTargetData($dataId)
	{
		global $conn;
		$sql = "SELECT target FROM data WHERE id =  '$dataId'" ;
		$result = $conn->query($sql);

		while ($row = $result->fetch_assoc() ){
			$data = (int)$row["target"];
		}

		if ($data == 2) $data = -1;

		return $data;
	}

	function updateWeight($dataId)
	{
		global $conn;
		global $x;
		global $weight;
		global $target;
		global $d;

		$learningRate = 0.3;
		$id = getIdFromDataDetails($dataId);

		for ($i=0; $i < 64 ; $i++) { 
			$tmp = $weight[$i] + (double)($learningRate*($target-$d)*$x[$i]);
		//	 echo $tmp." ";

			$idTmp = $id+$i;
			// echo $idTmp." ";
			$updateSql = "UPDATE datadetails SET weight = '$tmp' WHERE id = '$idTmp'";
			$conn->query($updateSql);
		}
	//	echo "pk";
	}

	function getIdFromDataDetails($dataId)
	{
		global $conn;

		$sql = "SELECT id FROM datadetails WHERE idData =  '$dataId'
				ORDER BY id ASC LIMIT 1" ;
		$result = $conn->query($sql);

		while ($row = $result->fetch_assoc() ){
			$data = (int)$row["id"];
		}

		return $data;
	}

