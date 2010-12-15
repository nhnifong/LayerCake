<?php
error_reporting(E_ALL);
ini_set('display_errors', true);


$dbhost = 'localhost';
$dbuser = 'cakephp';
$dbpass = 'nj2kjn8s9d';
$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
$dbname = 'layercakedb';
mysql_select_db($dbname);

$result = mysql_query("SELECT * FROM cakes ORDER BY published DESC;");

$npask = array();
$rows = array();
while ($row = mysql_fetch_array($result)){
  $rows[] = $row;
  $npask[] = $row['id'];
}
//$numonline = explode(" ",exec("python numplayers.py " . join(" ",$npask)));
$rowcount = count($rows);
for ($i=0; $i<$rowcount; $i++){
  $rows[$i]['online'] = 0;//$numonline[$i];
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

.latestlist{
  margin-left: 40px;
  margin-right: 40px;;
  margin-top: 60px;
  border-radius: 10px;
  -moz-border-radius: 10px;
  background-color: #CCCCCC;
  padding: 10px;
}

.cakeentry{
  border: solid #555555 3px;
  border-radius: 5px;
  -moz-border-radius: 5px;
  background-color: #779cb7;
  padding: 10px;
  margin: 25px;
  margin-top: 12px;
  margin-bottom: 12px;
  min-height: 100px;
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

.thumb{
  float:left;
  border: none;
  margin-right: 50px;
}

.entrytitle{
  float:left;
}

.people{
  float: right;
  position: relative;
  right: 180px;
  width: 150px; 
  top: 60px;
 }
    </style>

    <script type="text/javascript">

function heartToggle(){
  
}

    </script>

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

   <div>&nbsp</div>
    
   <div class="latestlist">

     <h2 class="new">Newest Submissions</h2>

<? 
foreach ($rows as $row){
  $id         = $row['id'];
  $title      = $row['title'];
  $numLayers  = $row['numLayers'];
  $width      = $row['width'];
  $height     = $row['height'];
  $author     = $row['author'];
  $published  = $row['published'];
  $hearts     = $row['hearts'];
  $online     = $row['online'];
?>

     <div class="cakeentry">
       <a href="view.php?id=<? echo $id; ?>">  
	 <img src="images/<? echo $id; ?>/thumb.png" class="thumb"/>
       </a>
       <div class="entrytitle">
         <h3><? echo $title ?></h3>
         <h4><? echo $author; ?></h4>

       </div>
       <div class="people">
  People online: &nbsp;&nbsp;<? echo $online; ?>
  Active rooms: &nbsp;&nbsp;&nbsp;&nbsp;<? echo ((int)($online / $numLayers)+1); ?>
       </div>
     </div>

     <? } ?>

   </div>

  </body>
</html>
