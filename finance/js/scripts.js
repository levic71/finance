function ajaxCall(method, url, msg, refresh=false) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			alert(msg)
			if (refresh) window.location.reload();
		}
	};
	xmlhttp.open(method, url, true);
	xmlhttp.send();
}

function addStock(symbol, name, type, region, marketopen, marketclose, timezone, currency) {
	url = "stock_add.php?symbol="+symbol+"&name="+name+"&type="+type+"&region="+region+"&marketopen="+marketopen+"&marketclose="+marketclose+"&timezone="+timezone+"&currency="+currency;
	ajaxCall("GET", url, 'Stock '+symbol+' added !');
}

function deleteStock(symbol) {
	if (confirm('Sur ?')) {
		ajaxCall("GET", "stock_delete.php?symbol="+symbol, 'Stock '+symbol+' deleted !', true);
	}
}

function updateStock(symbol) {
	ajaxCall("GET", "stock_update.php?symbol="+symbol, 'Stock '+symbol+' updated !', true);
}
