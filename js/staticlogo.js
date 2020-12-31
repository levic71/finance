///////////////////////////////////////////////////////////////////
/*Site Logo Script (Geocities Watermark)
© Dynamic Drive (www.dynamicdrive.com)
For full source code, installation instructions,
100's more DHTML scripts, and TOS, visit http://www.dynamicdrive.com/ */
///////////////////////////////////////////////////////////////////

//edit the below 5 steps

// 1) substitute 116 and 42 with the width and height of your logo image, respectively
var logowidth=50
var logoheight=36
var logoimage=new Image(logowidth,logoheight)

// 2) change the image path to reflect the path of your logo image
logoimage.src="../images/menu/jorkyball_v2.0_gauche_main.gif"

// 3) Change url below to the target URL of the logo
var logolink="contacter.php"

// 4) change the alttext variable to reflect the text used for the "alt" attribute of the image tag
var alttext="Mail au webmaster ..."

// 5) Finally, below variable determines the duration the logo should be visible after loading, in seconds. If you'd like the logo to appear for 20 seconds, for example, enter 20. Entering a value of 0 causes the logo to be perpectually visible. 
var visibleduration=0

// Optional parameters
var Hoffset=-5 //Enter logo's offset from left edge of window (edit only if you don't like the default offset)
var Voffset=10 //Enter logo's offset from bottom edge of window (edit only if you don't like the default offset)

///////////////////////////Do not edit below this line/////////////////////////

var ie=document.all&&navigator.userAgent.indexOf("Opera")==-1

var watermark_obj=ie? document.all.watermarklogo : document.getElementById? document.getElementById("watermarklogo") : document.watermarklogo

function insertimage(){
if (ie||document.getElementById)
watermark_obj.innerHTML='<a href="'+logolink+'"><img name=main src="'+logoimage.src+'" width="'+logowidth+'" height="'+logoheight+'" border=0 alt="'+alttext+'"></a>'
else if (document.layers){
watermark_obj.document.write('<a href="'+logolink+'"><img src="'+logoimage.src+'" width="'+logowidth+'" height="'+logoheight+'" border=0 alt="'+alttext+'"></a>')
watermark_obj.document.close()
}
}

function positionit(){
var dsocleft=ie? document.body.scrollLeft : pageXOffset
var dsoctop=ie? document.body.scrollTop : pageYOffset
var window_height=ie? document.body.clientHeight : window.innerHeight

if (ie||document.getElementById){
watermark_obj.style.left=parseInt(dsocleft)+5+Hoffset
watermark_obj.style.top=parseInt(dsoctop)+parseInt(window_height)-logoheight-Voffset
}
else if (document.layers){
watermark_obj.left=dsocleft+5+Hoffset
watermark_obj.top=dsoctop+window_height-logoheight-Voffset
}
}

function hidewatermark(){
if (document.layers)
watermark_obj.visibility="hide"
else
watermark_obj.style.visibility="hidden"
clearInterval(watermarkinterval)
}

function beingwatermark(){
watermarkinterval=setInterval("positionit()",50)
insertimage()
if (visibleduration!=0)
setTimeout("hidewatermark()",visibleduration*1000)
}

if (ie||document.getElementById||document.layers)
window.onload=beingwatermark

