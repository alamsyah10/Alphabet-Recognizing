<?php
	set_time_limit(3600);
	$conn = mysqli_connect('localhost', 'root', '', 'weight_jst_cardio');

	if (!$conn) {
    	die("Connection failed: " . $conn->connect_error);
	}

	$jsonFiles = file_get_contents("cardiovascular_neuralnetwork_assets_dataset_cardio_train.json");

	$data = json_decode($jsonFiles);

	$newData = array();
	$arraySize = 4000;

	foreach ($data as $key) {
		//var_dump($key);
		//exit;
		$arraySize--;
		$tes = get_object_vars($key);
		$newData[] = array_slice($tes, 0, 13);

		if ($arraySize == 0) break;
	}

	$totalInputTes = 1000;
	$start = 2001;
	$testBenar = 0;
	$v = loadWeightXtoZ();
	$w = loadWeightZtoY();

	for ($i=0; $i < 1000; $i++) { 
		$input = $newData[$start+$i];

		$x = loadInputData($input);
		$x = scalingInputData($x);
		
	//	var_dump($x);
	//	var_dump($v);
		$z = loadZ();
	//	var_dump($z);
		$z_in_j = loadZInJ();
		
		//var_dump($w);
		$y = loadActualResponse();
		$target = (int) $newData[$start+$i]["cardio"];

		if (($target == 1 && $y[0] >= 0.5) || ($target == 0 && $y[1] >= 0.5))
		{
			$testBenar++;
		}
	}
	echo "Akurasi: ";
	$akurasi = (double) $testBenar/$totalInputTes;
	$akurasi = $akurasi * 100;

	echo $akurasi."%";
	//var_dump($akurasi);
//	$akurasi = (double) ($akurasi * 100)/100;


//	var_dump($akurasi);



	// var_dump($y);
	// var_dump($target);

	function loadInputData($data)
	{
		$inputData[] = 1;
		foreach ($data as $key => $value) {
			//var_dump($key); exit;
			//echo $key; exit;
			if (strcmp($key, "id") && strcmp($key, "cardio")) {
				// var_dump($key);
				$inputData[] = (double) $value;
			}
		}
		return $inputData;
	//	var_dump($inputData);
	}
	function loadWeightXtoZ()
	{
		global $conn;
		$sql = "SELECT v FROM weight_x_to_z";
		$result = $conn->query($sql);

		while($row = $result->fetch_assoc()){
			$data[] = (double)$row["v"];
		} 

		return $data;
	}

	function loadZ()
	{
		global $x;
		global $v;
		$z[] = (double)1;
		$tmp = 0;
		for ($i=0; $i < 12; $i++) { 
			$tmp = $tmp + (double) ($v[$i*2]*$x[$i]);
		}
	//	var_dump($tmp);
		$tmp = callFx($tmp);
	//	var_dump($tmp);
		$z[] = $tmp;

		$tmp = 0;
		for ($i=0; $i < 12; $i++) { 
			$tmp = $tmp + (double) ($v[($i*2)+1]*$x[$i]);
		}
	//	var_dump($tmp);
		$tmp = callFx($tmp);
	//	var_dump($tmp);
		$z[] = $tmp;

		return $z;
	}

	function loadZInJ()
	{
		global $x;
		global $v;
	//	$z[] = (double)1;
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

	function loadWeightZtoY()
	{
		global $conn;
		$sql = "SELECT w FROM weight_z_to_y";
		$result = $conn->query($sql);

		while($row = $result->fetch_assoc()){
			$data[] = (double)$row["w"];
		} 

		return $data;
	}

	function loadActualResponse()
	{
		global $w;
		global $z;

		$tmp = 0;
		for ($i=0; $i <3 ; $i++) { 
			$tmp = $tmp + (double)($z[$i]*$w[$i*2]);
		}
		$tmp = callFx($tmp);
		$ans[] = $tmp;

		$tmp = 0;
		for ($i=0; $i <3 ; $i++) { 
			$tmp = $tmp + (double)($z[$i]*$w[($i*2)+1]);
		//	echo $tmp." ";
		}
		$tmp = callFx($tmp);
		$ans[] = $tmp;

		return $ans;
	}

	function callFx($x)
	{
		$ans = (double) (1/(1+(double)(exp(-1*$x))));
	//	$ans = intval($ans*1000000)/1000000;
		return $ans;
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

		//var_dump($maksIndeksSatu); exit;
	}

	function findMax($indeks)
	{
		global $newData;

		$max = -1;
		for ($i=0; $i < 4000; $i++) { 
			$tmp = (double) $newData[$i][$indeks];
			if ($tmp > $max) $max = (double) $tmp;
		}

		return $max;

	}
?>