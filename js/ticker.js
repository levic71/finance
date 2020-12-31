// Use Freely as long as following disclaimer is intact ::
//---------------------------------------------------------------
// Cross Browser Multi-Directional Scroller v1.0 24th August 2004
// This script written by Rik Comery. www.ricom.co.uk
// For support, visit the "Resources" section at www.ricom.co.uk
// All rights reserved.
// Featured in the SimplytheBest DHTML Scripts Library at http://simplythebest.net/scripts/

////// SCROLLER CONFIGURATION ///////////////////////////////////////////////////////////////////

orientation = "horizontal"        // Orientation of scroller (Horizontal or vertical)
scrollerWidth = "700"             // Width of entire scroller.
scrollerHeight = "25"             // Height of Scroller.
borderWidth = 1                   // Width of border. (use 0 for no border).
borderColour = "#006600"          // Colour of scroller border. (Use either hexadecimal or text values. e.g. "#FF0000" or "Red")
backColour = "#d7ebff"              // Colour of scroller background. (Use either hexadecimal or text values. e.g. "#FF0000" or "Red")
staticColour = "#000000"          // Colour of scroller items that are NOT a link. (including scrollerDivider characters)
stopScroll = 1                    // Pause the scroller on mouseOver. (use 0 for no.)
textAlignment="left"            // Alignment of each scroller item.  Only really makes a difference on vertical scroller
                                  // (center, left, right, justify)

// Scroller Links
linkFont = "arial"                // Font of scroller links;
linkWeight = "normal"             // Weight of scroller links;
linkColour = "#000080"            // Colour of scroller links
linkSize = "10"                   // Size of links (in points)
linkDecoration = "none"           // Decoration of links. (underline, overline, none)

// Scroller Links On MouseOver
slinkFont = "arial"               // Font of scroller links;
slinkWeight = "normal"            // Weight of scroller links;
slinkColour = "#cc3333"           // Colour of scroller links
slinkSize = "10"                  // Size of links (in points)
slinkDecoration = "underline"     // Decoration of links. (underline, overline, none)

scrollerDivider = "&nbsp;-&nbsp;" // Character to place between each scroller item.
                                  // <img> tags can be used if an image is required.
                                  // Use "0" for nothing.  For Vertical scrollers, it is best to use "<br>"

/*
  Because Netscape version 4 does not support this scroller, configure the values below so NS4 users do not see an error.
  In the scroller's place will be a static title which can be a link if require.  The appearance of the static text (colour, scroller border etc)
  will be exactly the same as for the working version in other browsers.
*/
ns4Text = "Click Here to visit the PlanMagic Software";  // Alternative text to display in Netscape 4.
ns4URL = "http://planmagic.com";            // URL of link in NS4. If no URL is required, enter "none"
ns4Target = "_top";                           // Frame target for link in NS4

////// DO NOT EDIT BELOW THIS LINE  ///////////////////////////////////////////////////////////////////

//Browser Sniffer
var isIE = (document.getElementById && document.all)?true:false;
var isNS4 = (document.layers)?true:false;
var isNS6 = (document.getElementById && !document.all)?true:false;

style='<style type="text/css">';
style+='p{font-family:'+linkFont+'; color:'+staticColour+'; font-size:'+linkSize+'pt; font-weight:'+linkWeight+'; text-align:'+textAlignment+'}';
style+='a.rcScroller,a.rcScroller:active,a.rcScroller:visited{color:'+linkColour+';text-decoration:'+linkDecoration+'; font-weight:'+linkWeight+'}';
style+='a.rcScroller:hover{color:'+slinkColour+';text-decoration:'+slinkDecoration+'; font-weight:'+slinkWeight+'}';
style+='rcScrollerHoverNS{color:red;text-decoration:'+slinkDecoration+'; font-weight:'+slinkWeight+'}';
style+='<\/style>';
document.writeln(style);

// Build the scroller and place it on the page
function buildScroller(Article)
{
  if(isNS4)
  {
    scroller='<table border="0" cellpadding="0" cellspacing="0" width="'+scrollerWidth+'" bgcolor="'+borderColour+'"><tr><td>'
    scroller+='<table border="0" cellpadding="3" cellspacing="1" width="100%" height="'+scrollerHeight+'" bgcolor="'+backColour+'"><tr><td align="center" nowrap><p>';
    if(ns4URL.toLowerCase()!="none"){scroller+='<a href="'+ns4URL+'" class="rcScroller" target="'+ns4Target+'">'+ns4Text+'</a>';}
    else{scroller+=ns4Text;}
    scroller+='</p></td></tr></table></td></tr></table>'
  }
  else
  {
    scroller='<table border="0" cellpadding="0" cellspacing="0" style="overflow: hidden; width:'+scrollerWidth+';height:'+scrollerHeight+';border:'+borderWidth+'px solid '+borderColour+';background-color:'+backColour+'">';
    scroller+='<tr valign="middle"><td style="overflow: hidden;"><div id="div" style="';
    if (orientation.toLowerCase()=="vertical")
    {
    	scroller+='height:'+scrollerHeight+';';
    }
    scroller+='width:'+scrollerWidth+'; position:relative; background-color:'+backColour+'; overflow:hidden;">';
    scroller+='<div id="div1" style="overflow: hidden; position:relative; left:0; z-index:1">';
    scroller+='<table border="0" name="table2" id="table" style="overflow: hidden;" ';
    if (orientation.toLowerCase()=="vertical")
    	scroller+='style="width:'+scrollerWidth+'"';

    scroller+='><tr>';
    y=0;
    while (y<4)
    {
      for (x=0; x<(Article.length); x++)
      {
        if (orientation.toLowerCase()=="vertical")
        	scroller+='<tr>';

        scroller+='<td ';
        if (orientation.toLowerCase()=="horizontal")
        	scroller+='nowrap';

        if (stopScroll==1)
        	scroller+=' onMouseOver="stopScroller();" onMouseOut="setWidth()"';

        scroller+='><p>';
        if (Article[x][1].toLowerCase()!="none")
        	scroller+='<a class="rcScroller" href="'+Article[x][1]+'" target="'+Article[x][2]+'" >'+Article[x][0]+'<\/a>';
        else
        	scroller+=Article[x][0];

        scroller+='</p><\/td>';

        if(orientation.toLowerCase()=="vertical")
			scroller+='<\/tr>';

        if(scrollerDivider.toLowerCase() != "none")
        	scroller+='<td nowrap><p>'+scrollerDivider+'<\/p><\/td>';
      }
      y++
    }
    scroller+='<\/tr><\/table><\/div><\/div><\/td><\/tr><\/table>';
  }
  document.writeln(scroller);
}
// Ensure the width of the scroller is divisible by 2. This allows smooth flowing of the scrolled content
function setWidth()
{
  tableObj=(isIE)?document.all("table"):document.getElementById("table");
  obj=(isIE)?document.all.div1:document.getElementById("div1");
  objWidth=(orientation.toLowerCase()=="horizontal")?getOffset(tableObj,"width"):getOffset(tableObj,"height");
  HalfWidth=Math.floor(objWidth/2);
  newWidth = (HalfWidth*2)+2;
  obj.style.width=newWidth
  moveLayer(obj, newWidth);
}

// Move the layer by one pixel to the left
function moveLayer(obj, width)
{

  maxLeft = (0-(width/2)+2)/2
  if(orientation.toLowerCase()=="horizontal")
  {
    obj.style.left=(parseInt(obj.style.left) <= maxLeft)?0:parseInt(obj.style.left)-1
  }
  else
  {
    if(obj.style.top==""){obj.style.top=0;}
   // alert(obj.style.top)
    if (parseInt(obj.style.top)<(0-(width/2)+6))
      obj.style.top = 0
    else
      obj.style.top = parseInt(obj.style.top)-1
  }
  timer = setTimeout ("moveLayer(obj, "+width+");", 25);
}

// Get width and height of layer
function getOffset(obj, dim)
{
  if(dim=="width")
  {
    oWidth = obj.offsetWidth
    return oWidth
  }
  else if(dim=="height")
  {
    oHeight = obj.offsetHeight
    return oHeight
  }
}

function stopScroller()
{
  clearTimeout(timer)
}

function runScroller(){
  if(!isNS4)
	setWidth()
}
