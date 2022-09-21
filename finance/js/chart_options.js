var euro = '\u20ac';

drawLabel = function(chart, text, args = {}) {

    const {
        ctx,
        chartArea: { top, right, bottom, left, width, height },
        scales: { x, y1 },
    } = chart;

    ctx.save();

	const bgColr     = args.bgColr     || 'white';
	const textColr   = args.textColr   || 'black';
	const fontFamily = args.fontFamily || 'Verdana';
	const fontSize   = args.fontSize   || 12;
	const alignX     = args.alignX || '';
	const alignY     = args.alignY || '';
	const valueX     = args.valueX || '';
	const valueY     = args.valueY || '';
	const paddingWidth  = args.paddingWidth || 20;
	const paddingHeight = args.paddingHeight || 6;
	const xx = args.x || 0;
	const yy = args.y || 0;

    ctx.font = fontSize + 'px ' + fontFamily;

    let l = ctx.measureText(text).width;
    let h = parseInt(ctx.font, fontSize);

    let posX = alignX === 'right'  ? right-l-paddingWidth   : (alignX === 'center' ? left+((width+paddingWidth-l) / 2)  : xx);
    let posY = alignY === 'bottom' ? bottom-h-paddingHeight : (alignY === 'center' ? ((height+paddingHeight-h) / 2) : yy);

    if (valueX !== '') posX = x.getPixelForValue(valueX);
    if (valueY !== '') posY = y1.getPixelForValue(valueY) - 10 - 2;

    if (alignX == 'rightAxe') posX = width;

    ctx.fillStyle = bgColr;
    ctx.fillRect(posX, posY, (l + paddingWidth), (h + paddingHeight));

    ctx.fillStyle = textColr;
    ctx.fillText(text, posX + (paddingWidth / 2), posY + h + (paddingHeight / 2) - 2);

    ctx.restore();
}

const rightAxeText = {
    id: 'rightAxeText',
    afterDraw(chart, args, options) {

        if (options.length == 0) return;

        options.forEach(function(item) {
            drawLabel(chart, item.title, { bgColr: item.bgcolr, textColr: item.colr, alignX: 'rightAxe', valueY: item.valueY, fontSize: '10', paddingHeight: 6, paddingWidth: 10 });
        });
    }
}

const insiderText = {
    id: 'insiderText',
    afterDraw(chart, args, options) {

        if (options.length == 0) return;

        options.forEach(function(item) {
            drawLabel(chart, item.title, { bgColr: item.bgcolr, textColr: item.colr, alignX: item.alignX, alignY: item.alignY });
        });
    }
}

drawCircle = function(chart, args = {}) {
    const {
        ctx,
        chartArea: { top, right, bottom, left, width, height },
        scales: { x, y1 },
    } = chart;

	const colr  = args.c || 'white';
	const xPos  = args.x || 0;
	const yPos  = args.y || 0;
	const rayon = args.r || 10;

    ctx.save();

    ctx.strokeStyle = 'rgba(' + colr + ', 1)';
    ctx.fillStyle   = 'rgba(' + colr + ', 0.6)';
    ctx.beginPath();
    ctx.arc(xPos, yPos, rayon, 0, 2 * Math.PI, false);
    ctx.fill();
    ctx.stroke();


}

const bubbles = {
    id: 'bubbles',
    afterDraw(chart, args, options) {
        const {
            ctx,
            chartArea: { top, right, bottom, left, width, height },
            scales: { x, y1 },
        } = chart;

        if (getIntervalStatus() != 'D') return;
        if (options.length == 0) return;

        const date_ref = new Date(x.getLabelForValue(0));

//        alert(x.getLabelForValue(0));

        ctx.save();

        options.forEach(function(item) {
            let valX  = item.valueX || 0;
            let valY  = item.valueY || 0;
            let rayon = item.rayon  || 10;
            let colr  = item.rgb    || '255, 255, 255';
            const date_val = new Date(valX);

            if (date_val > date_ref)
                drawCircle(chart, { x: x.getPixelForValue(valX), y: y1.getPixelForValue(valY), r: rayon, c: colr } );
        });

        ctx.restore();
    }
}

const horizontal = {
    id: 'horizontal',
    afterDraw(chart, args, options) {
        const {
            ctx,
            chartArea: { top, right, bottom, left, width, height },
            scales: { x, y1 },
        } = chart;

        if (options.length == 0) return;

        ctx.save();

        options.forEach(function(item) {

            if (typeof item.text !== 'undefined') {
                drawLabel(chart, item.text, { x: 0, y: y1.getPixelForValue(item.yPosition)-10-2, textColr: 'black', bgColr: item.lineColor, fontSize: '10', paddingHeight: 3, paddingWidth: 4 });
            }

            if (typeof item.lineDash !== 'undefined') ctx.setLineDash(item.lineDash);
            ctx.strokeStyle = item.lineColor;
            ctx.strokeRect(left, y1.getPixelForValue(item.yPosition), width, 0);
        });

        ctx.restore();
    }
}

const vertical = {
    id: 'vertical',
    afterDraw(chart, args, options) {
        const {
            ctx,
            chartArea: { top, right, bottom, left, width, height },
            scales: { x, y1 },
        } = chart;

        if (options.length == 0) return;

        ctx.save();

        options.forEach(function(item) {
            ctx.strokeStyle = item.lineColor;
            ctx.strokeRect(x.getPixelForValue(item.xPosition), top, 0, height);
        });

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

// externalTooltipHandler est une variable qui contient un object composé des elts et peut executer du code à l'instanciation
const externalTooltipHandler = (context) => {

    // Tooltip Elements : On instancie 2 variables avec 2 elements de l'objet context
    const {chart, tooltip} = context;
    const tooltipEl = getOrCreateTooltip(chart);

    // debugger;

    // Si affichage graphe portfolio, on affiche aussi la performance relative et par rapport à la vielle ?
    if (chart.canvas.id == 'portfolio_canvas') { };
  
    // Hide if no tooltip
    if (tooltip.opacity === 0) {
        tooltipEl.style.opacity = 0;
        return;
    }
  
    // Set Text (On récupère les titres des tooltips dans la liste de titles prealablement setter par les declarations issue de chart)
    if (tooltip.body) {

        // Legend x
        const titleLines = tooltip.title || [];

        // Legend(s) y
        const bodyLines = tooltip.body.map(b => b.lines);

        // Remove old children
        while (tooltipEl.firstChild) tooltipEl.firstChild.remove();

        // Correspond a la recuperation de la date
        titleLines.forEach(title => {
            const div = document.createElement('div');
            const text = document.createTextNode(title);
            div.appendChild(text);
            tooltipEl.appendChild(div);
        });
  
        let valo  = 0;
        let depot = 0;
        let perf  = 0;

        // On parcours les data y du graphique de la date en cours de focus
        bodyLines.forEach((body, i) => {

            const colors = tooltip.labelColors[i];

            var t = body[0].split(': ');

            // div parent
            const div = document.createElement('div');
            div.style.backgroundColor = 'inherit';
            div.style.borderWidth = 0;

            // Carre de couleur pour la legende
            const span = document.createElement('span');
            span.style.background = t[0] == 'VOLUME' ? colors.backgroundColor : colors.borderColor;
            span.style.borderColor = colors.borderColor;

            // Calcul performance
            if (chart.canvas.id == 'portfolio_canvas') {

                if (i == 0) depot = parseInt(t[1]);
                if (i == 1) {
                    valo  = parseInt(t[1]);
                    perf = getPerf(depot, valo);
                }

            }

            // div label legende
            const label1 = document.createElement('div');
            const text1 = document.createTextNode(t[0] + ' : ');
            label1.appendChild(text1);

            // div valeur y
            const label2 = document.createElement('div');
            const text2 = document.createTextNode(t[1] + (t[0] == 'VOLUME' ? ' K' : ' ' + euro) + (chart.canvas.id == 'portfolio_canvas' && i == 1 ? ' [' + (perf >=0 ? '+' : '') + perf.toFixed(2) + '%]' : '' ));
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
        legend: {
			display: false
		},
        tooltip: {
            enabled: false,
            external: externalTooltipHandler
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
					var c = value + ' ' + euro + '       ';
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

var options_Valo_Graphe = {
    interaction: {
		intersect: false,
        mode: 'index'
	},
	radius: 0,
	responsive: true,
	maintainAspectRatio: true,
    animation: true,
    plugins: {
        legend: {
			display: false
		},
        tooltip: {
            enabled: false,
            external: externalTooltipHandler
        }
	},
	scales: {
		x: {
			grid: {
				display: false
			},
			ticks: {
				minRotation: 45,
				maxRotation: 45,
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
		y: {
			grid: {
				color: 'rgba(255, 255, 255, 0.05)'
			},
			ticks: {
				align: 'end',
				callback: function(value, index, ticks) {
					var c = (value / 1000) + 'K' + euro;
					return c;
				}
			},
            beginAtZero: true,
			type: 'linear',
			position: 'right'
		}
	}
};