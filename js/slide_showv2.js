var curImg = 0; // index of the array entry
var lastImg = 0;
 
var DHTML = (document.getElementById || document.all || document.layers);
var slides;
 
function init_layers(mes_slides)
{
	slides = mes_slides;
	init = true;
}
 
// getObj function by Peter-Paul Koch, http://www.xs4all.nl/~ppk/
function getObj(name)
{
	if (document.getElementById)
	{
		this.obj = document.getElementById(name);
		this.style = document.getElementById(name).style;
	}
	else if (document.all)
	{
		this.obj = document.all[name];
		this.style = document.all[name].style;
	}
	else if (document.layers)
	{
		this.obj = document.layers[name];
		this.style = document.layers[name];
	}
}
 
function visib(objName, flag)
{
	x = new getObj(objName);
	x.style.visibility = (flag) ? 'visible' : 'hidden';
	x.style.display = (flag) ? 'block' : 'none';
}
 
function changeSlide(change)
{
	if (!init)
	{
		alert('Wait for the page to load');
		return;
	}
	if (!DHTML) return;
 
	curImg += change;
	if (curImg < 0) curImg = slides.length-1;
	else if (curImg >= slides.length) curImg = 0;

	visib(slides[lastImg], false);
	visib(slides[curImg], true);

	lastImg = curImg;
}
