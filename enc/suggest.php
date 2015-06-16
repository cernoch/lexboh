<?php
/**
 * Suggestion of headwords
 */
// Du Cange configuration
require ('lib/Ducange.php');

$rows=50;
if (isset($_REQUEST['rows'])) $rows=$_REQUEST['rows'];


$q="";
if (isset($_REQUEST['q'])) $q=$_REQUEST['q'];
$id="";
if (isset($_REQUEST['id'])) $id=$_REQUEST['id'];
// keep memory of last visited page
setcookie('side', 'suggest.php?q='.$q);

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" media="screen" href="ducange.css"/>
	<base target="_top"/>
  </head>
  <body class="suggest">
<?php
$q=Ducange::norm($q);
$q=strtr($q, array("*"=>"%"));
$sql=Ducange::$pdo->prepare('SELECT * FROM form WHERE norm LIKE ? LIMIT ?');
$sql->execute(array($q . '%' , $rows));
while ($row = $sql->fetch(PDO::FETCH_BOTH)) {
	echo '<a href="',$row['id'],$row['anchor'].'" class="',$row['rend'],'">',$row['text'],'</a>',"\n";
}

?>
  </body>
</html>
