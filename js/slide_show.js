/* $Id: slideshow.js,v 1.1 2002/10/02 18:49:42 shaggy Exp $ */
 
/*
Copyright (c) 2001, 2002 by Martin Tsachev. All rights reserved.
http://www.mt-dev.com
 
This script uses JavaScript library functions by Peter-Paul Koch
http://www.xs4all.nl/~ppk/
 
Redistribution and use in source and binary forms,
with or without modification, are permitted provided
that the conditions available at
http://www.opensource.org/licenses/bsd-license.html
are met.
*/
 
var layers = new Array();
var slideHeight = new Array();
var slideWidth = new Array();
 
var fade = false;
var curImg = 0; // index of the array entry
var lastImg = 0;
var firstFade = false;
 
var clipTop, clipWidth, clipBottom;
var clipHeight, middle;
var delay = 5;
var diff = 15;
var timer = null;
var init = false;
var DHTML = (document.getElementById || document.all || document.layers);
var slides;
 
function init_layers(mes_slides) {
  slides = mes_slides;
 if (!DHTML) {
  alert('Your browser is not DHTML capable');
  return;
 }
 for (i = 0; i < slides.length; i++) {
  layers[i] = new getObj(slides[i]);
 
  if (document.layers) {
   height = layers[i].style.clip.bottom;
   width = layers[i].style.clip.right;
  } else
  if (document.getElementById || document.all) {
   height = layers[i].obj.offsetHeight;
   width = layers[i].obj.offsetWidth;
  }
  slideHeight[i] = height;
  slideWidth[i] = width;
 }
 
 prepLyr(0, true);
 clip = layers[0].style.clip;
 if (clip == 'rect()' || !clip) {
  canFade = false;
 } else {
  canFade = true;
 }
 init = true;
}
 
// getObj function by Peter-Paul Koch, http://www.xs4all.nl/~ppk/
function getObj(name) {
 if (document.getElementById) {
  this.obj = document.getElementById(name);
  this.style = document.getElementById(name).style;
 } else
 if (document.all) {
  this.obj = document.all[name];
  this.style = document.all[name].style;
 } else
 if (document.layers) {
  this.obj = document.layers[name];
  this.style = document.layers[name];
 }
}
 
function visib(objName, flag) {
 x = new getObj(objName);
 x.style.visibility = (flag) ? 'visible' : 'hidden';
}
 
function setFade(switchFade) {// Fade switch function
 if (switchFade) {
  if (!canFade) {
   alert('Your browser does not support fading');
   return;
  }
  prepLyr(curImg, true);
 } else { // No fade
  if (timer) clearTimeout(timer);
 
  for (var i = 0; i < slides.length; i++) {
   prepLyr(i, true);
   if (slides[i] != slides[curImg])
    layers[i].style.visibility = 'hidden';
  }
 }
 fade = switchFade;
}
 
function changeSlide(change) {
 if (!init) {
  alert('Wait for the page to load');
  return;
 }
 if (!DHTML) return;
 
 curImg += change;
 if (curImg < 0) curImg = slides.length-1;
 else
 if (curImg >= slides.length) curImg = 0;
 
 if (fade) {
  firstFade = true;
  prepLyr(lastImg, true);
  fadeLayer(lastImg, diff);
 } else {
  layers[lastImg].style.visibility = 'hidden';
  layers[curImg].style.visibility = 'visible';
 }
 lastImg = curImg;
}
 
function prepLyr(layer, vis) {
 if (!DHTML) return;
 
 x = layers[layer];
 clipHeight = slideHeight[layer];
 clipWidth = slideWidth[layer];
 middle = Math.round(clipHeight/2);
 
 if (document.layers) {
  if (vis) {
   clipTop = 0;
   clipBottom = clipHeight;
  } else {
   clipBottom = middle;
   clipTop = middle;
  }
 
  x.style.clip.top = clipTop;
  x.style.clip.left = 0;
  x.style.clip.right = clipWidth;
  x.style.clip.bottom = clipBottom;
  x.style.visibility = 'show';
 } else
 
 if (document.getElementById || document.all) {
  if (vis) {
   clipTop = 0;
   clipBottom = clipHeight;
  } else {
   clipTop = middle;
   clipBottom = middle;
  }
  x.style.clip = 'rect('+clipTop+' '+clipWidth+' '+ clipBottom +' 0)';
  x.style.visibility = 'visible';
 }
}
 
function fadeLayer(layer, diff) {
 curLayer = layers[layer];
 realFade(diff);
}
 
function realFade(diff) {
 clipTop += diff;
 clipBottom -= diff;
 if (clipTop < 0 || clipBottom > clipHeight || clipTop > middle) {
  if (clipTop > middle) curLayer.style.visibility = 'hidden';
  if (firstFade) {
   firstFade = false;
   prepLyr(curImg, false);
   fadeLayer(curImg, -diff);
  }
  return;
 }
 
 if (document.getElementById || document.all) {
  clipstring = 'rect('+clipTop+' '+clipWidth+' '+clipBottom+' 0)'
  curLayer.style.clip = clipstring;
 }
 else
 if (document.layers) {
  curLayer.style.clip.top = clipTop;
  curLayer.style.clip.bottom = clipBottom;
 }
 timer = setTimeout('realFade(' + diff + ')', delay);
}
