var options_Stock_Graphe = {
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
	id: 'horizontalLines',
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
    scales: {
        x: {
            gridLines: {
                color: "red"
            },
            ticks: {
                minRotation: 45,
                maxRotation: 45,
                display: true
            }
        },
        y: {
            gridLines: {
                color: "red"
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
    id: 'horizontalLines',
    beforeDraw(chart, args, options) {
        const { ctx, chartArea: { top, right, bottom, left, width, height }, scales: { x, y } } = chart;
        ctx.save();
        ctx.strokeStyle = 'rgba(255, 215, 0, 0.5)';
        // Attention, l'origine du graphe est en haut a gauche et donc le top en bas et le bottom en haut
        ctx.beginPath();
        ctx.setLineDash([3, 3]);
        h = (height/2) + top;
        ctx.moveTo(left, h);
        ctx.lineTo(right, h);
        ctx.stroke();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
        ctx.restore();
    }
};

