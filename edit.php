<?php
ini_set('display_errors',1);
error_reporting(E_ALL|E_STRICT);


$dbhost = 'localhost';
$dbuser = 'cakephp';
$dbpass = 'nj2kjn8s9d';
$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
$dbname = 'layercakedb';
mysql_select_db($dbname);

$id = mysql_real_escape_string($_POST['lucid']);
$passprovided = md5($_POST['editpass']);

$query = "SELECT * FROM cakes WHERE id=$id;";
$result = mysql_query($query);
if ($row = mysql_fetch_array($result)){
  $title      = $row['title'];
  $numLayers  = $row['numLayers'];
  $width      = $row['width'];
  $height     = $row['height'];
  $author     = $row['author'];
  $published  = $row['published'];
  $hearts     = $row['hearts'];
  $istyle     = $row['istyle'];
  $controlled = $row['controlled'];
  $robots     = $row['robots'];
  $license    = $row['license'];
  $creditname = $row['creditname'];
  $crediturl  = $row['crediturl'];
  $passfromdb = $row['editpass'];
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
if ($passfromdb==$passprovided){
?>
    <h3>Edit layercake</h3>

    <form action="savechanges.php" method="post">
      Title: <input type="text" name="title" size="100" maxlength="200" value="<? echo($title); ?>" /><br>
      Author: <input type="text" name="author" size="45" maxlength="40" value="<? echo($author); ?>" /><br><br>

      Interaction Style:<br>
<input type="radio" name="istyle" value="0" <? if ($istyle==0){echo("checked='checked'");} ?> /> Inflating - hold mouse button to increase, let go to decrease.<br>
<input type="radio" name="istyle" value="1" <? if ($istyle==1){echo("checked='checked'");} ?> /> Waving - click and drag vertically to change value<br>
<input type="radio" name="istyle" value="2" <? if ($istyle==2){echo("checked='checked'");} ?> /> Scrubbing - move mouse faster to increase value<br><br>

      Player Controls:<br>
<input type="radio" name="controlled" value="0" <? if ($controlled==0){echo("checked='checked'");} ?> /> Opacity<br>
<input type="radio" name="controlled" value="1" <? if ($controlled==1){echo("checked='checked'");} ?> /> Brightness<br>
<input type="radio" name="controlled" value="2" <? if ($controlled==2){echo("checked='checked'");} ?> /> Blur<br><br>

      Simulated Players:<br>
<input type="checkbox" name="robots" value="Enabled" <? if ($robots==1){echo("checked='checked'");} ?> /> Enable simulated players (for when nobody is around)<br><br>

      License:<br>
<input type="radio" name="license" value="by" <? if ($license=="by"){echo("checked='checked'");} ?> />
  <a href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution</a><br>
<input type="radio" name="license" value="by-nd" <? if ($license=="by-nd"){echo("checked='checked'");} ?> />
  <a href="http://creativecommons.org/licenses/by-nd/3.0/">Creative Commons Attribution No-Derivatives</a><br>
<input type="radio" name="license" value="by-nc-nd" <? if ($license=="by-nc-nd"){echo("checked='checked'");} ?> />
  <a href="http://creativecommons.org/licenses/by-nc-nd/3.0/">Creative Commons Attribution Non-Commercial No-Derivatives</a><br>
<input type="radio" name="license" value="by-nc" <? if ($license=="by-nc"){echo("checked='checked'");} ?> />
  <a href="http://creativecommons.org/licenses/by-nc/3.0/">Creative Commons Attribution Non-Commercial</a><br>
<input type="radio" name="license" value="by-nc-sa" <? if ($license=="by-nc-sa"){echo("checked='checked'");} ?> />
  <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/">Creative Commons Attribution Non-Commercial Share-Alike</a><br>
<input type="radio" name="license" value="by-sa" <? if ($license=="by-sa"){echo("checked='checked'");} ?> />
  <a href="http://creativecommons.org/licenses/by-sa/3.0/">Creative Commons Attribution Share-Alike</a><br>

<input type="radio" name="license" value="COPYRIGHT" <? if ($license=="COPYRIGHT"){echo("checked='checked'");} ?> /> Copyright<br>
<input type="radio" name="license" value="PUBLIC" <? if ($license=="PUBLIC"){echo("checked='checked'");} ?> /> Public Domain<br>
        <br><br>

Need to give credit to an original author? do that here.<br>
   Name: <input type="text" size=30 name="creditname" value="<? echo($creditname); ?>" /><br>
   Link: <input type="text" size=50 name="creditlink" value="<? echo($crediturl); ?>" /><br><br>

<input type="hidden" name="lucid" value=<? echo($id); ?> />
<input type="hidden" name="dfmu" value=<? echo($passprovided); ?> />

      <input type="submit" value="Save Changes" />

    </form> 

<?
} else {
?>
    Wrong. Try again?<br>
   <form action="edit.php" method="post">
      <input type="text" name="editpass" size=30 />
      <input type="hidden" name="lucid" value=<? echo($id); ?> />
      <input type="submit" value="Edit" />
   </form>


<?
}
?>
     <br><br>
     </p>
     
   </div>
  </body
</html>
