/*
 * Shared client side code
 */
// Meta functions
var Sakura = (function () {
    function Sakura() {
    }
    // Get or set a cookie value
    Sakura.cookie = function (name, value) {
        if (value === void 0) { value = null; }
        // If value is null only get the cookie's value
        if (value) {
            // Delete the old instance
            document.cookie = this.cookiePrefix + name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=' + this.cookiePath;
            // Assign the cookie
            document.cookie = this.cookiePrefix + name + '=' + value + '; path=' + this.cookiePath;
            // Pass the value through
            return value;
        }
        else {
            // Perform a regex on document.cookie
            var get = new RegExp('(^|; )' + encodeURIComponent(this.cookiePrefix + name) + '=([^;]*)').exec(document.cookie);
            // If anything was returned return it (professional phrasing)
            return get ? get[2] : '';
        }
    };
    // Unix timestamp
    Sakura.epoch = function () {
        return Math.floor(Date.now() / 1000);
    };
    // Toggle a class
    Sakura.toggleClass = function (element, name) {
        // Check if the class already exists and if not add it
        if (element.className.indexOf(name) < 0) {
            element.className += ' ' + name;
        }
        else {
            element.className = element.className.replace(name, '').trim();
        }
    };
    // Remove every element with a specific class name
    Sakura.removeByClass = function (name) {
        // Get the elements
        var objs = document.getElementsByClassName(name);
        // Use a while loop to remove each element
        while (objs.length > 0) {
            objs[0].parentNode.removeChild(objs[0]);
        }
    };
    // Remove a single element with a specific id
    Sakura.removeById = function (id) {
        // Get the element
        var obj = document.getElementById(id);
        // If the element exists use the parent node to remove it
        if (typeof (obj) != "undefined" && obj !== null) {
            obj.parentNode.removeChild(obj);
        }
    };
    // Alternative for Math.log2() since it's still experimental
    Sakura.log2 = function (num) {
        return Math.log(num) / Math.log(2);
    };
    // Get the number of unique characters in a string
    Sakura.unique = function (string) {
        // Store the already found character
        var used = [];
        // The amount of characters we've already found
        var count = 0;
        // Count the amount of unique characters
        for (var i = 0; i < string.length; i++) {
            // Check if we already counted this character
            if (used.indexOf(string[i]) == -1) {
                // Push the character into the used array
                used.push(string[i]);
                // Up the count
                count++;
            }
        }
        // Return the count
        return count;
    };
    // Calculate password entropy
    Sakura.entropy = function (string) {
        // Decode utf-8 encoded characters
        string = utf8.decode(string);
        // Count the unique characters in the string
        var unique = this.unique(string);
        // Do the entropy calculation
        return unique * this.log2(256);
    };
    // Validate string lengths
    Sakura.stringLength = function (string, minimum, maximum) {
        // Get length of string
        var length = string.length;
        // Check if it meets the minimum/maximum
        if (length < minimum || length > maximum) {
            return false;
        }
        // If it passes both return true
        return true;
    };
    // Validate email address formats
    Sakura.validateEmail = function (email) {
        // RFC compliant e-mail address regex
        var re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,48})+$/;
        // Test it on the email var which'll return a boolean
        return re.test(email);
    };
    // Calculate the time that has elapsed since a certain data (doesn't take leap years in account).
    Sakura.timeElapsed = function (timestamp, append, none) {
        if (append === void 0) { append = ' ago'; }
        if (none === void 0) { none = 'Just now'; }
        // Subtract the entered timestamp from the current timestamp
        var time = this.epoch() - timestamp;
        // If the new timestamp is below 1 return a standard string
        if (time < 1) {
            return none;
        }
        // Times array
        var times = {
            31536000: ['year', 'a'],
            2592000: ['month', 'a'],
            604800: ['week', 'a'],
            86400: ['day', 'a'],
            3600: ['hour', 'an'],
            60: ['minute', 'a'],
            1: ['second', 'a']
        };
        //
        var timeKeys = Object.keys(times).reverse();
        // Iterate over the times
        for (var i in timeKeys) {
            // Do a devision to check if the given timestamp fits in the current "type"
            var calc = time / parseInt(timeKeys[i]);
            // Check if we have a match
            if (calc >= 1) {
                // Round the number
                var display = Math.floor(calc);
                // Return the formatted string
                return (display === 1 ? times[timeKeys[i]][1] : display) + " " + times[timeKeys[i]][0] + (display === 1 ? '' : 's') + append;
            }
        }
        // If everything fails just return none
        return none;
    };
    Sakura.cookiePrefix = ""; // Cookie prefix, gets prepended to cookie names
    Sakura.cookiePath = "/"; // Cookie path, can in most cases be left untouched
    return Sakura;
})();
// UTF-8 functions
var utf8 = (function () {
    function utf8() {
    }
    // Encode a utf-8 string
    utf8.encode = function (string) {
        return unescape(encodeURIComponent(string));
    };
    // Decode a utf-8 string
    utf8.decode = function (string) {
        return decodeURIComponent(escape(string));
    };
    return utf8;
})();
// HTTP methods
var HTTPMethods;
(function (HTTPMethods) {
    HTTPMethods[HTTPMethods["GET"] = 0] = "GET";
    HTTPMethods[HTTPMethods["HEAD"] = 1] = "HEAD";
    HTTPMethods[HTTPMethods["POST"] = 2] = "POST";
    HTTPMethods[HTTPMethods["PUT"] = 3] = "PUT";
    HTTPMethods[HTTPMethods["DELETE"] = 4] = "DELETE";
})(HTTPMethods || (HTTPMethods = {}));
// AJAX functions
var AJAX = (function () {
    // Prepares the XMLHttpRequest and stuff
    function AJAX() {
        this.send = null;
        this.request = new XMLHttpRequest();
        this.callbacks = new Object();
        this.headers = new Object();
    }
    // Start
    AJAX.prototype.start = function (method) {
        var _this = this;
        // Open the connection
        this.request.open(HTTPMethods[method], this.url, true);
        // Set headers
        this.prepareHeaders();
        // Watch the ready state
        this.request.onreadystatechange = function () {
            // Only invoke when complete
            if (_this.request.readyState === 4) {
                // Check if a callback if present
                if ((typeof _this.callbacks[_this.request.status]).toLowerCase() === 'function') {
                    _this.callbacks[_this.request.status]();
                }
                else {
                    if ((typeof _this.callbacks['0']).toLowerCase() === 'function') {
                        // Call that
                        _this.callbacks['0']();
                    }
                }
            }
        };
        this.request.send(this.send);
    };
    // Stop
    AJAX.prototype.stop = function () {
        this.request = null;
    };
    // Add post data
    AJAX.prototype.setSend = function (data) {
        // Storage array
        var store = new Array();
        // Iterate over the object and them in the array with an equals sign inbetween
        for (var item in data) {
            store.push(encodeURIComponent(item) + "=" + encodeURIComponent(data[item]));
        }
        // Assign to send
        this.send = store.join('&');
    };
    // Set raw post
    AJAX.prototype.setRawSend = function (data) {
        this.send = data;
    };
    // Get response
    AJAX.prototype.response = function () {
        return this.request.responseText;
    };
    // Set charset
    AJAX.prototype.contentType = function (type, charset) {
        if (charset === void 0) { charset = null; }
        this.addHeader('Content-Type', type + ';charset=' + (charset ? charset : 'utf-8'));
    };
    // Add a header
    AJAX.prototype.addHeader = function (name, value) {
        // Attempt to remove a previous instance
        this.removeHeader(name);
        // Add the new header
        this.headers[name] = value;
    };
    // Remove a header
    AJAX.prototype.removeHeader = function (name) {
        if ((typeof this.headers[name]).toLowerCase() !== 'undefined') {
            delete this.headers[name];
        }
    };
    // Prepare request headers
    AJAX.prototype.prepareHeaders = function () {
        for (var header in this.headers) {
            this.request.setRequestHeader(header, this.headers[header]);
        }
    };
    // Adds a callback
    AJAX.prototype.addCallback = function (status, callback) {
        // Attempt to remove previous instances
        this.removeCallback(status);
        // Add the new callback
        this.callbacks[status] = callback;
    };
    // Delete a callback
    AJAX.prototype.removeCallback = function (status) {
        // Delete the callback if present
        if ((typeof this.callbacks[status]).toLowerCase() === 'function') {
            delete this.callbacks[status];
        }
    };
    // Sets the URL
    AJAX.prototype.setUrl = function (url) {
        this.url = url;
    };
    return AJAX;
})();
