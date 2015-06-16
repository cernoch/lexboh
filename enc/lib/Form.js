/** encoding="UTF-8"
 * Singleton de méthodes statiques pour formulaires
 */
var Form = {
	/**
	 * Charge un formulaire avec les paramètres d'URI
	 * TODO, rendre plus intelligent, pour select, radio, etc
	 */
	load: function(form) {
		var param = location.search.substring(1).split("&");
		for (var i=0; i < param.length; i++) {
			var couple=param[0].split("=");
			if (form[couple[0]]) form[couple[0]].value= decodeURIComponent(couple[1].split('+').join(' '));
		}
	},

	/**
	 * Éviter qu'un formulaire renvoie trop de paramètres vide
	 */
	clean: function(form, ignore) {
		if (!form) return false;
		if (!form.elements) return true;
		ignore=" "+ignore+" ";
		for (var i=0; i < form.elements.length; i++) {
			var control=form.elements[i];
			if (!control) continue;
			// ne rien faire si dans la liste ignore
			if(control.name && ignore && ignore.indexOf(" "+control.name+" ") != -1) continue;
			// déselectionner les select (? ou valeur initiale ?)
			if(control.selectedIndex) continue;
			// bouton radio avec valeur vide
			if(control.checked && control.value) continue;
			if(control.value) continue;
			// control.disabled=true; // bug avec le bouton back
		}
		return false;
	},

	/**
	 * vider les champs d'un formulaire
	 * return true si échec pour laisser travailler le click par défaut
	 */
	reset: function (form, ignore) {
		if (!form) return true;
		if (!form.elements) return true;
		ignore = "   "+ignore+" ";
    var control;
		for (var i=0; i < form.elements.length; i++) {
			control=form.elements[i];
			// ne rien faire si dans la liste ignore
			if(ignore.indexOf(" "+control.name+" ") != -1) continue;
			// déselectionner les select (? ou valeur initiale ?)
			if(control.type.substring(0, 4) == "sele") { control.selectedIndex=-1; continue; }
			if (control.checked) { control.checked=null; continue; }
			if(control.type == "text" && control.value) { control.value=""; continue; }
			// TODO checkbox, radio
		}
		return false;
	},
	/**
	* Bascule entre présence absence d'une classe
	*/
	toggle : function (o, className, toggle) {
		if (!o) o=this;
		if(!o) return false;
		if(!className) className=o.toggle;
		if(!className) className="hi";
		pattern=new RegExp("\\s*"+ className,"gim");
		if (toggle) return o.className=(o.className.replace(pattern, "") + " "+className);
		if(toggle == false) return o.className=(o.className.replace(pattern, ""));
		if (!o.className || o.className.search(pattern) < 0 ) return o.className = (o.className + " "+className);
		else return o.className=(o.className.replace(pattern, ""));
	},

	/**
	 * Ajoute une classe aux boutons radio qui sont "checked"
	 */
	classChecked : function (o, className, attach) {
		if (!className) className="checked";
		var forms=new Array();
		if (o) forms=[o];
		else forms=document.forms;
		for (var j=forms.length - 1; j > -1 ; j--) {
			form=forms[j];
			for (var i=form.elements.length - 1; i > -1 ; i--) {
				var control=form.elements[i];
				if(control.type != "radio" && control.type.substring(0, 4) != "check" ) continue;
				Form.toggle(control, className, control.checked);
				if (control.parentNode != form) Form.toggle(control.parentNode, className, control.checked);
				if(o) continue;
				// pas génial ici, chaque click sur un bouton radio va exécuter cette même fonction qui boucle sur les radios
				var fn=function () {Form.classChecked(this.form, className);};
				if (control.addEventListener) control.addEventListener("click", fn, false);
				else if (control.attachEvent) control.attachEvent("onclick", fn);

			}
		}
	}
}
