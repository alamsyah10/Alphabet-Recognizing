<?php

	set_time_limit(3600);
	$conn = mysqli_connect('localhost', 'root', '', 'datasetjst');

	if (!$conn) {
    	die("Connection failed: " . $conn->connect_error);
	}
	$learningRate = 0.9;
	$epoch = 50;
	$dataId = 1;
	while($epoch>0){
		

		$x = loadInputData($dataId);
		$v = loadWeightXtoZ($x);
		$z = loadZ();
		$z_in_j = loadZInJ();
		$w = loadWeightZtoY();
		$target = loadTarget($dataId);
		$y = loadActualResponse($z);

		var_dump($dataId);
		var_dump($z_in_j);
		var_dump($target);
		var_dump($y);
		$y_in_k = loadActualResponse2($z);
		$errorY = loadError($y, $y_in_k);
		$error_in_Z = loadError_in_Z($errorY);
		$errorZ = loadErrorZ($error_in_Z, $z_in_j);

		var_dump($errorZ);
		$newWeighttoOutput = loadNewWeightToOutput($errorY);
		$newWeightFromInput = loadNewWeightInput($errorZ);


		updateWeighttoOutput($newWeighttoOutput);
		updateWeightFromInput($newWeightFromInput);

		$dataId++;

		if($dataId == 7){
			$epoch--;
			$dataId = 1;
		}

		// var_dump($x);
		// var_dump($v);
		// var_dump($z);

		// var_dump($w);
		// var_dump($target);
		// var_dump($y);
		// var_dump($y_in_k);
		// var_dump($errorY);
		// var_dump($error_in_Z);
		// var_dump($z_in_j);
		// var_dump($newWeighttoOutput);
		// var_dump($errorZ);
		// var_dump($newWeightFromInput);

		
		

	}
	function updateWeighttoOutput($newWeight)
	{
		global $conn;
		global $w;
		for ($i=0; $i < 6; $i++) { 
			$tes = $w[$i]+$newWeight[$i];
			$idTmp = 1+$i;
			$sql = "UPDATE weight_to_output SET w = '$tes' WHERE id = '$idTmp'";
			$conn->query($sql);
		}
	}

	function updateWeightFromInput($newWeight)
	{
		global $conn;
		global $v;
		$startId = 129;
		for ($i=0; $i < 128; $i++) { 

			$idTmp = $startId + $i;
			$tes = $v[$i]+$newWeight[$i];
			$sql = "UPDATE weightinputbackprop SET v = '$tes' WHERE id = '$idTmp'";
			$conn->query($sql);
		}
	}

	function loadInputData($dataId)
	{
	 	global $conn;
		$sql = "SELECT x FROM inputlistbackprop WHERE dataId =  '$dataId'" ;
		$result = $conn->query($sql);

		while ($row = $result->fetch_assoc() ){
			$data[] = (int)$row["x"];
		}

		return $data;
	}

	function loadWeightXtoZ($x)
	{
		global $conn;
		$sql = "SELECT v FROM weightinputbackprop";
		$result = $conn->query($sql);

		while($row = $result->fetch_assoc()){
			$data[] = (double)$row["v"];
		} 

		return $data;
		// for($i=0;$i<64;$i++){
		// 	$
		// }
	}

	function loadZ()
	{
		global $conn;
		global $x;
		global $v;

		$sql1 = "SELECT v FROM weightinputbackprop WHERE zid = 1";
		$sql2 = "SELECT v FROM weightinputbackprop WHERE zid = 2";

		$result = $conn->query($sql1);

		while ($row = $result->fetch_assoc()) {
			$vNeuron1[] = (double)$row["v"];
		}
	//	var_dump($vNeuron1);
		$tmp = 0;
		for ($i=0; $i <64 ; $i++) { 
			$tmp = $tmp + (double)($x[$i]*$vNeuron1[$i]);
		}
		$tmp = callFx($tmp);
		$z[] = $tmp;

		$result = $conn->query($sql2);

		while ($row = $result->fetch_assoc()) {
			$vNeuron2[] = (double)$row["v"];
		}
		$tmp = 0;
		for ($i=0; $i <64 ; $i++) { 
			$tmp = $tmp + (double)($x[$i]*$vNeuron2[$i]);
		//	echo $tmp." ";
		}
		$tmp = callFx($tmp);
		$z[] = $tmp;
		$z[] = 1;
		return $z;
	}

	function loadZInJ()
	{
		global $conn;
		global $x;
		global $v;

		$sql1 = "SELECT v FROM weightinputbackprop WHERE zid = 1";
		$sql2 = "SELECT v FROM weightinputbackprop WHERE zid = 2";

		$result = $conn->query($sql1);

		while ($row = $result->fetch_assoc()) {
			$vNeuron1[] = (double)$row["v"];
		}
	//	var_dump($vNeuron1);
		$tmp = 0;
		for ($i=0; $i <64 ; $i++) { 
			$tmp = $tmp + (double)($x[$i]*$vNeuron1[$i]);
		}
		//$tmp = callFx($tmp);
		$z[] = $tmp;

		$result = $conn->query($sql2);

		while ($row = $result->fetch_assoc()) {
			$vNeuron2[] = (double)$row["v"];
		}
		$tmp = 0;
		for ($i=0; $i <64 ; $i++) { 
			$tmp = $tmp + (double)($x[$i]*$vNeuron2[$i]);
		//	echo $tmp." ";
		}
	
		$z[] = $tmp;
	///	$z[] = 1;
		return $z;
	}

	function callFx($z_inj){
		$ans = (double) (2/(1+(exp(-1*$z_inj)))) - 1;
	//	$ans = intval($ans*1000000)/1000000;
		return $ans;
	}

	function loadWeightZtoY()
	{
		global $conn;
		$sql = "SELECT w FROM weight_to_output";
		$result = $conn->query($sql);

		while($row = $result->fetch_assoc()){
			$data[] = (double)$row["w"];
		} 

		return $data;
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
			$ans[] = -1;
			$ans[] = 1;
		} else {
			$ans[] = 1;
			$ans[] = -1;
		}

		return $ans;
	}

	function loadActualResponse($z)
	{
		global $conn;
		$sql1 = "SELECT w FROM weight_to_output WHERE yId = 1";
		$sql2 = "SELECT w FROM weight_to_output WHERE yId = 2";

		$result = $conn->query($sql1);

		while ($row = $result->fetch_assoc()) {
			$wNeuron1[] = (double)$row["w"];
		}
	//	var_dump($vNeuron1);
		$tmp = 0;
		for ($i=0; $i <3 ; $i++) { 
			$tmp = $tmp + (double)($z[$i]*$wNeuron1[$i]);
		}
		$tmp = callFx($tmp);
		$ans[] = $tmp;

		$result = $conn->query($sql2);

		while ($row = $result->fetch_assoc()) {
			$wNeuron2[] = (double)$row["w"];
		}
		$tmp = 0;
		for ($i=0; $i <3 ; $i++) { 
			$tmp = $tmp + (double)($z[$i]*$wNeuron2[$i]);
		//	echo $tmp." ";
		}
		$tmp = callFx($tmp);
		$ans[] = $tmp;

		return $ans;
	}

	function loadActualResponse2($z)
	{
		global $conn;
		$sql1 = "SELECT w FROM weight_to_output WHERE yId = 1";
		$sql2 = "SELECT w FROM weight_to_output WHERE yId = 2";

		$result = $conn->query($sql1);

		while ($row = $result->fetch_assoc()) {
			$wNeuron1[] = (double)$row["w"];
		}
	//	var_dump($vNeuron1);
		$tmp = 0;
		for ($i=0; $i <3 ; $i++) { 
			$tmp = $tmp + (double)($z[$i]*$wNeuron1[$i]);
		}
		
		$ans[] = $tmp;

		$result = $conn->query($sql2);

		while ($row = $result->fetch_assoc()) {
			$wNeuron2[] = (double)$row["w"];
		}
		$tmp = 0;
		for ($i=0; $i <3 ; $i++) { 
			$tmp = $tmp + (double)($z[$i]*$wNeuron2[$i]);
		//	echo $tmp." ";
		}
		
		$ans[] = $tmp;

		return $ans;
	}

	function loadError($y, $y_in_k)
	{
		global $target;
		for ($i=0; $i < 2; $i++) { 
			$error[] = ($target[$i] - $y[$i]) * fxInverse($y_in_k[$i]);
		}
		return $error;
	}

	function loadErrorZ($error_in_Z, $z_in_j)
	{
		for ($i=0; $i < 2; $i++) { 
			$errorZ[] = $error_in_Z[$i]*fxInverse($z_in_j[$i]);
		}
		return $errorZ;
	}

	function fxInverse($x)
	{
		return (double)(0.5)*(1+callFx($x)*(1-callFx($x)));
	}

	function loadNewWeightToOutput($errorY)
	{
		global $learningRate;
		global $z;
	
		for($i=0;$i<2;$i++){
			$newW[] =  $learningRate*$errorY[$i]*$z[0]; 
		}
	
		for($i=2;$i<4;$i++){
			if($i==2) $j=0; else $j=1;
			$newW[] = $learningRate*$errorY[$j]*$z[1];
		}
	
	
		for($i=4;$i<6;$i++){
			if($i==4) $j=0; else $j=1;
			$newW[] = $learningRate*$errorY[$j]*$z[2];
		}
		echo "newW: ";
		var_dump($newW);
		return $newW;
	}

	function loadError_in_Z($errorY)
	{
		global $conn;

		$sql1 = "SELECT w FROM weight_to_output WHERE zId = 1";
		$sql2 = "SELECT w FROM weight_to_output WHERE zId = 2";

		$result = $conn->query($sql1);

		while ($row = $result->fetch_assoc()) {
			$wNeuron1[] = (double)$row["w"];
		}
		$tmp = 0;
		for($i=0;$i<2;$i++){
			$tmp = $tmp + ($errorY[$i]*$wNeuron1[$i]);
		}
		$ans[] = $tmp;

		$result = $conn->query($sql2);

		while ($row = $result->fetch_assoc()) {
			$wNeuron2[] = (double)$row["w"];
		}
		$tmp = 0;
		for($i=0;$i<2;$i++){
			$tmp = $tmp + ($errorY[$i]*$wNeuron2[$i]);
		}
		$ans[] = $tmp;

		return $ans;
	}

	function loadNewWeightInput($errorZ)
	{
		global $learningRate;
		global $x;

		for($i=0;$i<64;$i++){
			for($j=0;$j<2;$j++){
				$newWeightInput[] = $learningRate*$errorZ[$j]*$x[$i];
			}
		}
		echo "newV: "; var_dump($newWeightInput);
		return $newWeightInput;
	}