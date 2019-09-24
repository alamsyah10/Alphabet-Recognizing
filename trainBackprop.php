<?php

	set_time_limit(7200);

	$conn = mysqli_connect('localhost', 'root', '', 'weight_jst_cardio');

	if (!$conn) {
    	die("Connection failed: " . $conn->connect_error);
	}

	$jsonFiles = file_get_contents("cardiovascular_neuralnetwork_assets_dataset_cardio_train.json");

	$data = json_decode($jsonFiles);
	$newData = array();
	//set 10000 data for training
	$arraySize = 1000;

	foreach ($data as $key) {

		$arraySize--;
		$tes = get_object_vars($key);
		$newData[] = array_slice($tes, 0, 13);

		if ($arraySize == 0) break;
	}
	//var_dump($newData); 

	$indeksData = 0;
	$learningRate = 0.01;
	$epoch = 200;

	//initial v (weight input to hidden layer 1)
	$v = array(1,2,1,2,1,2,1,2,1,2,1,2,1,2,1,2,1,2,1,2,1,2,1,2);

	//initial w (weight hidden layer 1 to response layer)
	$w = array(2, 3, 2, 4, 3, 5);

	

	while ($epoch>0) {
		$x = loadInputData($newData[$indeksData]);

		$x = scalingInputData($x);


		$z = loadZ();
		$z_in_j = loadZInJ();

		$targetTmp = (int) $newData[$indeksData]["cardio"];
		$target = array();
		if ($targetTmp == 1) {
			$target[] = 1;
			$target[] = 0;
		} else {
			$target[] = 0;
			$target[] = 1;
		}

		$y = loadActualResponse();

		$y_in_k = loadActualResponse2();

		$errorY = loadError($y, $y_in_k);
		$error_in_Z = loadError_in_Z($errorY);
		$errorZ = loadErrorZ($error_in_Z, $z_in_j);

		$newWeighttoOutput = loadNewWeightToOutput($errorY);
		$newWeightFromInput = loadNewWeightInput($errorZ);


	// var_dump($v);
	// var_dump($w);

		$w = $newWeighttoOutput;
		$v = $newWeightFromInput;
		echo "ok ";
		$indeksData++;
		if ($indeksData > 999) {
			var_dump($epoch);
			$epoch--;
			$indeksData = 0;
		}
	}


	var_dump($v);
	var_dump($w);

	// to update new weight to database

	 updateWeighttoOutput($w);
	 updateWeightFromInput($v);

	function updateWeighttoOutput($newWeight)
	{
		global $conn;
		global $w;
		for ($i=0; $i < 6; $i++) { 
			$tes = $newWeight[$i];
			$idTmp = 1+$i;
			$sql = "UPDATE weight_z_to_y SET w = '$tes' WHERE id = '$idTmp'";
			$conn->query($sql);
		}
	}

	function updateWeightFromInput($newWeight)
	{
		global $conn;
		global $v;
		$startId = 1;
		for ($i=0; $i < 24; $i++) { 

			$idTmp = $startId + $i;
			$tes = $newWeight[$i];
			$sql = "UPDATE weight_x_to_z SET v = '$tes' WHERE id = '$idTmp'";
			$conn->query($sql);
		}
	}

	function loadInputData($data)
	{
		$inputData[] = 1;
		foreach ($data as $key => $value) {
			if (strcmp($key, "id") && strcmp($key, "cardio")) {
				$inputData[] = (double) $value;
			}
		}
		return $inputData;
	}

	function scalingInputData($x)
	{
		$maksIndeksSatu = findMax('age');
		$maksIndeksTiga = findMax('height');
		$maksIndeksEmpat = findMax('weight');
		$maksIndeksLima = findMax('ap_hi');
		$maksIndeksEnam = findMax('ap_lo');
		$maksIndeksTujuh = findMax('cholesterol');
		$maksIndeksDelapan = findMax('gluc');

		$x[1] = (double) $x[1]/$maksIndeksSatu;
		$x[2] = $x[2]-1;
		$x[3] = (double) $x[3]/$maksIndeksTiga;
		$x[4] = (double) $x[4]/$maksIndeksEmpat;
		$x[5] = (double) $x[5]/$maksIndeksLima;
		$x[6] = (double) $x[6]/$maksIndeksEnam;
		$x[7] = (double) $x[7]/$maksIndeksTujuh;
		$x[8] = (double) $x[8]/$maksIndeksDelapan;

		return $x;
	}

	function findMax($indeks)
	{
		global $newData;

		$max = -1;
		for ($i=0; $i < 1000; $i++) { 
			$tmp = (double) $newData[$i][$indeks];
			if ($tmp > $max) $max = (double) $tmp;
		}

		return $max;
	}

	// function loadWeightXtoZ()
	// {
	// 	global $conn;
	// 	$sql = "SELECT v FROM weight_x_to_z";
	// 	$result = $conn->query($sql);

	// 	while($row = $result->fetch_assoc()){
	// 		$data[] = (double)$row["v"];
	// 	} 

	// 	return $data;
	// }

	function loadZ()
	{
		global $x;
		global $v;
		$z[] = (double)1;
		$tmp = 0;
		for ($i=0; $i < 12; $i++) { 
			$tmp = $tmp + (double) ($v[$i*2]*$x[$i]);
		}
		$tmp = callFx($tmp);
		$z[] = $tmp;

		$tmp = 0;
		for ($i=0; $i < 12; $i++) { 
			$tmp = $tmp + (double) ($v[($i*2)+1]*$x[$i]);
		}
		$tmp = callFx($tmp);
		$z[] = $tmp;

		return $z;
	}

	function callFx($x)
	{
		$ans = (double) (1/(1+(double)(exp(-1*$x))));
	//	$ans = intval($ans*1000000)/1000000;
		return $ans;
	}

	function loadZInJ()
	{
		global $x;
		global $v;

		$tmp = 0;
		for ($i=0; $i < 12; $i++) { 
			$tmp = $tmp + (double) ($v[$i*2]*$x[$i]);
		}

		$z[] = $tmp;

		$tmp = 0;
		for ($i=0; $i < 12; $i++) { 
			$tmp = $tmp + (double) ($v[($i*2)+1]*$x[$i]);
		}

		$z[] = $tmp;

		return $z;
	}

	// function loadWeightZtoY()
	// {
	// 	global $conn;
	// 	$sql = "SELECT w FROM weight_z_to_y";
	// 	$result = $conn->query($sql);

	// 	while($row = $result->fetch_assoc()){
	// 		$data[] = (double)$row["w"];
	// 	} 

	// 	return $data;
	// }

	function loadActualResponse()
	{
		global $w;
		global $z;

		$tmp = 0;
		for ($i=0; $i < 3; $i++) { 
			$tmp = $tmp + (double)($z[$i]*$w[$i*2]);
		}
		$tmp = callFx($tmp);
		$ans[] = $tmp;

		$tmp = 0;
		for ($i=0; $i <3 ; $i++) {
			$tmp = $tmp + (double)($z[$i]*$w[($i*2)+1]);
		}
		$tmp = callFx($tmp);
		$ans[] = $tmp;

		return $ans;
	}

	function loadActualResponse2()
	{
		global $w;
		global $z;

		$tmp = 0;
		for ($i=0; $i <3 ; $i++) { 
			$tmp = $tmp + (double)($z[$i]*$w[$i*2]);
		}
		$ans[] = $tmp;

		$tmp = 0;
		for ($i=0; $i <3 ; $i++) { 
			$tmp = $tmp + (double)($z[$i]*$w[($i*2)+1]);
		}

		$ans[] = $tmp;

		return $ans;
	}

	function loadError($y, $y_in_k)
	{
		global $target;
		for ($i=0; $i < 2; $i++) { 
			$error[] = (double) ($target[$i] - $y[$i]) * fxInverse($y_in_k[$i]);
		}
		return $error;
	}

	function loadErrorZ($error_in_Z, $z_in_j)
	{
		for ($i=0; $i < 2; $i++) { 
			$errorZ[] = (double)$error_in_Z[$i]*fxInverse($z_in_j[$i]);
		}
		return $errorZ;
	}

	function loadError_in_Z($errorY)
	{
		global $w;
		$tmp = 0;
		for($i=0;$i<2;$i++){
			$idTmp = ($i*2)+2;
			$tmp = $tmp + (double)($errorY[$i]*$w[$idTmp]);
		}
		$ans[] = $tmp;

		$tmp = 0;
		for($i=0;$i<2;$i++){
			$idTmp = (($i*2)+1)+2;
			$tmp = $tmp + (double)($errorY[$i]*$w[$idTmp]);
		}
		$ans[] = $tmp;

		return $ans;
	}

	function fxInverse($x)
	{
		return (double)(callFx($x)*(1-callFx($x)));
	}

	function loadNewWeightToOutput($errorY)
	{
		global $learningRate;
		global $z;
		global $w;
	
		for($i=0;$i<2;$i++){
			$newW[] =  $w[$i] + $learningRate*$errorY[$i]*$z[0]; 
		}
	
		for($i=2;$i<4;$i++){
			if($i==2) $j=0; else $j=1;
			$newW[] = $w[$i] + $learningRate*$errorY[$j]*$z[1];
		}
	
	
		for($i=4;$i<6;$i++){
			if($i==4) $j=0; else $j=1;
			$newW[] = $w[$i] + $learningRate*$errorY[$j]*$z[2];
		}

		return $newW;
	}

	function loadNewWeightInput($errorZ)
	{
		global $learningRate;
		global $x;
		global $v;

		for($i=0;$i<12;$i++){
			for($j=0;$j<2;$j++){
				$newWeightInput[] = $v[$i] + $learningRate*$errorZ[$j]*$x[$i];
			}
		}

		return $newWeightInput;
	}
?>