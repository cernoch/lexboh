<?php // encoding="UTF-8"

/**
 * Specific to the corpus
 */
// class and method are mainly static, instantiation allow to initialize different values
new Ducange();
class Ducange {
	/** pages of volumes */
	public static $volume=array(
		'A' => array(1,   3, 506),
		'B' => array(1, 507, 802),
		'C' => array(2,   1, 688),
		'D' => array(3,   1, 221),
		'E' => array(3, 221, 384),
		'F' => array(3, 385, 642),
		'G' => array(4,   1, 147),
		'H' => array(4, 147, 273),
		'I' => array(4, 274, 480),
		// 'J' => array(4;
		'K' => array(4, 480, 491),
		'L' => array(5,   1, 158),
		'M' => array(5, 158, 564),
		'N' => array(5, 564, 629),
		'O' => array(6,   1,  82),
		'P' => array(6,  83, 580),
		'Q' => array(6, 581, 619),
		'R' => array(7,   1, 246),
		'S' => array(7, 246, 694),
		'T' => array(8,   1, 222),
		// 'U' => array(8,,),
		'V' => array(8, 222, 398),
		'W' => array(8, 398, 419),
		'X' => array(8, 419, 421),
		'Y' => array(8, 421, 424),
		'Z' => array(8, 425, 435),
	);

	static $lat_tr;
	/** Constructor to initialize different things */
	public static $ducange_sqlite;
	function __construct() {
		self::$ducange_sqlite=dirname(dirname(__FILE__)).'/ducange.sqlite';
		// connect  SQLite base
		self::connect();
		// load char replacement table for medieval latin
		self::$lat_tr=self::json(dirname(__FILE__).'/lat.tr');
	}
	/**
	 * SQLite connection
	 */
	public static $pdo;
	public static function connect() {
		if (!file_exists(dirname( self::$ducange_sqlite ))) mkdir(dirname(self::$ducange_sqlite), 0775, true);
		self::$pdo=new PDO('sqlite:'.self::$ducange_sqlite );
		// try/catch is an expensive programming
		// $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// error as classic warning
		self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	}
	/**
	 * Graphic normalisation of latin
	 */
	public static function norm($form) {
		$form=mb_strtolower($form, "UTF-8");
		$form="^".$form."$";
		$form=strtr($form, self::$lat_tr);
		$form=trim($form, " \t\n^$");
		return $form;
	}

	/** Normalization pattern of an headword as an id */
	static $fro_id=array(
		"ç"  => "c",
		"Ç"  => "C",
		"œ"  => "oe",
		"Œ"  => "OE",
		"æ"  => "ae",
		"Æ"  => "AE",
		"é"  => "e",
		"É"  => "E",
		"è"  => "e",
		"È"  => "E",
		"ê"  => "e",
		"Ê"  => "E",
		"ë"  => "e",
		"Ë"  => "E",
		"ü"  => "u",
		"Ü"  => "U",
		"û"  => "u",
		"Û"  => "U",
		" 1" => "1",
		" 2" => "2",
		" 3" => "3",
		" 4" => "4",
		" 5" => "5",
		" 6" => "6",
		" 7" => "7",
		" 8" => "8",
		" 9" => "9",
		" 0" => "0",
		"."  => "",
		" "  => "-",
	);
	public static function fro_id($form) {
		$form=mb_strtoupper($form, "UTF-8");
		$form=strtr($form, self::$fro_id);
		$form=trim($form);
		return $form;
	}

	/**
	 * specific to latin id, JU > IV
	 */
	static $lat_id=array(
		"^J" => "^I",
		"^U" => "^U",
		"^Ü" => "^U",
		"^Û" => "^U",
	);
	public static function lat_id($form) {
		$form=self::fro_id($form);
		$form=strtr('^'.$form, self::$lat_id);
		$form=substr($form, 1);
		return $form;
	}


	/**
	 * Load XML files in the SQLite base
	 * by php functions called from XSLT
	 */
	static $sqlEntry;
	static $sqlForm;
	public static function create($glob="*.xml") {
		self::$pdo->exec("DROP TABLE IF EXISTS entry");
		self::$pdo->exec("
			CREATE TABLE entry (
			-- html article
				id    TEXT, -- entry/@xml:id upper case
				cb    TEXT, -- cb/@n
				label TEXT, -- displayable label for article entry//form[1]
				body  TEXT, -- html body of article
				head  TEXT  -- html head of article (title, metas)
			)
		");
		self::$pdo->exec("DROP TABLE IF EXISTS form");
		self::$pdo->exec("
			CREATE TABLE form (
				-- map headwords to entry id
				rend   TEXT,     -- level of headword bold, small caps, italic
				text   TEXT,     -- a form lower cased entry//form
				norm   TEXT,     -- a normalized version of the form
				id     TEXT,     -- entry/@xml:id
				anchor TEXT,     -- entry/dictScrap/@xml:id
				entry  INTEGER   -- integer rowid of the entry, a post SQL Query
			)
		");
		$proc = new XSLTProcessor();
		$proc->registerPHPFunctions();
		$dom=new DOMDocument();
		$dom->load(dirname(dirname(dirname(__FILE__))) .'/transform/ducange_php.xsl');
		$proc->importStyleSheet($dom);

		// prepared insert statements
		self::$sqlEntry=self::$pdo->prepare('INSERT INTO entry(id, cb, label, body, head)     VALUES(?,?,?,?,?)');
		self::$sqlForm=self::$pdo->prepare ('INSERT INTO  form(rend, text, norm, id, anchor) VALUES(?,?,?,?,?)');


		$glob=dirname(dirname(dirname(__FILE__)))."/xml/" . $glob;
		foreach ( glob($glob) as $file) {
			echo $file,"\n";
			// start transaction
			self::$pdo->beginTransaction();
			$dom->load($file);
			$proc->transformToXML($dom);
			// commit all pending insert
			self::$pdo->commit();
		}
		// créer les index
		self::$pdo->exec("CREATE INDEX entryId    ON entry (id ASC)");
		self::$pdo->exec("CREATE INDEX entryCb    ON entry (cb ASC)");
		self::$pdo->exec("CREATE INDEX entryLabel ON entry (label ASC)");
		self::$pdo->exec("CREATE INDEX formRend   ON form  (rend ASC)");
		self::$pdo->exec("CREATE INDEX formText   ON form  (text ASC)");
		self::$pdo->exec("CREATE INDEX formNorm   ON form  (norm ASC)");
		self::$pdo->exec("CREATE INDEX formId     ON form  (id ASC)");
		self::$pdo->exec("CREATE INDEX formAnchor ON form  (anchor ASC)");
		// map form.id to entry.rowid for perfs
	}
	/**
	 * Insert an article in the base, called from ducange_php.xsl
	 */
	static function entry($id, $cb, $label, $body, $head) {
		self::$sqlEntry->execute(array(
			$id,
			$cb,
			$label,
			self::xml($body),
			self::xml($head),
		));
	}
	/**
	 * Insert an article in the base, called from ducange_php.xsl
	 */
	static function form($rend, $form, $id, $anchor) {
		self::$sqlForm->execute(array(
			$rend,
			$form,
			self::norm($form),
			$id,
			$anchor,
		));
	}
	/**
	 * get XML from a dom sent by xsl
	 */
	static function xml($nodeset) {
		$xml='';
		if (!is_array($nodeset)) $nodeset=array($nodeset);
		foreach($nodeset as $doc) {
			$doc->formatOutput=true;
			$doc->substituteEntities=true;
			$doc->encoding="UTF-8";
			$doc->normalize();
			$xml.=$doc->saveXML($doc->documentElement);
		}
		return $xml;
	}
	/**
	 * load and clean json resources for php, return array()
	 */
	static function json($file) {
		$content=file_get_contents($file);
		$content=substr($content, strpos($content, '{'));
		$content= json_decode($content, true);
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
			break;
			case JSON_ERROR_DEPTH:
				echo "$file — Maximum stack depth exceeded\n";
			break;
			case JSON_ERROR_STATE_MISMATCH:
				echo "$file — Underflow or the modes mismatch\n";
			break;
			case JSON_ERROR_CTRL_CHAR:
				echo "$file — Unexpected control character found\n";
			break;
			case JSON_ERROR_SYNTAX:
				echo "$file — Syntax error, malformed JSON\n";
			break;
			case JSON_ERROR_UTF8:
				echo "$file — Malformed UTF-8 characters, possibly incorrectly encoded\n";
			break;
			default:
				echo "$file — Unknown error\n";
			break;
		}
		return $content;
	}

}

// import, ne rien faire
if (realpath($_SERVER['SCRIPT_FILENAME']) != realpath(__FILE__));
// ligne de commande, indexer en sqlite
else if (php_sapi_name() == "cli") {
    array_shift($_SERVER['argv']); // [0]=fileName.php
	if (isset($_SERVER['argv'][0])) Ducange::create($_SERVER['argv'][0]);
	else Ducange::create();
}
/*
kept for memory

# Indexer des fichiers sur une installation Solr
solr_post:
	@touch solr/$(CORPUS).xml ; \
	echo "Indexer $$LETTRE, dans SOLR_SERVER=$(SOLR_SERVER)" ; \
	LETTRE=$(LETTRE) ; \
	if [ "$$LETTRE" = "" ] ; then \
		read -p "Choisissez une lettre majuscule ou * pour tout indexer : " LETTRE ; \
	fi ; \
	GLOB=src/$$LETTRE.xml ; \
	TMP=solr/post ; \
	mkdir -p $$TMP ; \
	for F in $$GLOB ; do \
		NAME=`basename $$F` ; \
		echo "$$NAME, début de la transformation" ; \
		xsltproc -o "$$TMP/$$NAME" "transform/ducange_solr.xsl" "$$F" ; \
		echo "$$NAME, début de l'indexation" ; \
		curl $(SOLR_SERVER)/update?commit=true --data-binary @$$TMP/$$NAME -H 'Content-type:text/xml; charset=utf-8' ; \
		echo ; \
	done ; \
	curl $(SOLR_SERVER)/update --data-binary '<optimize/>' -H 'Content-type:text/xml; charset=utf-8'
*/



?>
