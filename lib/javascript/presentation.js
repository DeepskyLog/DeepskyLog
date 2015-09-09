var theResizeElement, theResizeSize;
var theClientHeight, theClientWidth;
var div5Width, div5Height;
var div5StdWidth, div5StdHeight;
var div5Left = 169; // see style sheet
var div5Top = 90; // see style sheet

var theColumnSource = '';
var theMaxColNr = 0;
var theClassName = 'class';
var orderColumnSourceOrder = new Array(); // [['formname','C0'],['formname','C3'],...]
var titleData = new Array(); // ['C1-innerHTML','C2-innerHTML',...]
var titleAttributes = new Array();
var titleAttributesValues = new Array();
var columnData = new Array(); // [['C1D1-innerHTML','C1D2-innerHTML',...],...['CxDy-innerHTML','CxDy+1-innerHTML',...],...]
var columnAttributes = new Array();
var columnAttributesValues = new Array();
var restoreColumnsSourceCollapsed = new Array(); // ['C5','C8',...]
var restoreColumnsSourceCollapsedHeaders = new Array(); // ['Header5','Header8',...]

function findVerticalPosition(theElement) {
	var obj = document.getElementById(theElement);
	var top = obj.offsetTop;
	while (obj = obj.offsetParent) {
		top += obj.offsetTop;
	}
	return top;
}
function resizeDivsViewCatalogs(leftmenu, topmenu) {

	var height = document.getElementById('catalogs').clientHeight;
	var width = document.getElementById('catalogs').clientWidth;
	document.getElementById('view_catalogs_left').style.height = (height)
			+ 'px';
	document.getElementById('view_catalogs_left').style.width = '190px';
	document.getElementById('view_catalogs_right').style.height = (height)
			+ 'px';
	document.getElementById('view_catalogs_right').style.width = (width - 200)
			+ 'px';
}
theResizeElement = '';
theResizeSize = 0;
function messageBox() {
	var height = window.innerHeight;
	var width = window.innerWidth;
	if (!(height)) {
		height = document.documentElement.clientHeight;
		width = document.documentElement.clientWidth;
	} else if (!(height)) {
		height = document.body.clientHeight;
		width = document.body.clientWidth;
	}
	document.getElementById('veil').style.height = height + 'px';
	document.getElementById('veil').style.width = width + 'px';
	document.getElementById('veil').style.filter = 'alpha(opacity=20)';
	document.getElementById('veil').style.opacity = 0.2;
	document.getElementById('dialogback').style.top = ((height / 2) - 92)
			+ 'px';
	document.getElementById('dialogback').style.left = ((width / 2) - 142)
			+ 'px';
	document.getElementById('dialogback').style.height = 200 + 'px';
	document.getElementById('dialogback').style.width = 300 + 'px';
	document.getElementById('dialogback').style.filter = 'alpha(opacity=30)';
	document.getElementById('dialogback').style.opacity = 0.3;
	document.getElementById('dialog').style.top = ((height / 2) - 100) + 'px';
	document.getElementById('dialog').style.left = ((width / 2) - 150) + 'px';
	document.getElementById('dialog').style.height = 200 + 'px';
	document.getElementById('dialog').style.width = 300 + 'px';
	document.getElementById('dialogdiv1').style.height = 150 + 'px';
	document.getElementById('dialogdiv1').style.width = 280 + 'px';
	document.getElementById('dialogdiv2').style.top = 170 + 'px';
	document.getElementById('dialogdiv2').style.height = 30 + 'px';
	document.getElementById('dialogdiv2').style.width = 300 + 'px';
	document.getElementById('alertMsgOk').focus();
}
function popupForm(dx, dy, contents) {
	var height = div5Height;
	var width = div5Width;
	document.getElementById('veil').style.height = height + 'px';
	document.getElementById('veil').style.width = width - 15 + 'px';
	document.getElementById('veil').style.filter = 'alpha(opacity=20)';
	document.getElementById('veil').style.opacity = 0.2;
	document.getElementById('dialogback').style.top = ((height - dy) / 2)
			+ 'px';
	document.getElementById('dialogback').style.left = ((width - dx) / 2)
			+ 'px';
	document.getElementById('dialogback').style.height = dy + 'px';
	document.getElementById('dialogback').style.width = dx + 'px';
	document.getElementById('dialogback').style.filter = 'alpha(opacity=30)';
	document.getElementById('dialogback').style.opacity = 0.3;
	document.getElementById('dialog').style.top = (((height - dy) / 2) - 5)
			+ 'px';
	document.getElementById('dialog').style.left = (((width - dx) / 2) - 5)
			+ 'px';
	document.getElementById('dialog').style.height = dy + 'px';
	document.getElementById('dialog').style.width = dx + 'px';
	document.getElementById('dialogdiv1').style.height = dy - 20 + 'px';
	document.getElementById('dialogdiv1').style.width = dx - 20 + 'px';
	document.getElementById('dialogdiv1').innerHTML = contents;
}
function confirmAlertMessage() {
	document.getElementById('veil').style.height = 0 + 'px';
	document.getElementById('veil').style.width = 0 + 'px';
	document.getElementById('dialogback').style.top = 0 + 'px';
	document.getElementById('dialogback').style.left = 0 + 'px';
	document.getElementById('dialogback').style.height = 0 + 'px';
	document.getElementById('dialogback').style.width = 0 + 'px';
	document.getElementById('dialog').style.top = 0 + 'px';
	document.getElementById('dialog').style.left = 0 + 'px';
	document.getElementById('dialog').style.height = 0 + 'px';
	document.getElementById('dialog').style.width = 0 + 'px';
	document.getElementById('dialog').style.overflow = 'hidden';
	document.getElementById('dialogdiv1').style.height = 0 + 'px';
	document.getElementById('dialogdiv1').style.width = 0 + 'px';
	document.getElementById('dialogdiv1').style.overflow = 'hidden';
	document.getElementById('dialogdiv2').style.top = 0 + 'px';
	document.getElementById('dialogdiv2').style.height = 0 + 'px';
	document.getElementById('dialogdiv2').style.width = 0 + 'px';
	document.getElementById('dialogdiv2').style.overflow = 'hidden';
}
function externalLinks() {
	if (!document.getElementsByTagName)
		return;
	var anchors = document.getElementsByTagName("a");
	for (var i = 0; i < anchors.length; i++) {
		var anchor = anchors[i];
		if (anchor.getAttribute("href")
				&& (anchor.getAttribute("rel") == "external")) {
			anchor.target = "new_window";
		}
	}
}
function getCookie(theCookie) {
	if (document.cookie.length > 0) {
		start = document.cookie.indexOf(theCookie + '=');
		if (start != -1) {
			start = start + theCookie.length + 1;
			end = document.cookie.indexOf(';', start);
			if (end == -1)
				end = document.cookie.length;
			return unescape(document.cookie.substring(start, end));
		}
	}
}
function setColumnSource(columnSource) {
	theColumnSource = columnSource;
}
function setClassName(className) {
	theClassName = className;
}
// read the data on the page into two arrays => titleData, columnData
function readTitleColumnData(maxColNr) {
	i = 0;
	theMaxColNr = maxColNr;
	while (i < maxColNr) {
		titleData[i] = document.getElementById('C' + i).innerHTML;

		var tempAttributes = new Array();
		var tempAttributesValues = new Array();
		tempAttributes[tempAttributes.length] = theClassName;
		tempAttributesValues[tempAttributesValues.length] = document
				.getElementById('C' + i).getAttribute(theClassName);

		titleAttributes[i] = tempAttributes;
		titleAttributesValues[i] = tempAttributesValues;

		j = 0;
		var tempColumnData = new Array();
		var tempColumnAttributes = new Array();
		var tempColumnAttributesValues = new Array();
		while (document.getElementById('C' + i + 'D' + j)) {
			tempColumnData[j] = document.getElementById('C' + i + 'D' + j).innerHTML;

			var tempAttributes = new Array();
			var tempAttributesValues = new Array();
			tempAttributes[tempAttributes.length] = theClassName;
			tempAttributesValues[tempAttributesValues.length] = document
					.getElementById('C' + i + 'D' + j).getAttribute(
							theClassName);
			tempAttributes[tempAttributes.length] = 'onmouseover';
			tempAttributesValues[tempAttributesValues.length] = document
					.getElementById('C' + i + 'D' + j).getAttribute(
							'onmouseover');

			tempColumnAttributes[j] = tempAttributes;
			tempColumnAttributesValues[j] = tempAttributesValues;

			j++;
		}
		columnData[i] = tempColumnData;
		columnAttributes[i] = tempColumnAttributes;
		columnAttributesValues[i] = tempColumnAttributesValues;
		i++;
	}
}
function setColumnsOrder(newcookiestring) { // read all orders cookie
	var cookie = eval('[' + newcookiestring + ']');
	// filter this form's orders => orderColumnSourceOrder
	orderColumnSourceOrder = new Array();
	for (i = 0; i < cookie.length; i++) {
		if (cookie[i][0] == theColumnSource)
			orderColumnSourceOrder[orderColumnSourceOrder.length] = cookie[i][1];
	}
	// get the cookies
	var orderColumns = eval(getCookie('orderColumns410'));
	cookiestring = '';
	// rebuild the other cookies
	for (i = 0; i < orderColumns.length; i++)
		if (orderColumns[i][0] != theColumnSource)
			cookiestring = cookiestring + "['" + orderColumns[i][0] + "','"
					+ orderColumns[i][1] + "'],";
	// add the own cookies
	for (i = 0; i < orderColumnSourceOrder.length; i++)
		cookiestring = cookiestring + "['" + theColumnSource + "','"
				+ orderColumnSourceOrder[i] + "'],";
	// rewrite the cookies
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + 365);
	document.cookie = "orderColumns410=["
			+ cookiestring.substr(0, cookiestring.length - 1) + "];expires="
			+ exdate.toUTCString();
}
function setColumnsRestore(newcookiestring) {
	var cookie = new Array();
	// read all restore columns cookie
	cookie = eval('[' + newcookiestring + ']');
	restoreColumnsSourceCollapsed = new Array();
	restoreColumnsSourceCollapsedHeaders = new Array();
	for (i = 0; i < cookie.length; i++) { // handle cookies for this form
		if (cookie[i][0] == theColumnSource) {
			restoreColumnsSourceCollapsed[restoreColumnsSourceCollapsed.length] = cookie[i][1];
			restoreColumnsSourceCollapsedHeaders[restoreColumnsSourceCollapsedHeaders.length] = cookie[i][2];
		}
	}
	// get the cookies
	var restoreColumns = eval(getCookie('restoreColumns410'));
	cookiestring = '';
	// rebuild the other cookies
	if (restoreColumns)
		for (i = 0; i < restoreColumns.length; i++)
			if (restoreColumns[i][0] != theColumnSource)
				cookiestring = cookiestring + "['" + restoreColumns[i][0]
						+ "','" + restoreColumns[i][1] + "','"
						+ restoreColumns[i][2] + "'],";
	// add the own cookies
	for (i = 0; i < restoreColumnsSourceCollapsed.length; i++)
		cookiestring = cookiestring + "['" + theColumnSource + "','"
				+ restoreColumnsSourceCollapsed[i] + "','"
				+ restoreColumnsSourceCollapsedHeaders[i] + "'],";
	// rewrite the cookies
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + 365);
	document.cookie = "restoreColumns410=["
			+ cookiestring.substr(0, cookiestring.length - 1) + "];expires="
			+ exdate.toUTCString();
}
// read the column order for this columnSource (=form) into an array =>
// orderColumnsSourceOrder
function readOrderColumnSourceOrder() { // read all orders cookie
	var orderColumns = eval(getCookie('orderColumns410'));
	// filter this form's orders => orderColumnSourceOrder
	if (orderColumns) {
		orderColumnSourceOrder = new Array();
		for (i = 0; i < orderColumns.length; i++)
			if (orderColumns[i][0] == theColumnSource)
				orderColumnSourceOrder[orderColumnSourceOrder.length] = orderColumns[i][1];
	}
	// if orders not in cookie => write standard order in cookie
	if (orderColumnSourceOrder.length != (theMaxColNr + 1)) {
		cookiestring = '';
		if (orderColumns) {
			for (i = 0; i < orderColumns.length; i++)
				if (orderColumns[i][0] != theColumnSource)
					cookiestring = cookiestring + "['" + orderColumns[i][0]
							+ "','" + orderColumns[i][1] + "'],";
		}
		i = 0;
		while (i <= theMaxColNr) {
			cookiestring = cookiestring + "['" + theColumnSource + "','C" + i
					+ "'],";
			orderColumnSourceOrder[i] = 'C' + i;
			i++;
		}
		var exdate = new Date();
		exdate.setDate(exdate.getDate() + 365);
		document.cookie = "orderColumns410=["
				+ cookiestring.substr(0, cookiestring.length - 1)
				+ "];expires=" + exdate.toUTCString();
	}
}
// read the collapsed columns for this columnSource (=form) into an array =>
// restoreColumnsSourceCollapsed
function readCollapseColumnsSourceCollapsed() {
	var cookie = new Array();
	// read all restore columns cookie
	if (getCookie('restoreColumns410'))
		cookie = eval(getCookie('restoreColumns410'));
	restoreColumnsSourceCollapsed = new Array();
	restoreColumnsSourceCollapsedHeaders = new Array();
	for (i = 0; i < cookie.length; i++) { // handle cookies for this form
		if (cookie[i][0] == theColumnSource) {
			restoreColumnsSourceCollapsed[restoreColumnsSourceCollapsed.length] = cookie[i][1];
			restoreColumnsSourceCollapsedHeaders[restoreColumnsSourceCollapsedHeaders.length] = cookie[i][2];
		}
	}
	return false;
}
function writeColumnsOrder() {
	i = 0;
	while (i < (orderColumnSourceOrder.length - 1)) {
		document.getElementById('C' + i).innerHTML = titleData[orderColumnSourceOrder[i]
				.substr(1)];
		t = 0;
		while (t < titleAttributes[i].length) {
			if (titleAttributesValues[orderColumnSourceOrder[i].substr(1)][t])
				document.getElementById('C' + i)
						.setAttribute(
								titleAttributes[orderColumnSourceOrder[i]
										.substr(1)][t],
								titleAttributesValues[orderColumnSourceOrder[i]
										.substr(1)][t]);
			t++;
		}
		j = 0;
		while (columnData[i][j]) {
			document.getElementById('C' + i + 'D' + j).innerHTML = columnData[orderColumnSourceOrder[i]
					.substr(1)][j];
			t = 0;
			while (t < columnAttributes[orderColumnSourceOrder[i].substr(1)][j].length) {
				document
						.getElementById('C' + i + 'D' + j)
						.setAttribute(
								columnAttributes[orderColumnSourceOrder[i]
										.substr(1)][j][t],
								columnAttributesValues[orderColumnSourceOrder[i]
										.substr(1)][j][t]);
				t++;
			}
			j++;
		}
		i++;
	}
}
function collapseColumns() {
	for (i = 0; i < restoreColumnsSourceCollapsed.length; i++) { // check
		// actual
		// location
		// if column
		k = 0;
		while ((k < orderColumnSourceOrder.length)
				&& (orderColumnSourceOrder[k] != restoreColumnsSourceCollapsed[i]))
			k++;
		if (k < orderColumnSourceOrder.length) { // empty column title
			document.getElementById('C' + k).innerHTML = "<a href=\"#\" class=\"centered width100pct\" onclick=\"restoreColumn('"
					+ restoreColumnsSourceCollapsed[i]
					+ "');return false;\" title=\""
					+ restoreColumnsSourceCollapsedHeaders[i] + "\">.</a>";
			j = 0;
			while (document.getElementById('C' + k + 'D' + j)) {
				document.getElementById('C' + k + 'D' + j).innerHTML = "";
				j++;
			}
		}
	}
	return false;
}
function removeColumn(theColumn, header0) { // check actual location if column
	k = 0;
	while ((k < orderColumnSourceOrder.length)
			&& (orderColumnSourceOrder[k] != theColumn))
		k++;
	if (orderColumnSourceOrder[k] != theColumn)
		return;
	// empty column
	document.getElementById('C' + k).innerHTML = "<a href=\"#\" class=\"centered width100pct\" onclick=\"restoreColumn('"
			+ theColumn + "');return false;\" title=\"" + header0 + "\">.</a>";
	i = 0;
	while (document.getElementById('C' + k + 'D' + i)) {
		document.getElementById('C' + k + 'D' + i).innerHTML = "";
		i++;
	}
	restoreColumnsSourceCollapsed[restoreColumnsSourceCollapsed.length] = theColumn;
	restoreColumnsSourceCollapsedHeaders[restoreColumnsSourceCollapsedHeaders.length] = header0;
	// register cookie
	var cookie = new Array();
	if (getCookie('restoreColumns410'))
		cookie = eval(getCookie('restoreColumns410'));
	cookiestring = '';
	for (i = 0; i < cookie.length; i++)
		if ((cookie[i][0] != theColumnSource) || (cookie[i][1] != theColumn))
			cookiestring = cookiestring + "['" + cookie[i][0] + "','"
					+ cookie[i][1] + "','" + cookie[i][2] + "'],";
	cookiestring = cookiestring + "['" + theColumnSource + "','" + theColumn
			+ "','" + header0 + "']";
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + 365);
	document.cookie = "restoreColumns410=[" + cookiestring + "];expires="
			+ exdate.toUTCString();
}
function moveColumnLeft(theColumn) { // move column left in order array
	var i;
	for (i = 1; i < orderColumnSourceOrder.length; i++) {
		if (orderColumnSourceOrder[i] == theColumn) {
			orderColumnSourceOrder[i] = orderColumnSourceOrder[i - 1];
			orderColumnSourceOrder[i - 1] = theColumn;
		}
	}
	// get the cookies
	var orderColumns = eval(getCookie('orderColumns410'));
	cookiestring = '';
	// rebuild the other cookies
	for (i = 0; i < orderColumns.length; i++)
		if (orderColumns[i][0] != theColumnSource)
			cookiestring = cookiestring + "['" + orderColumns[i][0] + "','"
					+ orderColumns[i][1] + "'],";
	// add the own cookies
	for (i = 0; i < orderColumnSourceOrder.length; i++)
		cookiestring = cookiestring + "['" + theColumnSource + "','"
				+ orderColumnSourceOrder[i] + "'],";
	// rewrite the cookies
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + 365);
	document.cookie = "orderColumns410=["
			+ cookiestring.substr(0, cookiestring.length - 1) + "];expires="
			+ exdate.toUTCString();
	// redraw the screen
	writeColumnsOrder();
	collapseColumns();
}
function moveColumnRight(theColumn) { // move column right in order array
	var i;
	for (i = 0; i < orderColumnSourceOrder.length - 1; i++) {
		if (orderColumnSourceOrder[i] == theColumn) {
			orderColumnSourceOrder[i] = orderColumnSourceOrder[i + 1];
			orderColumnSourceOrder[i + 1] = theColumn;
			i++;
		}
	}
	// get the cookies
	var orderColumns = eval(getCookie('orderColumns410'));
	cookiestring = '';
	// rebuild the other cookies
	for (i = 0; i < orderColumns.length; i++)
		if (orderColumns[i][0] != theColumnSource)
			cookiestring = cookiestring + "['" + orderColumns[i][0] + "','"
					+ orderColumns[i][1] + "'],";
	// add the own cookies
	for (i = 0; i < orderColumnSourceOrder.length; i++)
		cookiestring = cookiestring + "['" + theColumnSource + "','"
				+ orderColumnSourceOrder[i] + "'],";
	// rewrite the cookies
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + 365);
	document.cookie = "orderColumns410=["
			+ cookiestring.substr(0, cookiestring.length - 1) + "];expires="
			+ exdate.toUTCString();
	// redraw the screen
	writeColumnsOrder();
	collapseColumns();
}
function restoreColumn(theColumn) {
	k = 0;
	while (orderColumnSourceOrder[k] != theColumn)
		k++;
	document.getElementById('C' + k).innerHTML = titleData[theColumn.substr(1)];
	j = 0;
	while (j < columnData[theColumn.substr(1)].length) {
		document.getElementById('C' + k + 'D' + j).innerHTML = columnData[theColumn
				.substr(1)][j];
		j++;
	}
	var temprestoreColumnsSourceCollapsed = new Array();
	var temprestoreColumnsSourceCollapsedHeaders = new Array();
	for (var i = 0; i < restoreColumnsSourceCollapsed.length; i++) {
		if (restoreColumnsSourceCollapsed[i] != theColumn) {
			temprestoreColumnsSourceCollapsed[temprestoreColumnsSourceCollapsed.length] = restoreColumnsSourceCollapsed[i];
			temprestoreColumnsSourceCollapsedHeaders[temprestoreColumnsSourceCollapsedHeaders.length] = restoreColumnsSourceCollapsedHeaders[i];
		}
	}
	restoreColumnsSourceCollapsed = temprestoreColumnsSourceCollapsed;
	restoreColumnsSourceCollapsedHeaders = temprestoreColumnsSourceCollapsedHeaders;
	var cookie = new Array();
	if (getCookie('restoreColumns410'))
		cookie = eval(getCookie('restoreColumns410'));
	cookiestring = '';
	for (i = 0; i < cookie.length; i++)
		if ((cookie[i][0] != theColumnSource) || (cookie[i][1] != theColumn))
			cookiestring = cookiestring + "['" + cookie[i][0] + "','"
					+ cookie[i][1] + "','" + cookie[i][2] + "'],";
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + 365);
	document.cookie = "restoreColumns410=["
			+ cookiestring.substring(0, (cookiestring.length - 1))
			+ "];expires=" + exdate.toUTCString();
	return false;
}
function getColumnsOrder() {
	var cookie = new Array();
	if (getCookie('orderColumns410'))
		orderColumns = eval(getCookie('orderColumns410'));
	var cookiestring = '';
	for (var i = 0; i < orderColumns.length; i++)
		if (orderColumns[i][0] == theColumnSource)
			cookiestring = cookiestring + "['" + orderColumns[i][0] + "','"
					+ orderColumns[i][1] + "'],";
	return cookiestring.substring(0, cookiestring.length - 1);
}
function getColumnsRestore() {
	var cookie = new Array();
	if (getCookie('restoreColumns410'))
		cookie = eval(getCookie('restoreColumns410'));
	cookiestring = '';
	for (i = 0; i < cookie.length; i++)
		if ((cookie[i][0] == theColumnSource))
			cookiestring = cookiestring + "['" + cookie[i][0] + "','"
					+ cookie[i][1] + "','" + cookie[i][2] + "'],";
	return cookiestring.substring(0, cookiestring.length - 1);
}
