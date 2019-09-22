<?php

	set_time_limit(3600);
	$conn = mysqli_connect('localhost', 'root', '', 'datasetjst');

	if (!$conn) {
    	die("Connection failed: " . $conn->connect_error);
	}

	$epoch = 10;
	$dataId = 2;
	$learningRate = 0.3;

	while($epoch>0){


		$c1 = loadWeightClassA();
		$c2 = loadWeightClassE();

		$x = loadInputData($dataId);
		$targetX = loadTarget($dataId);

		$distanceXtoC1 = loadDistance($x, $c1);
		$distanceXtoC2 = loadDistance($x, $c2);

		var_dump($distanceXtoC1); var_dump($distanceXtoC2);

		if ($distanceXtoC1 <= $distanceXtoC2) {
			$actualResponse = $distanceXtoC1;
			$actualClass = 1;
		} else {
			$actualResponse = $distanceXtoC2;
			$actualClass = 4;
		}

		$targetActualClass = loadTarget($actualClass);

		$newWeight = loadNewWeight($targetActualClass, $targetX);

		updateNewWeight();

		// var_dump($c1);
		//var_dump($newWeight);

		$dataId++;

		if($dataId==4){
			$dataId = 5;
		} else if ($dataId == 7) {
			$dataId = 2;
			$epoch--;
		}
		
	}
	var_dump($c1);
	var_dump($c2);

	function updateNewWeight()
	{
		global $conn;
		global $newWeight;
		global $actualClass;

		if ($actualClass == 1) {
			for ($i=0; $i < 63; $i++) { 
				$tmp = $newWeight[$i];
				$id = $i+2;
				$sql = "UPDATE weight_lvq SET w = '$tmp' WHERE id = '$id'";
				$conn->query($sql);
			}
		} else {
			for ($i=0; $i < 63; $i++) { 
				$tmp = $newWeight[$i];
				$id = $i+66;
				$sql = "UPDATE weight_lvq SET w = '$tmp' WHERE id = '$id'";
				$conn->query($sql);
			}
		}
		
	}

	function loadWeightClassA()
	{
		global $conn;
		$sql = "SELECT w FROM weight_lvq WHERE targetId = 1";

		$result = $conn->query($sql);

		while ($row = $result->fetch_assoc() ){
			$data[] = (double)$row["w"];
		}

		return $data;
	}

	function loadWeightClassE()
	{
		global $conn;
		$sql = "SELECT w FROM weight_lvq WHERE targetId = 4";

		$result = $conn->query($sql);

		while ($row = $result->fetch_assoc() ){
			$data[] = (double)$row["w"];
		}

		return $data;
	}

	function loadInputData($dataId)
	{
		global $conn;
		$sql = "SELECT x FROM input_data_train_lvq WHERE targetId = '$dataId'";

		$result = $conn->query($sql);

		while ($row = $result->fetch_assoc() ){
			$data[] = (int)$row["x"];
		}

		return $data;
	}

	function loadDistance($x, $c)
	{
		$tmp = 0;
		for ($i=1; $i < 64; $i++) { 
			$tmp = $tmp + (double)(pow($x[$i]-$c[$i], 2));
		}

		return $tmp;
	}

	function loadTarget($dataId)
	{
		global $conn;
		$sql = "SELECT target FROM data WHERE id =  '$dataId'" ;
		$result = $conn->query($sql);

		while ($row = $result->fetch_assoc() ){
			$data = (int)$row["target"];
		}

		if ($data == 2) {
			$ans = -1;
		} else {
			$ans = 1;
		}

		return $ans;
	}

	function loadNewWeight($targetActualClass, $targetX)
	{
		global $learningRate;
		global $x;
		global $actualClass;
		global $c1;
		global $c2;

		if ($actualClass == 1){
			$c = $c1;
		} else {
			$c = $c2;
		}

		if ($targetActualClass == $targetX) {
			for ($i=1; $i < 64; $i++) { 
				$newW[] = $c[$i] + (double)($learningRate*($x[$i] - $c[$i]));
			}
		} else {
			for ($i=1; $i < 64; $i++) { 
				$newW[] = $c[$i] - (double)($learningRate*($x[$i] - $c[$i]));
			}
		}

		return $newW;
	}