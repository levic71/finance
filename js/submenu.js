var ie4=document.all&&navigator.userAgent.indexOf("Opera")==-1
var ns6=document.getElementById&&!document.all
var ns4=document.layers

function showmenu(e,which)
{
    showmenu2(e, which, 250);
}

function showmenuNoClickHide(e,which, width)
{
    showmenu2(e, which, width);
	if (ie4||ns6) document.onclick=''
}

function showmenuNoClickHideWithTitle(e,which, width, title)
{
    showmenuwithtitle(e, which, width, title);
	if (ie4||ns6) document.onclick=''
}

function showmenuwithtitle(e, which, width, title)
{
	var tmp='<div class="submenu_title">'+title+'</div><div class=submenu_body>';
	tmp+=which+'</div>';
	
    showmenu2(e, tmp, width);
}

function showmenu2(e, which, width)
{
	if (ie4||ns6) document.onclick=hidemenu

    if (!document.all&&!document.getElementById&&!document.layers) return;
    
    clearhidemenu();
    
    menuobj=ie4? document.all.popmenu : ns6? document.getElementById("popmenu") : ns4? document.popmenu : ""

   	if (document.getElementById)
   		menuobj = document.getElementById('popmenu');
   	else
   		menuobj = document.all["popmenu"];

    menuobj.thestyle=(ie4||ns6)? menuobj.style : menuobj

    menuobj.thestyle.width=width+'px';
    
    if (ie4||ns6)
        menuobj.innerHTML=which
    else
    {
        menuobj.document.write('<layer name=gui bgColor=#E6E6E6 width=165 onmouseover="clearhidemenu()" onmouseout="hidemenu()">'+which+'</layer>')
        menuobj.document.close()
    }
    
    menuobj.contentwidth=(ie4||ns6)? menuobj.offsetWidth : menuobj.document.gui.document.width
    menuobj.contentheight=(ie4||ns6)? menuobj.offsetHeight : menuobj.document.gui.document.height
    eventX=ie4? event.clientX : ns6? e.clientX : e.x
    eventY=ie4? event.clientY : ns6? e.clientY : e.y
    
    //Find out how close the mouse is to the corner of the window
    var rightedge=ie4? document.body.clientWidth-eventX : window.innerWidth-eventX
    var bottomedge=ie4? document.body.clientHeight-eventY : window.innerHeight-eventY
    
    //if the horizontal distance isn't enough to accomodate the width of the context menu
    if (rightedge<menuobj.contentwidth)
        left_value=ie4? document.body.scrollLeft+eventX-menuobj.contentwidth : ns6? window.pageXOffset+eventX-menuobj.contentwidth : eventX-menuobj.contentwidth
    else
        left_value=ie4? document.body.scrollLeft+eventX : ns6? window.pageXOffset+eventX : eventX

	menuobj.thestyle.left=(left_value < 0 ? 10 : left_value)+'px';
    
    //same concept with the vertical position
    if (bottomedge<menuobj.contentheight)
        top_value=ie4? document.body.scrollTop+eventY-menuobj.contentheight : ns6? window.pageYOffset+eventY-menuobj.contentheight : eventY-menuobj.contentheight
    else
        top_value=ie4? document.body.scrollTop+event.clientY : ns6? window.pageYOffset+eventY : eventY
    
	menuobj.thestyle.visibility="visible"
	
	menuobj.thestyle.top=(top_value < 0 ? 10 : top_value)+'px';
	
    return false
}

function contains_ns6(a, b)
{
    if (b == null) return false;
    
    //Determines if 1 element in contained in another- by Brainjar.com
    while (b.parentNode)
        if ((b = b.parentNode) == a)
            return true;
    
    return false;
}

function hidemenu()
{
    if (window.menuobj)
        menuobj.thestyle.visibility=(ie4||ns6)? "hidden" : "hide"
}

function dynamichide(e)
{
    if (ie4&&!menuobj.contains(e.toElement))
        hidemenu()
    else if (ns6&&e.currentTarget!= e.relatedTarget&& !contains_ns6(e.currentTarget, e.relatedTarget))
        hidemenu()
}

function delayhidemenu()
{
    if (ie4||ns6||ns4)
        delayhide=setTimeout("hidemenu()",500)
}

function clearhidemenu()
{
if (window.delayhide)
    clearTimeout(delayhide)
}

function highlightmenu(e,state)
{
    if (document.all)
        source_el=event.srcElement
    else if (document.getElementById)
        source_el=e.target
    if (source_el.className=="menuitems")
    {
        source_el.id=(state=="on")? "mouseoverstyle" : ""
    }
    else
    {
        while(source_el.id!="popmenu")
        {
            source_el=document.getElementById? source_el.parentNode : source_el.parentElement
            if (source_el.className=="menuitems")
            {
                source_el.id=(state=="on")? "mouseoverstyle" : ""
            }
        }
    }
}

document.writeln('<LINK REL=\"stylesheet\" HREF=\"../css/submenu.css\" TYPE=\"text/css\">');
