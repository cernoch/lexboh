<?php // encoding="UTF-8"
/**
<h1>Web, libraries for http request or response</h1>

© 2012, <a href="http://www.algone.net/">Algone</a>,
<a href="http://www.cecill.info/licences/Licence_CeCILL-C_V1-fr.html">licence CeCILL-C</a>
(LGPL compatible droit français)


<ul>
	<li>2012 [FG] <a onclick="this.href='mailto'+'\x3A'+'glorieux'+'\x40'+'algone.net'">Frédéric Glorieux</a></li>
</ul>


*/
class Web {
	static $mime=array(
		"css"=>'Content-Type: text/css; charset=UTF-8',
		"html"=>'Content-Type: text/html; charset=UTF-8',
		"jpg"=>'Content-Type: image/jpeg',
		"png"=>'Content-Type: image/png',
	);
	/**
	 * Return a static file from somewhere with best headers for caching
	 */
	public static function get($file) {
		if (!is_file($file) || !is_readable($file)) {
			header('HTTP/1.x 404 Not Found');
			exit;
		}
		header('Content-Length: ' . filesize($file));
		$ext=explode('.',$file);
		$ext=array_pop($ext);
		if ($ext=="html") header('Content-Type: text/html; charset=UTF-8');
		else if ($ext=="png") header('Content-Type: image/png');
		else if ($ext=="jpg") header('Content-Type: image/jpeg');
		else if ($ext=="css") header('Content-Type: text/css');
		else if ($ext=="js") header('Content-Type: text/javascript');
		self::notModified($file);
		// ob_clean(); // a l'air de déconner
		flush();
		readfile($file);
		exit;
	}

	const NO_COOKIE=0;
	const NO_DEFAULT=null;
	/**
	 * GET a parameter with possible cookie persistance. This is useful for params
	 * like "hits per page", "page style", and other value you want to set one time
	 * for some time.
	 *
	 * $name		: mandatory, the name of the parameter
	 * $default : a default value
	 * $expire	: durée de persistance (= cookie) en secondes
	 * $pattern : regexp to validate value
	 *
	 * return	: the value with quotes escaped (to be inserted in an attribute value)
	 */
	public static function param($name, $default=FALSE, $expire=0, $pattern=FALSE, $query=FALSE) {
		$value=FALSE;
		// if key as a dot, hack php
		if (strpos($name, '.') !== FALSE) $name=strtr($name, '.', '_');
		// prendre le paramètre de requête
		if(isset($_GET[$name])) $value=$_GET[$name];
		if(isset($_POST[$name])) $value=$_POST[$name];
		if ($value) {
			if (get_magic_quotes_gpc()) $value = stripslashes($value);
			// is ISO ? find a best test
			// if (preg_match('/[\xC0-\xFD]/', $value)) $value=utf8_encode ($value);
		}
		// validate value against pattern
		if ($pattern && $value && !preg_match($pattern, $value)) $value=FALSE;
		// if no expire, no cookie, neither read or write
		if (!$expire) {
			// if(!$value && isset($_COOKIE[$name])) $value=$_COOKIE[$name];
		}
		// if a value, set cookie, do not $_COOKIE[$name]=$value
		else if ($value) {
			// if a number
			if ($expire > 60) setcookie($name, $value, time()+ $expire);
			// session time
			else setcookie($name, $value);
		}
		// if empty, delete cookie
		else if ($value==="") {
			setcookie ($name);
		}
		// if cookie stored, load it
		else {
			if(isset($_COOKIE[$name])) $value=$_COOKIE[$name];
		}
		if(!$value && $default) $value=$default;
		/*
		// escape quotes, useful here ?
		$value= preg_replace(
			array('/"/'),
			array('&quot;'),
			$value
		);
		*/
		return $value;
	}

	/**
	 * Handle repeated parameters values, especially in multiple select.
	 * $_REQUEST propose a strange PHP centric interpretation of http protocol, with the bracket keys
	 * &lt;select name="var[]">
	 *
	 * $query : optional, a "query string" ?cl%C3%A9=%C3%A9%C3%A9&param=valeur1&param=&param=valeur2
	 * return : Array (
	 *	 "clé" => array("éé"),
	 *	 "param" => array("valeur1", "", "valeur2")
	 * )
	 *
	 *
	 */
	public static function params( $name=FALSE, $query=FALSE, $expire=0) {
		if (!$query) $query=Web::query();
		// remplir un tableau
		$params=array();
		$a = explode('&', $query);
		// if empty, do not return now, wait for possible cookie store value
		if(!$a || count($a)==0 || !$a[0]);
		else foreach ($a as $p) {
			list($k, $v) = preg_split('/=/', $p);
			$k=urldecode($k);
			$v=urldecode($v);
			// semble une chaîne ISO, traduire les accents
			if (preg_match('/[\xC0-\xFD]/', $k+$v)) {
				$k=utf8_encode ($k);
				$v=utf8_encode ($v);
			}
			$params[$k][]=$v;
		}
		// no key requested, return all params, do not store cookies
		if (!$name) return $params;
		// a param is requested, values found
		else if (isset($params[$name])) $params=$params[$name];
		// no param for this name
		else $params=array();


		// no cookie store requested
		if(!$expire);
		// if empty ?, delete cookie
		else if (count($params)==1 && !$params[0]) {
			setcookie($name);
		}
		// if a value, set cookie, do not $_COOKIE[$name]=$value
		else if (count($params)) {
			// if a number
			if ($expire > 60) setcookie($name, serialize($params), time()+ $expire);
			// session time
			else setcookie($name, serialize($params));
		}
		// if cookie stored, load it
		else if(isset($_COOKIE[$name])) $params=unserialize($_COOKIE[$name]);
		return $params;
	}

	/**
	 * Send the best headers for cache, according to the request and a timestamp
	 */
	public static function notModified($file, $force=false) {
		if (!$file) return false;
		$filemtime=false;
		// seems already a filemtime
		if (is_int($file)) $filemtime=$file;
		// if array of file, get the newest
		else if (is_array($file)) foreach($file as $f) {
			// if not file exists, no error
			if (!file_exists($f)) continue;
			$i=filemtime($f);
			if ($i && $i > $filemtime) $filemtime=$i;
		}
		else $filemtime=filemtime($file);
		if(!$filemtime) return $filemtime;
		// take script file date
		header("X-File: ". basename($_SERVER['SCRIPT_FILENAME']));
		// Default expires
		if (filemtime($_SERVER['SCRIPT_FILENAME']) > $filemtime) {
			$filemtime=filemtime($_SERVER['SCRIPT_FILENAME']);
		}
		// $modification=substr(date('r', $filemtime), 0, -5).'GMT';
		$modification=gmdate('D, d M Y H:i:s', $filemtime).' GMT';
		// header("X-Date: ". substr(gmdate('r'), 0, -5).'GMT');
		// date de modification
		header("Last-Modified: $modification");
		// page key
		$etag = '"'.md5($modification).'"';
		header("ETag: $etag");
		// tests for 304
		if($force) return;
		if (self::noCache()) return;
		$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) :false;
		$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : false;
		// send 304
		if ( ($if_none_match && $if_none_match == $etag) || $if_modified_since == $modification) {
			header('HTTP/1.x 304 Not Modified');
			exit;
		}
	}

	/**
	 * If client ask a forced relaod.
	 */
	public static function noCache() {
		// pas de cache en POST
		if ($_SERVER['REQUEST_METHOD'] == 'POST') return 'POST';
		if (isset ($_SERVER['HTTP_PRAGMA']) && stripos($_SERVER['HTTP_PRAGMA'], "no-cache") !== false) return "Pragma: no-cache";
		if (isset ($_SERVER['HTTP_CACHE_CONTROL']) && stripos($_SERVER['HTTP_CACHE_CONTROL'], "no-cache") !== false) return "Cache-Control: no-cache";
		if (isset($_REQUEST['no-cache'])) return '?no-cache=';
		if (isset($_REQUEST['force'])) return '?force=';
		return false;
	}


	/**
	 * trouver query string nettoyée de différents paramètres
	 *
	 *
	 * query() : retourne la chaîne de requête complète ?A=1&A=&B=2
	 * query(true) : supprime les paramètres vides ?A=1&B=2
	 * query("B|C|D") : filtre certains paramètres ?A=1
	 * query("?X=1&Y=&Z=2", "Y|Z") : filtre certains paramètres ?X=1
	 *
	 */
	public static function query($p1=false, $p2=false) {
		// query passée en variable
		if ($p2) {
			$exclude=$p2;
			$query=preg_replace( '/&amp;/', '&', $p1);
		}
		// query en POST
		else if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$exclude=$p1;
			if (isset($HTTP_RAW_POST_DATA)) $query=$HTTP_RAW_POST_DATA;
			else $query = file_get_contents("php://input");
		}
		// query en GET
		else {
			$exclude=$p1;
			$query=$_SERVER['QUERY_STRING'];
		}
		// suppression de paramètres en regex
		if (strlen($exclude)) $query=preg_replace( '/&('.$exclude.')=[^&]*/', '', '&'.$query);
		// supprimer les paramètres vides, ne pas chercher à matcher entre deux &, sinon le parser redémarre après pour le suisvant
		if ($exclude)  $query=preg_replace( array('/[^&=]+=&/', '/&$/'), array('', ''), $query.'&');
		return $query;
	}
	/**
	 * "Query String Append" (Rewrite rules)
	 * append non empty parameters as a query string for links
	 */
	public static function qsa($exclude=false) {
		$qsa=self::query($exclude);
		$qsa=preg_replace( '/&[^=&]*=&/', '&', '&'.$qsa.'&');
		return preg_replace('/&/', '&amp;', $qsa);
	}

	/**
	 * Give pathinfo with priority order of different values.
	 * The possible variables are not equally robust
	 *
	 * http://localhost/~user/teipot/doc/install&sons?a=1&a=2#ancre
	 *
	 * — $_SERVER['REQUEST_URI'] OK /~user/teipot/doc/install&sons?a=1&a=2
	 * — $_SERVER['SCRIPT_NAME'] OK /~user/teipot/index.php
	 * — $_SERVER['PHP_SELF'] /~user/teipot/index.php/doc/install&sons (le pathinfo n'est parfois pas transmis mod_rewrite)
	 * — $_SERVER['PATH_INFO'] sometimes unavailable, ex: through mod_rewrite /doc/install&sons
	 * — $_SERVER['SCRIPT_URI'] sometimes, ex : http://teipot.x10.mx/install&bon
	 * — $_SERVER['PATH_ORIG_INFO'] found on the web
	 *
	 */
	static $pathinfo;
	public static function pathinfo() {
		if (self::$pathinfo) return self::$pathinfo;
		$pathinfo="";
		if (!isset($_SERVER['REQUEST_URI'])) return $pathinfo; // ligne de commande
		list($request)=explode('?', $_SERVER['REQUEST_URI']);
		if(strpos($request, '%') !== false) $request=urldecode($request);
		if (strpos($request, $_SERVER['SCRIPT_NAME']) === 0)
			$pathinfo=substr($request, strlen($_SERVER['SCRIPT_NAME']));
		else if (strpos($request, dirname($_SERVER['SCRIPT_NAME'])) === 0)
			$pathinfo=substr($request, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		// if nothing found, try other variables
		if ($pathinfo); // something found, keep it
		else if (isset($_SERVER['PATH_ORIG_INFO'])) $pathinfo=$_SERVER['PATH_ORIG_INFO'];
		else if (isset($_SERVER['PATH_INFO'])) $pathinfo=$_SERVER['PATH_INFO'];
		else if (isset($_REQUEST['id'])) $pathinfo=$_REQUEST['id'];
		// why trim last / ?
		self::$pathinfo=ltrim($pathinfo, '/');
		return self::$pathinfo;
	}
	/**
	 * Get the needed ../ from a path to resolve relative path
	 */
	static $pathbase;
	public static function pathbase() {
		if (self::$pathbase) return self::$pathbase;
		self::$pathbase=str_repeat("../", substr_count(self::pathinfo(), '/'));
		return self::$pathbase;
	}
}

/*
 TODO, un serveur local
#!/usr/bin/php // encoding="UTF-8"

// pas de limite de temps
set_time_limit(0);

echo "Teipot, votre serveur local.\n";

// Un serveur local
$host="127.0.0.1";
$host_name="localhost";
$port = 1234;


function hein {
// ma socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create : marche pas");
// J'écoute
socket_bind($socket, $host, $port);
socket_listen($socket, 5);
echo "Serveur Teipot démarré, ouvrez votre navigaeur à l'adresse http://$host_name:$port/\n";


while (true) {
	$client = socket_accept($socket);
	// 1ko devrait suffire pour une requête en Get ?
	$request = socket_read($client, 1024);
	$response = "Votre requête était : ".$request."\r\n";
	socket_write($client, $response);
	socket_close($client);
}

socket_close($socket);
}

$socket = stream_socket_server("tcp://0.0.0.0:1234", $errno, $errstr, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN);
if (!$socket) {
		die("$errstr ($errno)");
}
// on démarre la boucle d'écoute
while ($conn = stream_socket_accept($socket, 100000)) {
	$req_raw = fread ($conn, 2046);
	// $request = preg_split("\n", $request, 10, PREG_SPLIT_NO_EMPTY);
	$request=explode("\n", $req_raw, 10);
	$uri=explode(" ", $request[0]);
	$uri_raw=urldecode($uri[1]);
	$query=substr( $uri_raw, strpos($uri_raw . '?', '?')+1);
	$uri=substr( $uri_raw, 0, strpos($uri_raw .'?', '?'));

	$param="xml";
	$xml=substr($query, strpos($query, $param.'='));
	$xml=substr($xml, 0, strpos($xml.'&', '&'));
	$xml=substr($xml, strpos($xml, '=')+1);


	ob_start();
	// à partir d'ici plus rien ne sort sur la console, tout va au client (le navigateur)

	// prendre le chemin et les paramètres demandés en GET

echo "HTTP/1.x 200 OK
Cache-Control: no-cache
Server: Teipot
Content-Language: fr
Content-Type: text/html;charset=utf-8

";
?>
<html>
	<head>
		<title>Page</title>
	</head>
	<body>
		<?php echo(date('Y-m-d g:i:s') . "\n"); ?>
		<h1><?php echo ($uri_raw . "<br/>" . $uri . "<br/>" . $query	); ?></h1>
		<?php echo("<pre>".$req_raw."</pre>"); ?>
		<form method="get">
			<textarea rows="5" cols="80" name="xml"><?php echo($xml); ?></textarea>
			<br/>
			<button type="submit">Envoyer</button>
		</form>
		<table border="1">
			<tr><th>noeud</th><th>byte</th><th>col</th></tr>
<?php


$xml_parser = xml_parser_create();
// use case-folding so we are sure to find the tag in $map_array
xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
xml_set_element_handler($xml_parser, "x_start", "x_end");
xml_set_character_data_handler($xml_parser, "x_chars");

function quoi () {
if (!($fp = fopen($file, "r"))) {
		die("could not open XML input");
}
while ($data = fread($fp, 4096)) {
		if (!xml_parse($xml_parser, $data, feof($fp))) {
				die(sprintf("XML error: %s at line %d",
										xml_error_string(xml_get_error_code($xml_parser)),
										xml_get_current_line_number($xml_parser)));
		}
}
}
xml_parse($xml_parser, $xml);
xml_parser_free($xml_parser);

?>
		</table>
		<form method="post">
			post <input name="post"/>
		</form>
	</body>
</html>
<?php
	// envoyer ce qui a été écrit ci dessus au client
	fwrite($conn, ob_get_contents());
	// surtout ne pas oublier de régulièrement nettoyer le buffer
	ob_end_clean();
	fclose($conn);
}
fclose($socket);


function x_start($parser, $name, $attrs) {
	echo("\n<tr><td>&lt;" . $name . "&gt;</td><td>" . xml_get_current_byte_index($parser) . "</td><td>" . xml_get_current_column_number($parser) . "</td></tr>");
}

function x_end ($parser, $name) {
	global $text;
	echo("\n<tr><td>&lt;/" . $name . "&gt;</td><td>" . xml_get_current_byte_index($parser) . "</td><td>" . xml_get_current_column_number($parser) . "</td></tr>");
	echo"<td>";
	print_r(str_word_count($text, 2, 'àáãéèêë'));
	echo "</td>";
	echo "</tr>";
	// reset texte courant
	$text="";
}

function x_chars ($parser, $data)	{
	global $text;
	$text.=$data;
	echo("\n<tr><td>" . $data . "</td><td>" . xml_get_current_byte_index($parser) . "</td><td>" . xml_get_current_column_number($parser) . "</td>");
	echo "</tr>";
}
*/

?>
