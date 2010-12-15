<?php
error_reporting(E_ALL);
ini_set('display_errors', true);


$dbhost = 'localhost';
$dbuser = 'cakephp';
$dbpass = 'nj2kjn8s9d';
$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
$dbname = 'layercakedb';
mysql_select_db($dbname);

$id = mysql_real_escape_string($_GET['id']);
$result = mysql_query("SELECT * FROM cakes WHERE id=$id;");

if ($row = mysql_fetch_array($result)){
  $title      = $row['title'];
  $numLayers  = $row['numLayers'];
  $width      = $row['width'];
  $height     = $row['height'];
  $author     = $row['author'];
  $published  = $row['published'];
  $hearts     = $row['hearts'];
  $istyle     = $row['istyle'];
  $robots     = $row['robots'];
  $license    = $row['license'];
  $creditname = $row['creditname'];
  $crediturl  = $row['crediturl'];
  $controlled = $row['controlled'];
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
 width: <? echo ($width+130); ?>;
}

.logo{
  float:left;
  border: none;
}

.create{
  float: right;
  border-radius: 8px;
  -moz-border-radius: 8px;
  background-color: #555555;
  padding-left: 26px;
  padding-right: 26px;
  padding-top: 4px;
  padding-bottom: 4px;
  margin-right: 40px;
}

.createlink{
  color: #FFFFFF;
}

.heartcontainer{
  margin: 20px;
}

.heartnum{
}

.instruct{
  font-size: 16px;
}
    </style>

    <script type="text/javascript">//<![CDATA[
  // Google Analytics for WordPress by Yoast v4.06 | http://yoast.com/wordpress/google-analytics/
  var _gaq = _gaq || [];
_gaq.push(['_setAccount','UA-11122994-2']);
_gaq.push(['_trackPageview']);
(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
 })();
// End of Google Analytics for WordPress by Yoast v4.0
//]]></script>


    <script type="text/javascript">
        lcTitle = "<? echo($title); ?>";
        numLayers = <? echo($numLayers); ?>;
        gameid = <? echo($id); ?>;
        interactionStyle = <? echo($istyle); ?>;
        controlled = <? echo($controlled); ?>;
    </script>
    
    <script type="text/javascript" src="layerandsim.js"></script>
    <script type="text/javascript" src="draw.js"></script>

  </head>
  <body>

   <div>
     <a href="http://uncc.ath.cx/LayerCake/"><img class="logo" src="header.png" alt="Layer Cake (beta)"/></a>
   </div>

   <a href="create.html" class="createlink">
     <div class="create">
       <h3>Create Your Own</h3>
     </div>
   </a>

  <div>&nbsp;</div>
    
   <div class="maincontain">
     <canvas id="myDrawing" width="<? echo($width); ?>" height="<? echo($height); ?>">
       <p>Your browser doesn't support canvas.</p>
     </canvas>

   </div>
   
   <p class="instruct">
     <h2>Instructions</h2>
	<? if ($istyle==0){ ?>
     Click and hold mouse button to increase the opacity of your layer.<br>
	<? } else if ($istyle==1) { ?>
     Click and drag vertically to increase the opacity of your layer.<br>
        <? } else if ($istyle==2) { ?>
     Move your mouse faster in the image to increase the opacity of your layer.<br>
	<? } ?>
     Click on another layer in the right pane to control that one. (Or press number keys to switch layers.)<br>
     Other layers may be controlled by other players online right now.<br>
   </p>
       <?
     if ($license=="COPYRIGHT"){
       echo("&copy; Copyright ".substr($published,0,4)." $author<br>");
     } else if ($license=="PUBLIC") {
       echo("Released into the public domain<br>");
     } else {
       echo("<a href='http://creativecommons.org/licenses/$license/3.0/' style='border: 0'><img src='http://i.creativecommons.org/l/$license/3.0/88x31.png' /><a><br>");
     }

     if ($creditname!=NULL){
       if ($crediturl!=NULL){
         echo("Original Author: <a href='$crediturl'>$creditname</a>");
       } else {
         echo("Original Author: $creditname");
     }
}
?>
   <br><br>
   You can edit this piece if you know the edit phrase.
   <form action="edit.php" method="post">
	<input type="text" name="editpass" size=30 />
        <input type="hidden" name="lucid" value=<? echo($id); ?> />
        <input type="submit" value="Edit" />
   </form>

   <p style="font-size: 12px;">
     This application is in Beta stage. Thanks for helping to test it!<br>
     If you see "Error. Click for details" click to bring up the java console.<br>
     <ul style="font-size: 12px;">
       <li>Press 5 to set the trace level to all.</li>
       <li>Try to reproduce the error. (You probably just have to refresh the page)</li>
       <li>Copy all the text on the Java console starting from the latest "basic: Loading applet..." line.</li>
       <li>Email it to mission2ridews@gmail.com with a description of what happened.</li>
     </ul>    
   </p>

  </body>
</html>
