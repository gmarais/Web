/* ******************************************************** _ *** _ ******** */
/*                                                   ______//_____\\______   */
/*   WhiteCanvas 2016                               |                     |  */
/*                                                  |                     |  */
/*   Created by Gabriel Marais                      |                     |  */
/*                                                  |                     |  */
/*                                                  |_____.____.______W_C_|  */
/*   https://github.com/gmarais                     |_____________________|  */
/*                                                  //         ||        \\  */
/* *********************************************** // ******************* \\ */

/*
 * WhiteCanvas JS vasic type extenssions
 */
JSON.tryParse = function (string) {
	try
	{
		return JSON.parse(string);
	}
	catch (e)
	{
		return null;
	}
};
Array.prototype.contains = function(obj) {
	var i = this.length;
	while (i--) {
		if (this[i] === obj) {
			return true;
		}
	}
	return false;
};
NodeList.prototype.contains = function(obj) {
	var i = this.length;
	while (i--) {
		if (this[i] === obj) {
			return true;
		}
	}
	return false;
};

/*
 * WhiteCanvas Nodes prototypes functions
 */
Node.prototype.ready = function (fn) {
	if (this.readyState && this.readyState != 'loading')
	{
		fn();
	}
	else if (this.addEventListener)
	{
		this.addEventListener('DOMContentLoaded', fn);
	}
	else
	{
		this.attachEvent('onreadystatechange', function() {
			if (this.readyState != 'loading')
				fn();
		});
	}
	return this;
};
Node.prototype.hasClass = function (className)
{
	if (className && this.className && this.className.indexOf(className) != -1)
	{
		return true;
	}
	return false;
}
Node.prototype.addClass = function (className)
{
	if (className && !this.className || this.className.indexOf(className) == -1)
	{
		if (this.className)
			this.className += ' ' + className;
		else
			this.className = className;
	}
	return this;
}
Node.prototype.removeClass = function (className)
{
	if (className && this.className && this.className.indexOf(className) != -1)
	{
		var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
		this.className = this.className.replace(reg, '');
	}
	return this;
}
Node.prototype.html = function (data)
{
	if (data !== undefined)
	{
		this.innerHTML = data;
		return this;
	}
	return this.innerHTML;
};
Node.prototype.attr = function (name, value)
{
	if (value !== undefined)
	{
		this.setAttribute(name, value);
		return this;
	}
	return this.getAttribute(name);
};
Node.prototype.data = function (name, value)
{
	return this.attr('data-' + name, value);
}
Node.prototype.append = function (data)
{
	this.innerHTML += data;
	return this;
};
Node.prototype.on = function (type, target, callback)
{
	if (callback instanceof Function)
	{
		this.addEventListener(type, function (e) {
			var list = document.querySelectorAll(target);
			if (list.contains(e.target))
			{
				callback(e);
			}
		}, false);
	}
	else if (target instanceof Function)
	{
		callback = target;
		this.addEventListener(type, function (e) {
				callback(e);
		}, false);
	}
	return this;
};

/*
 * WhiteCanvas JS object
 * The wc object is a minimalist JQuery like tool
 * Constructor:
 */
var wc = function wc(query)
{
	var elem;
	if (query instanceof HTMLElement
		|| query instanceof HTMLDocument
		|| query instanceof Window)
	{
		elem = query;
	}
	else
	{
		elem = document.querySelector(query);
	}
	return elem;
};

/*
 * WhiteCanvas JS object
 * Generic functions:
 */
wc.ajax = function(url, params, callback, method)
{
	method = method || "POST";
	method = method.toUpperCase();
	var xhttp=new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (xhttp.readyState == 4 && xhttp.status == 200)
		{
			callback(xhttp.responseText);
		}
	};
	xhttp.open(method, url, true);
	if (method == "POST")
	{
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		var params_urlencoded = "";
		for(var key in params)
		{
			if(params.hasOwnProperty(key))
			{
				params_urlencoded += '&' + key + '=' + params[key];
			}
		}
		xhttp.send(params_urlencoded.substring(1));
	}
	else
	{
		xhttp.send();
	}
};
wc.post = function(url, params, method)
{
	method = method || "post";
	var form = document.createElement("form");
	form.setAttribute("method", method);
	form.setAttribute("action", url);
	for(var key in params)
	{
		if(params.hasOwnProperty(key))
		{
			var hiddenField = document.createElement("input");
			hiddenField.setAttribute("type", "hidden");
			hiddenField.setAttribute("name", key);
			hiddenField.setAttribute("value", params[key]);
			form.appendChild(hiddenField);
		}
	}
	document.body.appendChild(form);
	form.submit();
};