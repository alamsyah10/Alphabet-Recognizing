<?php
  session_start();
  // var_dump($_SESSION);
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
  $v = loadWeightXtoZ();
  $z = loadZ();
  $z_in_j = loadZInJ();
  $w = loadWeightZtoY();
  $y = loadActualResponse($z);
  // var_dump($y);
  // exit;
  if($y[0] >= 0 && $y[1] < 0){
    $d = 1;
  }else{
    $d = -1;
  }
  $_SESSION['y'] = $y;
  if ($d == 1) $_SESSION['backprop'] = "A";
  else $_SESSION['backprop'] = "E";;

  $_SESSION['akurasi_backprop'] = $y;
  ?>

  <script>window.location="http://localhost:8080/tugas_jst/generateLVQ.php";</script>
  <?php

  function loadWeightXtoZ()
  {
    global $conn;
    $sql = "SELECT v FROM weightinputbackprop";
    $result = $conn->query($sql);

    while($row = $result->fetch_assoc()){
      $data[] = (double)$row["v"];
    } 

    return $data;
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
  //  var_dump($vNeuron1);
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
    //  echo $tmp." ";
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
  //  var_dump($vNeuron1);
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
    //  echo $tmp." ";
    }
  
    $z[] = $tmp;
  /// $z[] = 1;
    return $z;
  }

  function callFx($z_inj){
    $ans = (double) (2/(1+(exp(-1*$z_inj)))) - 1;
  //  $ans = intval($ans*1000000)/1000000;
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

  function loadActualResponse($z)
  {
    global $conn;
    $sql1 = "SELECT w FROM weight_to_output WHERE yId = 1";
    $sql2 = "SELECT w FROM weight_to_output WHERE yId = 2";

    $result = $conn->query($sql1);

    while ($row = $result->fetch_assoc()) {
      $wNeuron1[] = (double)$row["w"];
    }
  //  var_dump($vNeuron1);
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
    //  echo $tmp." ";
    }
    $tmp = callFx($tmp);
    $ans[] = $tmp;

    return $ans;
  }