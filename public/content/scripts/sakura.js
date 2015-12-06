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
