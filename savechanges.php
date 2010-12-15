<?php
  //ini_set('display_errors',1);
  //error_reporting(E_ALL|E_STRICT);

$dbhost = 'localhost';
$dbuser = 'cakephp';
$dbpass = 'nj2kjn8s9d';
$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
$dbname = 'layercakedb';
mysql_select_db($dbname);

$id = mysql_real_escape_string($_POST['lucid']);
$pass = mysql_real_escape_string($_POST['dfmu']);

$title = "";
$title = mysql_real_escape_string($_POST['title']);

$author = "";
$author = mysql_real_escape_string($_POST['author']);

$istyle = 1;
$istyle = mysql_real_escape_string($_POST['istyle']);

$controlled = 0;
$controlled = mysql_real_escape_string($_POST['controlled']);

$license = "PUBLIC";
$license = mysql_real_escape_string($_POST['license']);

$creditname = "";
$creditname = mysql_real_escape_string($_POST['creditname']);

$crediturl = "";
$crediturl = mysql_real_escape_string($_POST['crediturl']);

$robots = 0;
if (array_key_exists('robots',$_POST)){
  if ($_POST['robots'] == "Enabled"){
    $robots = 1;
  }
}

$update_query = "UPDATE cakes SET title='$title', author='$author', istyle=$istyle, controlled=$controlled, robots=$robots, license='$license', creditname='$creditname', crediturl='$crediturl' WHERE (id=$id AND editpass='$pass');";
$result = mysql_query($update_query);

mysql_close($conn);

$_GET['id'] = $id;

require('view.php');

?>
