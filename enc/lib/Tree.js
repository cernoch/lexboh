
/**
<h1>Tree, automatic table of contents and other tools for clickable trees</h1>

© 2010, <a href="http://www.enc.sorbonne.fr/">École nationale des chartes</a>,
<a href="http://www.cecill.info/licences/Licence_CeCILL-C_V1-fr.html">licence CeCILL-C</a>
(LGPL compatible droit français)

<ul>
	<li>2009–2011 [FG] <a onmouseover="this.href='mailto'+'\x3A'+'frederic.glorieux'+'\x40'+'enc.sorbonne.fr'">Frédéric Glorieux</a></li>
</ul>

<p>
Manage show/hide trees. No dependancies to a javascript library.
The HTML structure handled is a multilevel list with nested ul in li.
Relies the more as possible on CSS, and toggles between
classes, especially to display groups of items (ul).
<b>li.plus</b> means there is more items inside (ul inside are hidden).
<b>li.minus</b> means it's possible to hide items inside.
First usecase is to generate an autotoc on the header hierarchy of HTML page.
All the events on such trees are also available for user written table of contents.
The method Tree.load() (to use on an body.onload() event) may be used to to add events
to all li.plus and li.minus.
</p>

<pre>
<body>

	<h1>Title of page</h1>
	...
	<h2 id="header_id">1) header</h2>
	...
	<h2>2) header</h2>
	...

	<div id="nav ">
		<-- Generated by javascript -->
		<ul class="tree">
			<li class="plus"><a href="#h_1">Title of page</a>
				<ul>
					<li><a href="#header_id">1) header</a></li>
					<li><a href="#h_3">2) header</a></li>
				</ul>
			</li>
		</ul>
	</div>
	<!-- all li.plus and li.minus will become clickable -->
	<script src="../diple/js/Tree.js">//</script>
</pre>
Sample CSS to take advantage
<pre>
ul.tree {
	padding:0 0 0 2ex;
	margin:0;
	list-style:none;
	font-size:12px;
	font-family:Arial, sans-serif;
	line-height:105%;
}
ul.tree ul {
	list-style-type:none;
	padding:0 !important;
	margin:2px 0 2px 0 !important;
}
ul.tree li {
	margin:2px 0 2px 0;
	background-repeat: no-repeat;
	background-position:0px 1px;
	list-style-image:none !important;
}
ul.tree li {
	padding-left: 12px !important;
}
ul.tree li.plus {
	background-image:url('img/plus.png');
}
ul.tree li.minus {
	background-image:url('img/minus.png');
}
ul.tree li.minus ul {
	display:block;
}
ul.tree li.plus ul {
	display:none;
}
ul.tree a.here {
	background-color:#FFFFFF;
	padding:0 1ex;
}
</pre>


 */
var Tree = {

	/** default class name for root ul */
	TREE: "tree",
	/** default class name for li with hidden list */
	MORE: "plus",
	/** default class name for li with visible list */
	LESS: "minus",
	/** default class name for clicked link */
	HERE: "here",
	/** default id where to put the toc */
	TOC: "toc",
	/** default id where to fin titles */
	ARTICLE: "article",

	/**
	 * Ititialisation of the object
	 */
	ini: function() {
		if (Tree.reLessmore) return;
		Tree.reLessmore=new RegExp(" *("+Tree.LESS+"|"+Tree.MORE+") *", "gi");
		Tree.reHere=new RegExp(" *("+Tree.HERE+") *", "gi");
	},

	/**
	 * Autotoc on h1, h2...
	 * This recursive function is quite tricky, be careful when change a line
	 *
	 * nav : element (or identifier) where to put the toc
	 * doc : element (or identifier) where to grap hierarchical titles
	 */
	create: function(nav, article) {
		Tree.ini();
		// en onload, FF passe l'événement
		if( nav && nav.stopPropagation) {
			nav=null;
			article=null;
		}
		// if nothing provide, try default
		if (!nav) nav=document.getElementById(Tree.TOC);
		if (!article) article=document.getElementById(Tree.ARTICLE);
		// nothing to do, go out
		if(!nav) return;
		// seems ids
		if (nav.nodeType != 1) nav=document.getElementById(nav);
		if(!nav) return;
		if (article && article.nodeType != 1) article=document.getElementById(article);
		if(!article) article=document.getElementsByTagName("body")[0];

		// Get the list of headers <h>
		var hList = Tree.getElementsByTagRE(/\bH[1-9]\b/i, article);
		// if very few headers, go out
		if(hList.length < 3) return;
		// create the root element, and be sur to keep a hand on it
		if (document.createElementNS) var tree = document.createElementNS("http://www.w3.org/1999/xhtml", 'div');
		else var tree = document.createElement('div');
		tree.className="autotoc "+Tree.TREE;
		// the current list to which append items
		var ul=tree;
		// current item
		var li;
		// current item
		var level=1;
		// number of level visible
		var level_plus=0;
		// loop on header
		for(var i=0; i < hList.length; ++i) {
			// the link
			if (document.createElementNS) var a = document.createElementNS("http://www.w3.org/1999/xhtml", 'a');
			else var a = document.createElement('a');
			// take id of header if available
			var id=hList[i].id;
			// if not, build an automatic id
			if (!id) {
				id="h_" + i;
				hList[i].id=id;
			}
			a.href="#"+id;
			// just the text of the header (without tags)
			if (hList[i].textContent) {
				a.textContent=hList[i].textContent;
			} else if (hList[i].innerText) {
				a.innerText=hList[i].innerText;
			}
			// Now build ul/li according to the level of the title
			var hn=Number(hList[i].nodeName.substring(1));
			// current level deeper than last one, inser a list in last item
			if (hn > level) {
				if (document.createElementNS) ul=document.createElementNS("http://www.w3.org/1999/xhtml", 'ul');
				else ul = document.createElement('ul');
				// deeper than visible levels
				if (li && level > level_plus) li.className+=" " + Tree.MORE;
				// append list to last item
				if (li) li.appendChild(ul);
			}
			// current level higher than last one, catch the relevant list where to append item
			else if (hn < level) {
				for (var j=level - hn ; j> 0 ; j--) {
					if (ul && ul.parentNode && ul.parentNode.parentNode && ul.parentNode.parentNode.nodeName.toLowerCase() == "ul")
						ul=ul.parentNode.parentNode;
					else break;
				}
			}
			// changer le niveau courant
			level=hn;
			// container is a div, probably root h1
			if (ul.nodeName.toLowerCase() == 'div') {
				if (document.createElementNS) li=document.createElementNS("http://www.w3.org/1999/xhtml", 'header');
				else li=document.createElement('header');
				// append link
				li.appendChild(a);
				// append current item in the right list level
				ul.appendChild(li);
				// add children list as sibling (not child)
				li=ul;
				continue;
			}

			// create current item
			if (document.createElementNS) li=document.createElementNS("http://www.w3.org/1999/xhtml", 'li');
			else li=document.createElement('li');
			// add an event on all li to have a selected class effect
			li.onclick = function(e) {return Tree.click(this, e);}
			// set a class for header level
			li.className=li.className+" "+hList[i].nodeName.toLowerCase();
			// append link
			li.appendChild(a);
			// append current item in the right list level
			ul.appendChild(li);
		}
		// Attach the tree
		nav.appendChild(tree);
	},

	/** Last li clicked */
	here:"",
	/**
	 * Put some events on a tree list
	 */
	loaded:false,
	load: function(id, href) {
		Tree.ini(); // compile Regexp
		// page address without ?query= or #hash
		if (!href) href=location.protocol + "//"+location.host+location.pathname;
		// on FF and onload, test if root is not an event
		// instanceof do not work on IE
		if( id && id.stopPropagation) {
			id=null;
		}
		root=id;
		if (id && typeof id == 'string') root=document.getElementById(id);
		if (!root && document) root=document.documentElement;
		// case seems sometimes significant
		var reHereDel=new RegExp(' *'+Tree.HERE+' *');
		var nodeset = root.getElementsByTagName("li");
		for(var i=0; i < nodeset.length; ++i) {
			li=nodeset[i];
			li.onclick = function(e) {return Tree.click(this, e);}
			// hilite current link in this item
			var links=li.getElementsByTagName("a");
			for(var j=0; j < links.length; j++) {
				a=links[j];
				// avoid .../n2 = .../n20
				// alert(a.href.replace(/\/$/, '')+'/'+' '+href.indexOf(a.href.replace(/\/$/, '')+'/'));
				if (
					href.indexOf(a.href.replace(/\/$/, '')+'/') !== 0
					&& (a.href+'/').indexOf(href+'/') !== 0
				) continue;
				// open parents
				Tree.open(li);
                // class on li or a ?
				if (a.className.indexOf(Tree.HERE) != -1) a.className=a.className.replace(reHereDel, '');
				a.className += " "+Tree.HERE;
				// bout de branche
				if (li.getElementsByTagName("li").length==0) {
					if (li.className.indexOf(Tree.HERE) != -1) li.className=li.className.replace(reHereDel, '');
					li.className += " "+Tree.HERE;
				}
			}
		}
		Tree.loaded=true;
	},


	/**
	 * Change ClassName of a <li> onclick to open/close
	 */
	click: function (li, e) {
		Tree.ini(); // useful for lists with inline event onclick to have REgExp
		if(Tree.className != null) li=this;
		if (!li) return false;
		var ret=false;
		var className="";
		if(li.className.indexOf(Tree.LESS) > -1) className=" "+Tree.MORE;
		else if(li.className.indexOf(Tree.MORE) > -1) className=" "+Tree.LESS;
		// if click in a link, force open
		var ev = e || event, src = ev.target || ev.srcElement;
		while (src && src.tagName.toLowerCase() != "li") {
			if (src.tagName.toLowerCase() == 'a') {
				className=" "+Tree.LESS;
				ret=true;
				// hilite the clicked item
				if (Tree.a) Tree.a.className=Tree.a.className.replace(Tree.reHere, ' ');
				src.className=src.className.replace(Tree.reHere, ' ') + " "+Tree.HERE;
				Tree.a=src;
				break;
			}
			src = src.parentNode;
		}
		// change class only if already less or more
		if (li.className.search(Tree.reLessmore)>-1) li.className=li.className.replace(Tree.reLessmore, ' ') + className;
		// stop propagation
		if (ev && document.all ) ev.cancelBubble = true;
		if (ev && ev.stopPropagation) ev.stopPropagation();
		return ret;
	},
	/**
	 * Recursively open li ancestors
	 */
	open: function () {
		var li; // don't forget or may produce some strange var collapse
		for (i=arguments.length - 1; i>=0; i--) {
			li=arguments[i];
			if (li.className == null) li=document.getElementById(arguments[i]);
			if (!li) continue;
			while (li && li.tagName.toLowerCase() == 'li') {
				// avoid icon in front of single item
				if (li.className.match(Tree.reLessmore) || li.getElementsByTagName('UL').length > 0)
					li.className = li.className.replace(Tree.reLessmore, ' ') +" "+Tree.LESS;
				li=li.parentNode.parentNode; // get a possible li ancestor (jump an ul container)
			}
		}
	},
	/**
	 * Get an array of elements matching a regular expression,
	 * essentially used to get h[1-6]
	 *
	 * @param node the node from where to search
	 * @param re   a regular expression to compile
	 * @returns	an array of matching nodes
	 */
	getElementsByTagRE: function(re, node) {
		if(!node) node=document.getElementsByTagName("BODY")[0];
		// array of nodes to filter
		var nodeset = node.getElementsByTagName("*");
		if(!re || re == "*") return nodeset;
		if(!re.test) re = new RegExp(re);
		re.ignoreCase = true;
		// array to return
		var select = [];
		for(i = 0; i < nodeset.length; ++i)
			if( re.test(nodeset[i].nodeName.toLowerCase()) ) select.push( nodeset[i] );
		return select;
	},
	props: function(o) {
		tmp='';
		for (x in o) tmp += x + "  " ;// ": " + o[x] + "\n";
		alert (tmp);
	}
}

// if loaded as bottom script, create trees ?
if(window.document.body) {
	// Tree.create("toc");
	// Tree.load("nav");
}
else {
	if (window.addEventListener) {
		if (!Tree.loaded) window.addEventListener('load', Tree.load, false);
		window.addEventListener('load', Tree.create, false);
	}
	else if (window.attachEvent) {
		if (!Tree.loaded) window.attachEvent('onload', Tree.load);
		window.attachEvent('onload', Tree.create);
	}
}
