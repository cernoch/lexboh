<?php
$microtime=microtime(true);
// params specific to Du Cange
include ('lib/Ducange.php');
// object for word trace
include ('lib/CookieCrumb.php');
// object to deal with http params
include ('lib/Web.php');
$pathinfo=Web::pathinfo();

$solrUri="http://elec.enc.sorbonne.fr/tomcat55/solr/ducange/";


// historique, avec gestion de cookie, avant sortie
$hist=new CookieCrumb("glossarium");


// What to display in the side frame ?
if (Web::param("clear")) {
	$hist->reset();
	setcookie("side");
	$side="about:blank";
}
else if (isset($_COOKIE["side"])) {
	$side=$_COOKIE["side"];
}
// documentation
else if (strpos($pathinfo, '/') !== false) {
	$side="about:blank";
}
else if($pathinfo) {
	$side="suggest.php?q=".$pathinfo;
}
else {
	$side="about:blank";
}


$cookieCrumb="";
if ($ul=$hist->ul()) $cookieCrumb= " Derniers articles consultés : ". $ul . '   (<a href="?clear=1">vider</a>)' ;

// generate search form before html output if cookies are needed
$searchForm=searchForm();
// TODO Should be no more useful
$vue="";


$solr="";
if(isset($_REQUEST['q'])) $solr=solrQuery();

?><!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" media="screen" href="enc.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo Web::pathbase(); ?>ducange.css"/>
    <link href='http://fonts.googleapis.com/css?family=Trykker&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
    <script src="<?php echo Web::pathbase(); ?>lib/Cookie.js" type="text/javascript">//</script>
<?php
// SQLite result, populate by a query and given
$result=array();
if ($pathinfo) {
	$term=$pathinfo;
	$sql = Ducange::$pdo->prepare("SELECT * FROM entry WHERE id = ?");
	$sql->execute(array($term));
	$result=$sql->fetchAll(PDO::FETCH_ASSOC);
	if (!count($result)) {
		$term=Ducange::lat_id($pathinfo);
		$sql = Ducange::$pdo->prepare("SELECT * FROM entry WHERE id = ?;");
		$sql->execute(array($term));
		$result=$sql->fetchAll(PDO::FETCH_ASSOC);
	}
	if (!count($result)) {
		$term=Ducange::norm($pathinfo);
		$sql = Ducange::$pdo->prepare("SELECT entry.id, entry.label, entry.head, entry.body FROM entry, form WHERE form.norm = ? and form.id=entry.id GROUP BY entry.id");
		$sql->execute(array($term));
		$result=$sql->fetchAll(PDO::FETCH_ASSOC);
	}
}
/*
if (count($result)) {
	echo $result[0]['head'];
}
else {
*/
	echo "<title>Latinitatis medii aevi lexicon</title>\n";
//}
?>
  </head>
  <body class="ducange<?php // if($vue=="doc") echo " fixed" ?>">
    <script type="text/javascript">window.document.body.className+=' '+Cookie.get('noImage'); </script>
    <div id="header">
      <div class="container">
      	<div style="background-color: #4086a9; height: 130px; padding: 10px 0 0 340px">
      	  <address class="kks">
      	    <a href="http://www.ics.cas.cz/">Kabinet pro klasická studia</a>
      	  </address>
      	  <address class="fuav">
      	    <a href="http://www.ics.cas.cz/">Filosofického ústavu AV ČR, V.V.I</a>
      	  </address>
          <address class="lexicon">
            <a href="<?php echo Web::pathbase()?>?clear=1">Latinitatis medii aevi lexicon Bohemorum – Slovník středověké latiny v českých zemích, I–II. Praha: Academia 1992. Elektronická verse 1.0‏</a>
          </address>
        </div>
      	<div style="background-color: #cbc9c6; height: 10px">
      	</div>
      </div>
    </div>
    <div style="position: absolute; left:0; top:150px; right:0px; bottom:0px">
    <div class="container">
      <iframe name="side" id="side" src="<?php echo Web::pathbase(),$side; ?>" frameborder="0" scrolling="auto"> </iframe>
<?php /*        
        <div id="toolbar">
          <a class="but" href="#" id="imageCache" title="Cacher le panneau"
style="float:left;"
onclick="window.document.body.className=window.document.body.className.replace(/ *noImage/gi, ' ')+' noImage'; Cookie.set('noImage', ' noImage'); return false;">«</a>
          <a class="but" href="#" id="imageMontre" title="Montrer le panneau"
style="float:left;"
onclick="window.document.body.className=window.document.body.className.replace(/ *noImage/gi, ' '); Cookie.del('noImage');  return false;
    ">»</a>
          <ul>
            <li><a class="aide" href="<?php echo Web::pathbase(); ?>doc/aide">Aide</a></li>
            <li><a class="telecharger" href="<?php echo Web::pathbase(); ?>doc/sources">Téléchargements</a></li>
            <li><a class="doc" href="<?php echo Web::pathbase(); ?>doc/schema">Documentation</a></li>
            <li><a class="biblio" href="<?php echo Web::pathbase(); ?>doc/biblio">Publications</a></li>
            <li><a class="presentation" href="<?php echo Web::pathbase(); ?>doc/dia/">Présentation</a></li>
          </ul>
        </div>
        <div id="ariane">&#xA0;
          <?php  print $cookieCrumb; ?>
        </div>
*/ ?>        
        <div <?php  echo ' class="thecontent '.$vue.'"'; if($vue!="doc") echo ' id="article"';?>>
<?php
// search form
print $searchForm;

// documentation
if (file_exists($file=$pathinfo.'.html')) {
	$stream=fopen($file , "r");
	fpassthru ($stream);
	fclose($stream);
}
else if (count($result)) {
	if (count($result) > 1 ) {
		$first=true;
		foreach($result as $row) {
			if(!$first) echo ', ';
			$first=false;
			echo '<a href="#',$row['id'],'">',$row['label'],'</a>';
		}
		echo '.';
	}
	foreach($result as $row) {
		echo $row['body'];
	}
}
else if ($pathinfo) {
	echo '<h1>Heslo nebylo nalezeno.</h1>';
}
// search results
else if ($solr) {
	$uri=$solrUri . '/select/?mode=div&' . $solr;
	print "\n<!-- $uri -->";
	$stream=fopen($uri , "r");
	fpassthru ($stream);
	fclose($stream);
}
// default page
else {
	$stream=fopen("doc/index.html" , "r");
	fpassthru($stream);
	fclose($stream);
}
?>
      </div>
      <div id="footer">
      	© <a class="link img" href="http://www.enc.sorbonne.fr" title="Contributions de l’École de des chartes"><img src="<?php echo Web::pathbase(); ?>img/enc.png" style="vertical-align: middle" alt="ENC" /></a>,
        © <a class="link img" href="http://www.ics.cas.cz/">Filosofický ústav AV ČR, v. v. i.</a>
        <!--<a class="link" href="mailto:">JO</a> &amp; <a class="link" href="mailto:radomir.cernoch@gmail.com">RČ</a>-->
        <a class="link img" href="http://www.tei-c.org/release/doc/tei-p5-doc/fr/html/REF-ELEMENTS.html" target="blank" title="Sources en XML/TEI P5" ><img style="vertical-align: middle" src="<?php echo Web::pathbase(); ?>img/tei.png" align="top" alt="TEI" /></a>
      </div>
    </div>
    </div>
	<script src="<?php echo Web::pathbase(); ?>ducange.js" type="text/javascript">//</script>
	<script src="<?php echo Web::pathbase(); ?>lib/Form.js" type="text/javascript">//</script>
    <script src="<?php echo Web::pathbase(); ?>lib/Tree.js" type="text/javascript">//</script>

<?php
echo '
	<script type="text/javascript">
Form.classChecked();
';
/*
if ($vue == FRO) echo "\n".'Suggest.create("q", Suggest.data.glossaire);';
else if(Web::param("suggest") != 'no') echo "\n".'Suggest.create("q", "suggest.php?id=q&q=");'; // ? avec ou pas de suggest ?
*/
echo "\n".'</script>';
print '<!--'.(microtime(true) - $microtime).'ms -->';
?>
  </body>
</html>
<?php


/**
 * URI solr selon les paramètres du formulaire de recherche
 *
 * q : texte recherché
 * f : champ de recherche (text|forms|lat|fro|grc)
 * exact : booléen
 * sort : champ de tri
 * navigation précédent suivant
 */
function solrQuery($q=null, $f=null) {
	// persistance 30 jours pour certains paramètres
	$expire=60*60*24*30;
	if (!$q) $q=urlencode(Web::param('q'));
	if (!$f) $f=Web::param("f",Web::NO_DEFAULT,Web::NO_COOKIE,'/(text|forms|lat|fro|grc)/');
	$sort='';
	if (isset($_GET['sort'])) $sort='&sort='. urlencode($_GET['sort']);
	$exact=(isset($_REQUEST['exact']) && $_REQUEST['exact']);
	if (!$f) $f="text";
	if ($exact) $df="&df=".$f."_exact";
	else $df="&df=$f";
	$hl="";
	$hl="&hl.fl=$f";
	if ($exact) $hl="&hl.fl=$f"."_exact";
	// nombre de snippets ?
	// $snip=Web::param("hl.snippets", null, $expire);
	$start="";
	if(isset($_REQUEST['start']) && $_REQUEST['start']>0) $start="&start=".$_REQUEST['start'];
	$uri="q=$q$df$sort$hl$start";
	return $uri;
}

/**
 * Logique d'affichage du formulaire, différente de Solr
 *
 */
function searchForm($q=null) {
	if(!$q && isset($_REQUEST['q'])) $q=$_REQUEST['q'];
	if(!$q) $q=Web::pathinfo();
	if (strpos($q, '/')!==false) $q="";

	$q=str_replace('"', '&quot;', $q);
	$suggest=Web::param("suggest",Web::NO_DEFAULT,3600,'/|lat|fro|no/');
	$f=Web::param("f",Web::NO_DEFAULT,Web::NO_COOKIE,'/(text|forms|lat|fro|grc)/');
	$exact=(isset($_REQUEST['exact']) && $_REQUEST['exact']);
	return '
<form class="q" action="." autocomplete="off"
  onsubmit="
var field;
for (var i=0; i &lt; this.f.length; i++) if (this.f[i].checked) field=this.f[i].value;
if(!field) {
  var q=this.q.value.replace(/^\s+/, \'\').replace(/\s+$/, \'\');
  window.location.href=q;
  return false;
}
"
>
  <div class="border">
'/*
	<label>
	  <input' . (($f == '')?' checked="checked"':'') . ' name="f" value="" class="radio" type="radio"/> consulter un article
	</label>
	<label>
	  <input' . (($f == 'text')?' checked="checked"':'') . ' name="f" id="fulltext" value="text" class="radio" type="radio"/> recherche plein texte
	</label>
    <label>
      <input name="exact"'. ($exact?' checked="checked"':'') .' type="checkbox"> formes exactes
    </label>
*/.'
    <div class="middle">
      <input name="f" value="" type="hidden"/>
      <input id="q" class="input" onkeyup="return qKey(this)" autocomplete="off" accesskey="q" name="q" size="40"  value="' . $q . '"/>
      <input type="submit" class="submit" value="Hledat"/>
    </div>
'
/*
      <label>
        <input' . (($f == 'forms')?' checked="checked"':'') . ' name="f" value="forms" class="radio" type="radio"/>
        vedettes et renvois </label>
*/
/*	.'<label>
        <input' . (($f == 'lat')?' checked="checked"':'') . ' name="f" value="lat" class="radio" type="radio"/>
        citations latines </label>

      <label>
        <input' . (($f == 'fro')?' checked="checked"':'') . ' name="f" value="fro" class="radio" type="radio"/>
        citations françaises </label>

      <label>
        <input' . (($f == 'grc')?' checked="checked"':'') . ' name="f" value="grc" class="radio" type="radio"/>
        citations grecques </label>
*/.'
  </div>
</form>
';
}


?>
