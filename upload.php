<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL|E_STRICT);


$dbhost = 'localhost';
$dbuser = 'cakephp';
$dbpass = 'nj2kjn8s9d';
$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
$dbname = 'layercakedb';
mysql_select_db($dbname);

$err = NULL;

$title = mysql_real_escape_string($_POST['title']);
$author = mysql_real_escape_string($_POST['author']);
$istyle = mysql_real_escape_string($_POST['istyle']);
$controlled = mysql_real_escape_string($_POST['controlled']);
$license = mysql_real_escape_string($_POST['license']);
$editpass = md5($_POST['editpass']);
if ($_POST['robots'] == "Enabled"){
  $robots = 1;
 } else {
  $robots = 0;
 }

$numLayers = 0;

foreach ($_FILES as $iname => $ifile) {
  if ($ifile['error']==UPLOAD_ERR_OK){
  
    $name = $ifile['tmp_name'];
    $imdesc = explode(' ',exec("identify $name"));
    
    if ($imdesc[1]=='PNG'){
      $size = explode('x',$imdesc[2]);
      $width = (int)$size[0];
      $height = (int)$size[1];
      if ($width <= 1200 || $height <= 1200){
	if ($iname=='bg'){
	  $sizebg = $size;
	} else {
	  if ($size==$sizebg){
	    $numLayers++;
	  } else {
	    $err = "Layer $iname did not have the same dimensions as the background.<br>Background: $sizebg<br>Layer $iname: $size";
	    break;
	  }
	}
      } else {
	$err = "Images too large. Neither width or height may exceed 1200";
	break;
      }
    } else {
      $err = "Layer $iname was not a PNG.";
      break;
    }
  }  
}

if ($numLayers < 1){
  $err = "You must submit at least 1 layer other than the background.";
}


if ($err == NULL){
  $insert_query = "INSERT INTO cakes (title,numLayers,width,height,author,published,hearts,istyle,controlled,robots,license,editpass) VALUES ('$title',$numLayers,$width,$height,'$author',NOW(),0,$istyle,$controlled,$robots,'$license','$editpass') ;";
  mysql_query($insert_query);
  $result = mysql_query("SELECT LAST_INSERT_ID();");
  $cake_id = mysql_fetch_array($result);
  $cake_id =  $cake_id[0];
  $uploaddir = '/var/www/LayerCake/images/' . $cake_id;
  mkdir($uploaddir,0775);
  chmod($uploaddir,0775);
  foreach ($_FILES as $iname => $ifile) {
    if ($ifile['error']==UPLOAD_ERR_OK){
      $final_path = $uploaddir . '/' . $iname . '.png';
      move_uploaded_file($ifile['tmp_name'], $final_path);
      chmod($final_path,0664);
    }
  }

  // Image Work. Takes around 2 seconds on an unloaded system for a typical layercake
  exec("cp $uploaddir/bg.png $uploaddir/thumb_bg.png");
  exec("mogrify -resize 100x100 $uploaddir/thumb_bg.png");
  chmod("$uploaddir/thumb_bg.png",0664);
  for ( $ln=1; $ln<=$numLayers; $ln+=1) {
    exec("cp $uploaddir/$ln.png $uploaddir/thumb_$ln.png");
    exec("mogrify -resize 100x100 $uploaddir/thumb_$ln.png");
    chmod("$uploaddir/thumb_$ln.png",0664);
  }
  exec("composite $uploaddir/thumb_1.png $uploaddir/thumb_bg.png $uploaddir/thumb.png");
  for ( $ln=2; $ln<=$numLayers; $ln+=1) {
    exec("composite $uploaddir/thumb_$ln.png $uploaddir/thumb.png $uploaddir/thumb.png");
  }
  chmod("$uploaddir/thumb.png",0664);
}


mysql_close($conn);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Layer Cake</title>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
    <style type="text/css">
body{
  font-family: sans-serif;
}

.maincontain{
  margin-left: 40px;
  margin-right: 40px;
  margin-top: 60px;
  border-radius: 10px;
  -moz-border-radius: 10px;
  background-color: #CCCCCC;
  padding: 10px;
  padding-left: 22px;
}

.logo{
  float:left;
  border: none;
}

    </style>

  </head>
  <body>

   <div>
     <a href="http://uncc.ath.cx/LayerCake/"><img class="logo" src="header.png" alt="Layer Cake (beta)"/></a>
   </div>

   <div>&nbsp</div>
    
   <div class="maincontain">
     
     <p>
<? 
if ($err==null){
?>
     Success!<br><br>
      
     <a href="view.php?id=<? echo $cake_id; ?>"><h3>View your LayerCake</h3></a>

<?
} else {
  echo $err;
}
?>
     <br><br>
     </p>
     
   </div>
  </body
</html>
