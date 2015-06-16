/**
<h1>Cookie, helpers and examples</h1>

<p>Cookies are a light way to record some persistance on client side.</p>

*/
var Cookie = {
  /**
   * Set a cookie
   *
   * @param name  ! the name of the cookie
   * @param value ! the value of the cookie
   * @param days  ? duration of the cookie
   */
  set: function(name, value, days) {
    if (days) {
      var date = new Date();
      date.setTime(date.getTime()+(days*24*60*60*1000));
      var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+";"; // ? utile ? path=/";
  },
  /**
   * Get a cookie
   */
  get: function(name, value) {
    if (document.cookie.length < 1) return "";
    i=document.cookie.indexOf(name + "=");
    if (i<0) return "";
    i=i+name.length+1;
    j=(document.cookie +";").indexOf(";",i);
    return document.cookie.substring(i,j);
  },
  /**
   * Delete a cookie
   */
  del: function (name) {
    this.set(name,"",-1);
  }
}
