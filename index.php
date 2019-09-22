
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Create</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Design Bootstrap -->
    <link href="css/mdb.min.css" rel="stylesheet">
    <link href="style2.css" rel="stylesheet">
  </head>
  <body>
    <p align='center'>
      <?php
        session_start();
        if (isset($_SESSION['perceptron'])) {
          echo "Pola (Perceptron) : ".$_SESSION['perceptron']."<br>";
        }

        if (isset($_SESSION['backprop'])) {
          echo "Pola (Backpropagation) : ".$_SESSION['backprop']."<br>";
        }

        if (isset($_SESSION['lvq'])) {
          echo "Pola (LVQ) : ".$_SESSION['lvq'];
        }

       ?>
    <table>
      <?php for ($i=1; $i <=9 ; $i++) {
        // code...
       ?>
    <div class="h1toh5">
        <tr>
          <?php
              for ($j=1; $j <=7 ; $j++) {
                $tempi = "'".$i."'";
                $tempj = "'".$j."'";
              if(!isset($_SESSION[$tempi][$tempj])){
                $in[$i][$j] = 1;


          ?>

        <td><form    method="post"><input  type=submit value=<?php echo "\"".$i.$j."\"" ?> name=<?php echo "\"".$i.$j."\"" ?> class="btn btn-outline-primary seatbutton"></form></td>
        <?php
      }else{
        if($_SESSION[$tempi][$tempj]%2==1){


        ?>
        <td><form    method="post"><input  type=submit value=<?php echo "\"".$i.$j."\"" ?> name=<?php echo "\"".$i.$j."\"" ?> class="btn btn-outline-primary seatbutton"></form></td>
        <?php
      }
        else{
          ?>
          <td><form    method="post"><input style="background-color:black" type=submit value=<?php echo "\"".$i.$j."\"" ?> name=<?php echo "\"".$i.$j."\"" ?> class="btn btn-outline-primary seatbutton"></form></td>
        <?php
        }
      }
              }
            }
         ?>

        </tr>
    </div>

    </table>
    </p>
    <form method="post">
      <input class = "btn btn-outline-primary "type="submit" name="reset" value="reset">
    
      <input class = "btn btn-outline-primary" type="submit" name="allType" value = "Submit">
    </form>

    <?php
  //  var_dump($_SESSION);
      for ($i=1; $i <=9 ; $i++) {
        for ($j=1; $j <=7 ; $j++) {
          if(isset($_POST[$i.$j])){
            $tempi = "'".$i."'";
            $tempj = "'".$j."'";
            if(isset($in[$i][$j])){
              $_SESSION[$tempi][$tempj] = $in[$i][$j];

            }
              $_SESSION[$tempi][$tempj]++;
          //  echo "ok";

          //  echo $_SESSION[$tempi][$tempj];
            ?>
            <script>window.location="http://localhost:8080/tugas_jst/";</script>
            <?php
          }
        }
      }
      if(isset($_POST['reset'])){
        session_destroy();
        ?>
        <script>window.location="http://localhost:8080/tugas_jst/";</script>
        <?php
      }


      if(isset($_POST['allType'])){
        ?>
        <script>window.location="http://localhost:8080/tugas_jst/generate.php";</script>
        <?php
      }

     ?>

  </body>
</html>
