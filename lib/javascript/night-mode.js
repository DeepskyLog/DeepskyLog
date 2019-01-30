// Night-Mode developed by Pedro Falcão Costa for DeepSkyLog

// Write and read cookies with the night mode status

var nightModePrefix = "night_mode.";
var nightModeClass = ".nightMode";

function setCookie(cname, cvalue) {
   document.cookie = cname + "=" + cvalue + ";path=/";
}

function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for(var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function activateNightMode() {
     $(nightModeClass).each(function() {
         var cssUrl= $(this).attr("href");
         var cssFile = cssUrl.split("/");
         var nightModeCssUrl = cssFile[0] + "/" + nightModePrefix + cssFile[1];
         $(this).attr("href", nightModeCssUrl);

     })
}

function deactivateNightMode() {
    $(nightModeClass).each(function() {
             var cssUrl= $(this).attr("href");
             var nightModeCssUrl = cssUrl.replace(nightModePrefix,'');
             console.log(nightModeCssUrl);
             $(this).attr("href", nightModeCssUrl);

         })
}


function toggleNightMode() {
    var nightMode = getCookie("nightMode");
    console.log(nightMode);

    if (nightMode=='true') {
        deactivateNightMode();
        setCookie("nightMode",false);

    } else {
        activateNightMode();
        setCookie("nightMode",true);
    }
 }


// read cookie to persist night mode and add event to night mode button
var nightMode = getCookie("nightMode");
if (nightMode=='true') {
        activateNightMode();
 }

$(function() {
     $("#nightMode").click(function() {
        toggleNightMode();
     });
})

