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

  // if ($_SESSION['target'] == 'A') {
  //   $idData = 1;
  // } else $idData = 4;

  $x = $input;
  $weight = loadWeightData(1);
 // $target = loadTargetData($idData);

  $sgn = 0;
  for ($i=0; $i <64 ; $i++) { 
    $sgn = $sgn + ($x[$i]*$weight[$i]);
  }
  if ($sgn >= 0) {
    $d = 1;
  } else {
    $d = -1;
  }

  if ($d == 1) $_SESSION['perceptron'] = "A";
  else $_SESSION['perceptron'] = "E";;
  ?>

  <script>window.location="http://localhost:8080/tugas_jst/generateBackprop.php";</script>
  <?php
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

  // var_dump($input);
  // var_dump($_POST);

 ?>
