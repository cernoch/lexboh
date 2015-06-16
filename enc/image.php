<?php
// the corpus pilot
include ('lib/Ducange.php');
// tools to deal with http
include ('lib/Web.php');
// where to find images
$jpgUri="http://media.enc.sorbonne.fr/ducange/jpg/";

// encourage browser cache of page
Web::notModified(__FILE__);
echo '<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" media="screen" href="ducange.css"/>
';
// a one pass loop, a C program pattern using break
do {
	$id=@$_REQUEST['id'];
	if (!$id) break;
	if (strlen($id) < 7) break;
	/* A/106a.jpg */
	$lettre=strtoupper(substr($id, 0, 1));
	if (!Ducange::$volume[$lettre]) break;
	$page=substr($id, 2, 3);
	if ($page < Ducange::$volume[$lettre][1] || $page > Ducange::$volume[$lettre][2]) break;
	$col=substr($id, 5, 1);
	if ($col == 'a') {
		// impossible de savoir si ça plante
		if ($page > Ducange::$volume[$lettre][1]) $prev=$lettre.'/'. sprintf( "%03d",($page - 1)) .'c.jpg';
		else $prev="";
		$next=$lettre.'/'. $page .'b.jpg';
	} else if ($col == 'b') {
		$prev=$lettre.'/'.$page.'a.jpg';
		$next=$lettre.'/'.$page.'c.jpg';
	} else if ($col == 'c') {
		$prev=$lettre.'/'.$page.'b.jpg';
		if ($page < Ducange::$volume[$lettre][2]) $next=$lettre.'/'. sprintf( "%03d",($page + 1)) .'a.jpg';
		else $next="";
	}
	// cette valeur semble correcte, on la stocke en cookie
	setcookie('side', 'image.php?id='.$id);
	$src=$jpgUri.$id;
} while (false);

if (!isset($src) || !$src) {
	$src="doc/accueil.jpg";
	$next="A/003a.jpg";
	$prev="Z/435c.jpg";
}
// précision biblio
if(isset($lettre) && Ducange::$volume[$lettre]) $pagination="t. ".Ducange::$volume[$lettre][0].", p. ".$page.", col. ".$col;
else $pagination="";
$nav='
<table width="300" cellspacing="0" cellpadding="0" class="navimage">
  <tr>
    <td>
      <a href="?id='.$prev.'">
        <img src="img/prev.png"/>
      </a>
    </td>
    <td valign="top" align="center"><span class="author">Du Cange</span> <i>et al.</i>, Glossarium..., éd. Favre.<br/>'.$pagination.'.</td>
    <td>
      <a href="?id='.$next.'">
        <img src="img/next.png"/>
      </a>
    </td>
  </tr>
</table>
';


echo '
  </head>
  <body class="image">
    <p class="pagination"><a href=".?clear=1"><span class="author">Du Cange</span> <i>et al.</i>, <i>Glossarium mediæ et infimæ latinitatis</i>.</a>
	Niort : Favre, 1883-1887. '.$pagination.'.</p>
';
if ($pagination=="") print "<p>&#xA0;</p> <p> </p>";
print '
<a href="?id='.$prev.'" class="gc" title="colonne précédente">
  <img src="img/prev.png"/>
</a>
<a href="?id='.$next.'" class="dr" title="Colonne suivante">
  <img src="img/next.png"/>
</a>
<img width="300" src="' . $src . '"/>
';
print $nav . '<div id="space_foot"></div>

	</body>
</html>
';
?>
