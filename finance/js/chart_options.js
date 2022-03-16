const insider = {
    id: 'insider',
    beforeDraw(chart, args, options) {
        const {
            ctx,
            chartArea: { top, right, bottom, left, width, height },
            scales: { x, y1 },
        } = chart;

        ctx.save();

        if (typeof options.title !== 'undefined') {

            var padding_width  = 15;
            var padding_height = 5;
            var size_font = options.size;

            ctx.font = size_font + 'px Verdana';

            var l = ctx.measureText(options.title).width;
            var h = parseInt(ctx.font, size_font);

            if (options.align == 'right') {
                ctx.fillStyle = options.bgcolr;
                ctx.fillRect(right - l - padding_width, 0, l + padding_width, h + padding_height);
                ctx.fillStyle = options.colr;
                ctx.fillText(options.title, right - l - (padding_width / 2), top + h);
             }
            else if (options.align == 'left') {
                ctx.fillStyle = options.bgcolr;
                ctx.fillRect(left, 0, l + padding_width, h + padding_height);
                ctx.fillStyle = options.colr;
                ctx.fillText(options.title, padding_width / 2, 0 + h);
             }
            else {
                ctx.fillStyle = options.bgcolr;
                ctx.fillRect(left + ((width + padding_width - l) / 2), 0, l + padding_width, h + padding_height);
                ctx.fillStyle = options.colr;
                ctx.fillText(options.title, ((width + padding_width - l) / 2 ) + padding_width / 2, 0 + h);
            }
        }

        ctx.restore();
    }
}

const horizontal = {
    id: 'horizontal',
    beforeDraw(chart, args, options) {
        const {
            ctx,
            chartArea: { top, right, bottom, left, width, height },
            scales: { x, y1 },
        } = chart;

        ctx.save();

        if (options.length > 0) {
            options.forEach(function(item) {

                if (typeof item.text !== 'undefined') {
                    var size_font = 10;
                    ctx.font = size_font + 'px Verdana';
                    var l = ctx.measureText(item.text).width;
                    var h = parseInt(ctx.font, size_font);
                    ctx.fillStyle = item.lineColor;
                    ctx.fillRect(left, y1.getPixelForValue(item.yPosition), l + 10, -h);

                    ctx.fillStyle = 'black';
                    ctx.fillText(item.text, 5, y1.getPixelForValue(item.yPosition) - 1);
                }

                ctx.strokeStyle = item.lineColor;
                ctx.strokeRect(left, y1.getPixelForValue(item.yPosition), width, 0);
            });
        }

        ctx.restore();
    }
}

const vertical = {
    id: 'vertical',
    beforeDraw(chart, args, options) {
        const {
            ctx,
            chartArea: { top, right, bottom, left, width, height },
            scales: { x, y1 },
        } = chart;

        ctx.save();

        if (options.length > 0) {
            options.forEach(function(item) {
                ctx.strokeStyle = item.lineColor;
                ctx.strokeRect(x.getPixelForValue(item.xPosition), top, 0, height);
            });
        }

        ctx.restore();
    }
}

const getOrCreateTooltip = (chart) => {
    let tooltipEl = chart.canvas.parentNode.querySelector('div');
  
    if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        tooltipEl.id = 'tooltip_stock_graphe_div';
        chart.canvas.parentNode.appendChild(tooltipEl);
    }
  
    return tooltipEl;
}

const externalTooltipHandler = (context) => {
    // Tooltip Element
    const {chart, tooltip} = context;
    const tooltipEl = getOrCreateTooltip(chart);
  
    // Hide if no tooltip
    if (tooltip.opacity === 0) {
        tooltipEl.style.opacity = 0;
        return;
    }
  
    // Set Text
    if (tooltip.body) {
        const titleLines = tooltip.title || [];
        const bodyLines = tooltip.body.map(b => b.lines);

        // Remove old children
        while (tooltipEl.firstChild) tooltipEl.firstChild.remove();

        titleLines.forEach(title => {
            const div = document.createElement('div');
            const text = document.createTextNode(title);
            div.appendChild(text);
            tooltipEl.appendChild(div);
        });
  
        bodyLines.forEach((body, i) => {
            const colors = tooltip.labelColors[i];

            var t = body[0].split(': ');

            const span = document.createElement('span');
            span.style.background = t[0] == 'VOLUME' ? colors.backgroundColor : colors.borderColor;
            span.style.borderColor = colors.borderColor;

            const div = document.createElement('div');
            div.style.backgroundColor = 'inherit';
            div.style.borderWidth = 0;

            const label1 = document.createElement('div');
            const text1 = document.createTextNode(t[0] + ' : ');
            label1.appendChild(text1);
            const label2 = document.createElement('div');
            const text2 = document.createTextNode(t[1] + (t[0] == 'VOLUME' ? ' K' : ' \u20ac'));
            label2.appendChild(text2);

            div.appendChild(span);
            div.appendChild(label1);
            div.appendChild(label2);

            tooltipEl.appendChild(div);
        });
    }
  
    const {offsetLeft: positionX, offsetTop: positionY} = chart.canvas;
  
    // Display, position, and set styles for font
    tooltipEl.style.opacity = 1;
}

const getOrCreateTooltip2 = (chart) => {
    let tooltipEl = chart.canvas.parentNode.querySelector('div');
  
    if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        tooltipEl.id = 'tooltip_stock_graphe';    
        const table = document.createElement('table');
        tooltipEl.appendChild(table);
        chart.canvas.parentNode.appendChild(tooltipEl);
    }
  
    return tooltipEl;
}

const externalTooltipHandler2 = (context) => {
    // Tooltip Element
    const {chart, tooltip} = context;
    const tooltipEl = getOrCreateTooltip2(chart);
  
    // Hide if no tooltip
    if (tooltip.opacity === 0) {
        tooltipEl.style.opacity = 0;
        return;
    }
  
    // Set Text
    if (tooltip.body) {
        const titleLines = tooltip.title || [];
        const bodyLines = tooltip.body.map(b => b.lines);

        const tableHead = document.createElement('thead');

        titleLines.forEach(title => {
            const tr = document.createElement('tr');
            const th = document.createElement('th');
            th.colSpan = 2;
            const text = document.createTextNode(title);
            th.appendChild(text);
            tr.appendChild(th);
            tableHead.appendChild(tr);
        });
  
        const tableBody = document.createElement('tbody');
        bodyLines.forEach((body, i) => {
            const colors = tooltip.labelColors[i];

            var t = body[0].split(': ');

            const span = document.createElement('span');
            span.style.background = t[0] == 'VOLUME' ? colors.backgroundColor : colors.borderColor;
            span.style.borderColor = colors.borderColor;
            span.style.borderWidth = '2px';
            span.style.marginRight = '10px';
            span.style.height = '10px';
            span.style.width = '10px';
            span.style.display = 'inline-block';

            const tr = document.createElement('tr');
            tr.style.backgroundColor = 'inherit';
            tr.style.borderWidth = 0;

            const td1 = document.createElement('td');
            td1.style.borderWidth = 0;

            const td2 = document.createElement('td');
            td2.style.textAlign = 'right';
            td2.style.borderWidth = 0;

            const text1 = document.createTextNode(t[0]);
            const text2 = document.createTextNode(t[1] + (t[0] == 'VOLUME' ? ' K' : ' \u20ac'));

            td1.appendChild(span);
            td1.appendChild(text1);
            td2.appendChild(text2);
            tr.appendChild(td1);
            tr.appendChild(td2);
            tableBody.appendChild(tr);
        });
  
        const tableRoot = tooltipEl.querySelector('table');
  
        // Remove old children
        while (tableRoot.firstChild) {
            tableRoot.firstChild.remove();
        }
  
        // Add new children
        tableRoot.appendChild(tableHead);
        tableRoot.appendChild(tableBody);
    }
  
    const {offsetLeft: positionX, offsetTop: positionY} = chart.canvas;
  
    // Display, position, and set styles for font
    tooltipEl.style.opacity = 1;
    tooltipEl.style.left = '50%';
    tooltipEl.style.top = '25px';
//    tooltipEl.style.left = positionX + tooltip.caretX + 'px';
//    tooltipEl.style.top = positionY + tooltip.caretY + 'px';
}

var options_Stock_Graphe = {
    interaction: {
		intersect: false,
        mode: 'index'
	},
	radius: 0,
	responsive: true,
	maintainAspectRatio: true,
    animation: true,
    plugins: {
        horizontal: {
            lineColor: 'blue',
            xPosition: 10,
        },
        vertical: {
            lineColor: 'green',
            yPosition: 50,
        },
        legend: {
			display: false
		},
        tooltip: {
            enabled: false,
            external: externalTooltipHandler
        },    
        tooltip2: {  // Ne sert plus on utilise tooltip au dessus
            callbacks: {
                label: function(context) {
                    // console.log(context);
                    // alert(context.dataIndex);
                    // alert(context.dataset.data[context.dataIndex].y);
                    let label = ' ' + context.dataset.label + '  ' || '';
                    label += context.dataset.label == 'Cours' ? '    ' : (context.dataset.label == 'MM200' ? '  ' : (context.dataset.label == 'VOLUME' ? '' : (context.dataset.label == 'MM7' ? '      ' : '    ')));
                    let ext = context.dataset.label == 'VOLUME' ? " K " : " \u20ac";
                    if (context.parsed.y !== null)
                        label += ' : ' + context.parsed.y.toLocaleString() + ext;
                    return label;
                }
            }
        }
	},
	scales: {
		x: {
			grid: {
				display: false
			},
			ticks: {
				minRotation: 0,
				maxRotation: 0,
                callback: function(value, index, ticks) {

                    let search = this.getLabelForValue(value);

                    if (typeof array_years == 'undefined') return search;

                    let year = this.getLabelForValue(value).split('-')[0];
                    let found = array_years.find(element => element == search);
                    if (found != undefined) {
                        return year;
                    }

                }
			}
		},
		y1: {
			grid: {
				color: 'rgba(255, 255, 255, 0.05)'
			},
			ticks: {
				align: 'end',
				callback: function(value, index, ticks) {
					var c = value + " \u20ac       ";
					return c.substring(0, 6);
				}
			},
			type: 'linear',
			position: 'right'
		},
		y2: {
			type: 'linear',
			position: 'left',
			display: false,
			ticks : {
				max: 100000000,
				min: 0,
				stepSize: 20000000
			}
		}
	}
};

var options_RSI_Graphe = {
	interaction: {
		intersect: false
	},
	radius: 0,
	responsive: true,
	maintainAspectRatio: true,
	plugins: {
		legend: {
			display: false
		}
	},
	scales: {
		x: {
			grid: {
				display: false
			},
			ticks: {
				minRotation: 90,
				maxRotation: 90,
				display: false
			}
		},
		y: {
			position: 'right',
			suggestedMin: 0,
			suggestedMax: 100,
			grid: {
				color: 'rgba(255, 255, 255, 0.05)'
			},
			beginAtZero: true,
			ticks: {
				stepSize: 25,
				display: true,
				align: 'end',
				callback: function(value, index, values) {
					var c = value+" %       ";
					return c.substring(0, 6);
				}
			},
			afterSetDimensions: (scale) => {
				scale.maxWidth = 100;
			}
		}
	}
};

const horizontalLines_RSI_Graphe = {
	id: 'horizontalLines_RSI_Graphe',
	beforeDraw(chart, args, options) {
		const { ctx, chartArea: { top, right, bottom, left, width, height }, scales: { x, y } } = chart;
		ctx.save();
		ctx.strokeStyle = 'rgba(255, 215, 0, 0.5)';
		// Attention, l'origine du graphe est en haut a gauche et donc le top en bas et le bottom en haut
		ctx.beginPath();
		ctx.setLineDash([3, 3]);
		h = (height/2) + top;
		// console.log('h:'+height+'y:'+y+'b:'+bottom+'t:'+top);
		ctx.moveTo(left, h);
		ctx.lineTo(right, h);
		ctx.stroke();
		ctx.fillStyle = 'rgba(1, 207, 243, 0.1)';
		pct = 30/100;
		x1 = (height*pct) + top;
		h2 = (height*(1-(pct*2)));
		ctx.fillRect(left, x1, right, h2);
		ctx.restore();
	}
};
	
var options_DM_Graphe = {
    responsive: true,
    maintainAspectRatio: true,
    animation: true,
	plugins: {
		legend: {
			display: false
		}
	},
    scales: {
        x: {
            grid: {
                display: false
            },
            ticks: {
                minRotation: 90,
                maxRotation: 90,
                display: false
            }
        },
        y: {
			grid: {
				color: 'rgba(255, 255, 255, 0.05)'
			},
            ticks : {
                suggestedMin: -40,
                suggestedMax: 40,
                max: 40,
                min: -40,
                stepSize: 10,
				callback: function(value, index, values) {
					var c = value+" %       ";
					return c.substring(0, 6);
				}
			},
        }
    }
};

const horizontalLines_DM_Graphe = {
    id: 'horizontalLines_DM_Graphe',
    beforeDraw(chart, args, options) {
//        console.log(chart);
        const { ctx, chartArea: { top, right, bottom, left, width, height }, scales: { x, y } } = chart;
        ctx.save();
//		alert(chart.scales.y.max);
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.7)';
        // Attention, l'origine du graphe est en haut a gauche et donc le top en bas et le bottom en haut
        ctx.beginPath();
        ctx.setLineDash([5, 5]);
        h = height - ((Math.abs(chart.scales.y.min) * height) / (Math.abs(chart.scales.y.min) + chart.scales.y.max)) + top;
        ctx.moveTo(left, h);
        ctx.lineTo(right, h);
        ctx.stroke();
        ctx.restore();
    }
};

var options_simulator_graphe = {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
        x: {
            gridLines: {
            },
            ticks: {
                minRotation: 45,
                maxRotation: 45,
                display: true
            }
        },
        y: {
            ticks: {
                beginAtZero:true
            }
        }
    }
};