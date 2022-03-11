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
            options.forEach(function(item){
                ctx.strokeStyle = item.lineColor;
                ctx.strokeRect(left, y1.getPixelForValue(item.yPosition), width, 0);
            });
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
            options.forEach(function(item){
                ctx.strokeStyle = item.lineColor;
                ctx.strokeRect(x.getPixelForValue(item.xPosition), top, 0, height);
            });
        }

        ctx.restore();
    }
}
  
var options_Stock_Graphe = {
    interaction: {
		intersect: false
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
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    let ext = context.datasetIndex == 2 ? "K " : "";
                    if (label) {
                        label += ':  ';
                    }
                    if (context.parsed.y !== null) {
//                        label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                        label += context.parsed.y.toLocaleString() + ext;
                    }
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
				minRotation: 90,
				maxRotation: 90
			}
		},
		y1: {
			grid: {
				color: 'rgba(255, 255, 255, 0.05)'
			},
			ticks: {
				align: 'end',
				callback: function(value, index, values) {
					var c = value+" \u20ac       ";
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
            gridLines: {
                color: "red"
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
        ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
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