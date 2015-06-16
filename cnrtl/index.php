<?php // encoding="UTF-8"
include ('Ducange.php');

// collecter les docs
$docs=docs(PATHINFO);
$vue;
if(isset($_REQUEST['vue'])) $vue=$_REQUEST['vue'];
header ('Content-type: text/html; charset=utf-8');

// si on veut une enveloppe html
if ($vue != "div") {
  echo '
<?xml version="1.0" encoding="UTF-8"?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
  >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
  <head profile="http://dublincore.org/documents/2008/08/04/dc-html/">
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="ducange_cnrtl.css"/>
  </head>
  <body class="ducange_cnrtl">
    <div class="cadre">
';
}

?>
<div id="ducange" class="fro">
  <h1 class="messagecenter">P. <span class="author">Carpentier</span>, L. <span class="author">Henschel</span>. « Glossaire français » (1766, 1850)<br/>
  <i>in</i> <span class="author">du Cange</span>, et al., <i>Glossarium mediae et infimae latinitatis</i>,
  éd. augm., Niort : L. Favre, 1883–1887, t. 9.</h1>
<?php
if (count($docs)) foreach($docs as $d) {
  if ($d=="fro_lat") {
    echo '<p class="glossarium">Articles du glossaire latin correspondant aux renvois ci-dessus, les alinéas avec citations en anciens français.
    <a href="#" onclick="var o=document.getElementById(\'ducange\'); o.className=(o.className==\'fro\')?\'\':\'fro\'; return false;" class="but" title="Montrer / Cacher les alinéas latins">+/–</a></p>';
  }
  else if ($d=="lat") {
    echo '<p class="glossarium"><a href="#" onclick="var o=document.getElementById(\'ducange\'); o.className=(o.className==\'fro\')?\'\':\'fro\'; return false;" class="but" title="Montrer / Cacher les alinéas latins">+/–</a></p>';
  }
  else if (!is_object($d)) {
  }
  else if(get_class($d) == "HtmlInc") echo $d->body();
}
// que faire sir rien trouvé ?
else {
  echo '« ' . $pathinfo . ' » n\'a pu être trouvé dans le <i>Du Cange</i>.
  Vous trouverez un moteur de recherche à l\'adresse <a href="http://ducange.enc.sorbonne.fr/">http://ducange.enc.sorbonne.fr/</a>.
  ';
}


?>
</div>

<?php
if ($vue != "div") {
  echo '
    </div>
  </body>
</html>
';
}

?>
