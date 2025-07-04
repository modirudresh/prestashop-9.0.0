$(document).ready(function(){
  $("#cookielaw").addClass('cookielaw-showed');
  $("#cookielaw-accept").click(function (event) {
    event.preventDefault();
    $("#cookielaw").removeClass('cookielaw-showed');
    setcookielaw();
  });
});

function setcookielaw() {
  var name = 'has_cookielaw';
  var value = '1';
  var expire = new Date();
  expire.setMonth(expire.getMonth()+12);
  document.cookie = name + "=" + escape(value) +";path=/;" + ((expire==null)?"" : ("; expires=" + expire.toGMTString()))
}
