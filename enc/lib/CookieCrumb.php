<?php // encoding="UTF-8"
/**
<h1>CookieCrumb, little helper to keep navigation history in cookie</h1>

© 2010–2012, <a href="http://www.enc.sorbonne.fr/">École nationale des chartes</a>, <a href="http://www.cecill.info/licences/Licence_CeCILL-C_V1-fr.html">licence CeCILL-C</a> (LGPL compatible droit français)

2009–2012 [FG] <a onclick="this.href='mailto'+'\x3A'+'frederic.glorieux'+'\x40'+'fictif.org'">Frédéric Glorieux</a>


 */
class CookieCrumb {
	/** key */
	public $name;
	/** Array of links */
	public $history;
	/** Max size of the LIFO stack */
	public $max=20;
	/** durée de vie, une semaine */
	public $expire;

	/**
	 * Constructor, initialize
	 * @param $name : cookie name
	 */
	public function __construct($name, $max=15, $expire=FALSE) {
	if($max) $this->max=$max;
		if (!$expire) $expire=time()+60*60*24*30;
		$this->expire=$expire;
		$this->name=$name;
		$this->load();
	}

	/**
	 * Load the cookie
	 */
	public function load() {
		if (!isset($_COOKIE[$this->name])) {
			$this->history=array();
			return false;
		}
		$var=$_COOKIE[$this->name];
		if (get_magic_quotes_gpc()) $var = stripslashes($var);
		$var=unserialize($var);
		// ne pas charger une valeur corrompue
		if ($var) $this->history=$var;
		if (!$this->history) $this->history=array();
		return $var;
	}
	/**
	 * Save the cookie
	 */
	public function save() {
		setcookie($this->name, serialize($this->history), $this->expire);
	}
	/**
	 * Add an item to history
	 * @param $html an html link to save
	 * @param $key an optional key allowing sorting and unicity
	 */
	public function add($html, $key=null) {
		// implement the LIF0 stack
		if (count($this->history) >= $this->max) array_shift($this->history);
		if ($key) {
			if (isset($this->history[$key])) unset($this->history[$key]);
			$this->history[$key]=$html;
		}
		else $this->history[]=$html;
		$this->save();
	}
	/**
	 * tester si une clé est présente
	 */
	public function contains($key) {
		return isset($this->history[$key]);
	}
	/**
	 * Supprimer un item.
	 */
	public function del($key) {
		if (isset($this->history[$key])) unset($this->history[$key]);
		$this->save();
	}
	/**
	 * Empty History
	 */
	public function reset() {
		$this->history=array();
		setcookie($this->name);
	}
	/**
	 * Display history as ul/li structure.
	 * $sort : optional, default = false = time order , true = key order
	 */
	public function ul( $sort=false ) {
		if (!count($this->history)) return null;
		$keys=array_keys($this->history);
		if ($sort) natcasesort($keys);
		$ul[]="\n".'<ul class="'.__CLASS__.'">';
		$count=count($keys);
		foreach ($keys as $k) {
			$count--;
			$dot=($count)?" ; ":". ";
			$ul[]="\n<li>".$this->history[$k].$dot."</li>";
		}
		$ul[]="\n</ul>";
		return implode($ul);
	}
	public function div( $sort=false ) {
		if (!count($this->history)) return null;
		$keys=array_keys($this->history);
		if ($sort) natcasesort($keys);
		$div[]='<div class="'.__CLASS__.'">';
		foreach ($keys as $k) $div[]=$this->history[$k];
		$div[]="</div>";
		return implode("\n",$div);
	}
}
?>
