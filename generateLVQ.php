<?php

	session_start();
  
  	$input[0] = 1;
  	$iterat = 0;
  	for ($i=1; $i <= 9 ; $i++) {
  	  for ($j=1; $j <=7 ; $j++) {
	      $tempi = "'".$i."'";
	      $tempj = "'".$j."'";

	      $iterat++;

	      if (isset($_SESSION[$tempi][$tempj])) {
	          if($_SESSION[$tempi][$tempj]%2==1){
	            $input[$iterat] = -1;
	          }else{
	            $input[$iterat] = 1;
	          }
	      } else {
	          $input[$iterat] = -1;
	      }
	      

	    }
	 }

  $conn = mysqli_connect('localhost', 'root', '', 'datasetjst');

  if (!$conn) {
      die("Connection failed: " . $conn->connect_error);
  }

  	$x = $input;

  	$c1 = loadWeightClassA();
	$c2 = loadWeightClassE();

	$distanceXtoC1 = loadDistance($x, $c1);
	$distanceXtoC2 = loadDistance($x, $c2);

	if ($distanceXtoC1 <= $distanceXtoC2) {
		$_SESSION['lvq'] = "A";
	} else {
		$_SESSION['lvq'] = "E";
	}

	?>
  		<script>window.location="http://localhost:8080/tugas_jst/";</script>

  	<?php

	function loadDistance($x, $c)
	{
		$tmp = 0;
		for ($i=1; $i < 64; $i++) { 
			$tmp = $tmp + (double)(pow($x[$i]-$c[$i], 2));
		}

		return $tmp;
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