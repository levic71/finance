// Based on http://vis.stanford.edu/protovis/ex/clock.html
// Based on http://blog.pixelbreaker.com/polarclock

var width = 350,
    height = 350,
    radius = Math.min(width, height) / 1.3,
    sectorWidth = 0.09;

var fill = d3.scale.linear()
    .range(["hsl(-180, 50%, 50%)", "hsl(180, 50%, 50%)"])
    .interpolate(d3.interpolateString);

var arc = d3.svg.arc()
    .startAngle(function(d) { return d.start * 2 * Math.PI; })
    .endAngle(function(d) { return d.value * 2 * Math.PI; })
    .innerRadius(function(d) { return d.index * radius; })
    .outerRadius(function(d) { return (d.index + sectorWidth) * radius; });

var vis = d3.select("#clock").append("svg")
    .attr("width", width)
    .attr("height", height)
    .append("g")
    .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");

vis.append("svg:line")
	.attr("x1", radius * -1)
	.attr("y1", 0)
	.attr("x2", radius)
	.attr("y2", 0)
	.style("stroke", "#000")
	.style("stroke-width", 0.5);

vis.append("svg:line")
	.attr("x1", 0)
	.attr("y1", radius * -1)
	.attr("x2", 0)
	.attr("y2", radius)
	.style("stroke", "#000")
	.style("stroke-width", 0.5);

vis.append("svg:line")
	.attr("x1", radius * -.46)
	.attr("y1", radius * -.46)
	.attr("x2", radius * .46)
	.attr("y2", radius * .46)
	.style("stroke", "#000")
	.style("stroke-width", 0.5);

vis.append("svg:line")
	.attr("x1", radius * .46)
	.attr("y1", radius * -.46)
	.attr("x2", radius * -.46)
	.attr("y2", radius * .46)
	.style("stroke", "#000")
	.style("stroke-width", 0.5);

var g = vis.selectAll("g")
    .data(fields)
    .enter().append("g");

g.append("path")
    .style("fill", function(d) { return (d.color == "" ? fill(d.value) : d.color); })
    .attr("d", arc);

g.append("text")
    .attr("text-anchor", "middle")
    .attr("dy", "1em")
    .attr("fill", function(d) { return d.tcolor; })
    .attr("transform", function(d) {
	        return "rotate(" + 360 * d.value + ")"
	            + "translate(0," + -(d.index + sectorWidth / 2) * radius + ")"
	            + "rotate(" + 90 + ")"
	      })
    .text(function(d) { return d.text; });


var defs = vis.append("svg:defs");

defs.append("svg:clipPath")
	.attr("id", "circle1")
	.append("svg:circle")
    .attr("cx", 0)
    .attr("cy", 0)
    .attr("r", 65);


vis.append("svg:circle")
	.attr("cx", 0)
	.attr("cy", 0)
    .attr("r", 65)
    .attr("fill", "#fff")
	.style("stroke", "#ccc")
	.style("stroke-width", 0.5);

var img_size = 160;
vis.append("image")
    .attr("xlink:href", "user-img.png")
    .attr("x", Math.round(img_size/2) * -1)
    .attr("y", Math.round(img_size/2) * -1)
    .attr("class", "img-icon")
    .attr("clip-path", "url(#circle1)")
    .attr("width", img_size)
    .attr("height", img_size);


// Generate the fields for the current date/time.
function fields() {

  return [
    {start: 0.0, value: 0.8, index: .55, text: "aa", tcolor: "#ffffff", color: "#A7A9AC"},
    {start: 0.8, value: 1.0, index: .55, text: "",   tcolor: "#ffffff", color: "#eeeeee"},
    {start: 0.0, value: 0.2, index: .45, text: "cc", tcolor: "#ffffff", color: "#00AEEF"},
    {start: 0.2, value: 1.0, index: .45, text: "",   tcolor: "#ffffff", color: "#eeeeee"},
    {start: 0.0, value: 0.4, index: .35, text: "ee", tcolor: "#ffffff", color: "#EC008C"},
    {start: 0.4, value: 1.0, index: .35, text: "",   tcolor: "#ffffff", color: "#eeeeee"},
    {start: 0.0, value: 0.9, index: .25, text: "ii", tcolor: "#ffffff", color: "#00AEEF"},
    {start: 0.9, value: 1.0, index: .25, text: "",   tcolor: "#ffffff", color: "#eeeeee"},
  ];
}

var canvas = document.getElementById('clock');
var context = canvas.getContext('2d');
var imageObj = new Image();

imageObj.onload = function() {
	context.drawImage(imageObj, Math.round(width/2), Math.round(height/2), 200, 200);
};
imageObj.src = 'user-img.png';



