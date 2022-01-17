var options_Stock_Graphe = {
    interaction: {
		intersect: false
	},
	radius: 0,
	responsive: true,
	maintainAspectRatio: true,
    animation: false,
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
    id: 'horizontalLines',
    beforeDraw(chart, args, options) {
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


/*         {
            "responsive": true,
            "maintainAspectRatio": true,
            "scales": {
                "x": {
                    "axis": "x",
                    "gridLines": {
                        "color": "red"
                    },
                    "ticks": {
                        "minRotation": 90,
                        "maxRotation": 90,
                        "display": true,
                        "mirror": false,
                        "textStrokeWidth": 0,
                        "textStrokeColor": "",
                        "padding": 3,
                        "autoSkip": true,
                        "autoSkipPadding": 3,
                        "labelOffset": 0,
                        "minor": {},
                        "major": {},
                        "align": "center",
                        "crossAlign": "near",
                        "showLabelBackdrop": false,
                        "backdropColor": "rgba(255, 255, 255, 0.75)",
                        "backdropPadding": 2,
                        "color": "#666"
                    },
                    "type": "category",
                    "display": true,
                    "offset": false,
                    "reverse": false,
                    "beginAtZero": false,
                    "bounds": "ticks",
                    "grace": 0,
                    "grid": {
                        "display": true,
                        "lineWidth": 1,
                        "drawBorder": true,
                        "drawOnChartArea": true,
                        "drawTicks": true,
                        "tickLength": 8,
                        "offset": false,
                        "borderDash": [],
                        "borderDashOffset": 0,
                        "borderWidth": 1,
                        "color": "rgba(0,0,0,0.1)",
                        "borderColor": "rgba(0,0,0,0.1)"
                    },
                    "title": {
                        "display": false,
                        "text": "",
                        "padding": {
                            "top": 4,
                            "bottom": 4
                        },
                        "color": "#666"
                    },
                    "id": "x",
                    "position": "bottom"
                },
                "y": {
                    "axis": "y",
                    "grid": {
                        "color": "rgba(255, 255, 255, 0.05)",
                        "display": true,
                        "lineWidth": 1,
                        "drawBorder": true,
                        "drawOnChartArea": true,
                        "drawTicks": true,
                        "tickLength": 8,
                        "offset": false,
                        "borderDash": [],
                        "borderDashOffset": 0,
                        "borderWidth": 1,
                        "borderColor": "rgba(0,0,0,0.1)"
                    },
                    "ticks": {
                        "suggestedMin": -40,
                        "suggestedMax": 40,
                        "max": 40,
                        "min": -40,
                        "stepSize": 10,
                        "minRotation": 0,
                        "maxRotation": 50,
                        "mirror": false,
                        "textStrokeWidth": 0,
                        "textStrokeColor": "",
                        "padding": 3,
                        "display": true,
                        "autoSkip": true,
                        "autoSkipPadding": 3,
                        "labelOffset": 0,
                        "minor": {},
                        "major": {},
                        "align": "center",
                        "crossAlign": "near",
                        "showLabelBackdrop": false,
                        "backdropColor": "rgba(255, 255, 255, 0.75)",
                        "backdropPadding": 2,
                        "color": "#666"
                    },
                    "type": "linear",
                    "display": true,
                    "offset": false,
                    "reverse": false,
                    "beginAtZero": false,
                    "bounds": "ticks",
                    "grace": 0,
                    "title": {
                        "display": false,
                        "text": "",
                        "padding": {
                            "top": 4,
                            "bottom": 4
                        },
                        "color": "#666"
                    },
                    "id": "y",
                    "position": "left"
                }
            },
            "plugins": {}
        }
 */