/*
 * Shared client side code
 */

// Meta functions
class Sakura {
    public static cookiePrefix: string = ""; // Cookie prefix, gets prepended to cookie names
    public static cookiePath: string = "/"; // Cookie path, can in most cases be left untouched

    // Get or set a cookie value
    public static cookie(name: string, value: string = null): string {
        // If value is null only get the cookie's value
        if (value) {
            // Delete the old instance
            document.cookie = this.cookiePrefix + name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=' + this.cookiePath;

            // Assign the cookie
            document.cookie = this.cookiePrefix + name + '=' + value + '; path=' + this.cookiePath;

            // Pass the value through
            return value;
        } else {
            // Perform a regex on document.cookie
            var get = new RegExp('(^|; )' + encodeURIComponent(this.cookiePrefix + name) + '=([^;]*)').exec(document.cookie);

            // If anything was returned return it (professional phrasing)
            return get ? get[2] : '';
        }
    }

    // Unix timestamp
    public static epoch(): number {
        return Math.floor(Date.now() / 1000);
    }

    // Toggle a class
    public static toggleClass(element: HTMLElement, name: string): void {
        // Check if the class already exists and if not add it
        if (element.className.indexOf(name) < 0) {
            element.className += ' ' + name;
        } else { // If so remove it and kill additional spaces
            element.className = element.className.replace(name, '').trim();
        }
    }

    // Remove every element with a specific class name
    public static removeByClass(name: string): void {
        // Get the elements
        var objs = document.getElementsByClassName(name);

        // Use a while loop to remove each element
        while (objs.length > 0) {
            objs[0].parentNode.removeChild(objs[0]);
        }
    }

    // Remove a single element with a specific id
    public static removeById(id: string): void {
        // Get the element
        var obj = document.getElementById(id);

        // If the element exists use the parent node to remove it
        if (typeof (obj) != "undefined" && obj !== null) {
            obj.parentNode.removeChild(obj);
        }
    }

    // Alternative for Math.log2() since it's still experimental
    public static log2(num: number): number {
        return Math.log(num) / Math.log(2);
    }

    // Get the number of unique characters in a string
    public static unique(string: string): number {
        // Store the already found character
        var used: string[] = [];

        // The amount of characters we've already found
        var count: number = 0;

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
    }

    // Calculate password entropy
    public static entropy(string: string): number {
        // Decode utf-8 encoded characters
        string = utf8.decode(string);

        // Count the unique characters in the string
        var unique: number = this.unique(string);

        // Do the entropy calculation
        return unique * this.log2(256);
    }

    // Validate string lengths
    public static stringLength(string: string, minimum: number, maximum: number): boolean {
        // Get length of string
        var length = string.length;

        // Check if it meets the minimum/maximum
        if (length < minimum || length > maximum) {
            return false;
        }

        // If it passes both return true
        return true;
    }

    // Validate email address formats
    public static validateEmail(email: string): boolean {
        // RFC compliant e-mail address regex
        var re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,48})+$/;

        // Test it on the email var which'll return a boolean
        return re.test(email);
    }

    // Calculate the time that has elapsed since a certain data (doesn't take leap years in account).
    public static timeElapsed(timestamp: number, append: string = ' ago', none: string = 'Just now'): string {
        // Subtract the entered timestamp from the current timestamp
        var time: number = this.epoch() - timestamp;

        // If the new timestamp is below 1 return a standard string
        if (time < 1) {
            return none;
        }

        // Times array
        var times: Object = {
            31536000: ['year', 'a'],
            2592000: ['month', 'a'],
            604800: ['week', 'a'],
            86400: ['day', 'a'],
            3600: ['hour', 'an'],
            60: ['minute', 'a'],
            1: ['second', 'a']
        };

        //
        var timeKeys: string[] = Object.keys(times).reverse();

        // Iterate over the times
        for (var i in timeKeys) {
            // Do a devision to check if the given timestamp fits in the current "type"
            var calc: number = time / parseInt(timeKeys[i]);

            // Check if we have a match
            if (calc >= 1) {
                // Round the number
                var display: number = Math.floor(calc);

                // Return the formatted string
                return (display === 1 ? times[timeKeys[i]][1] : display) + " " + times[timeKeys[i]][0] + (display === 1 ? '' : 's') + append;
            }
        }

        // If everything fails just return none
        return none;
    }
}

declare function unescape(a);
declare function escape(a);

// UTF-8 functions
class utf8 {
    // Encode a utf-8 string
    public static encode(string): string {
        return unescape(encodeURIComponent(string));
    }

    // Decode a utf-8 string
    public static decode(string): string {
        return decodeURIComponent(escape(string));
    }
}

// HTTP methods
enum HTTPMethods {
    GET,
    HEAD,
    POST,
    PUT,
    DELETE
}

// AJAX functions
class AJAX {
    // XMLHTTPRequest container
    private request: XMLHttpRequest;
    private callbacks: Object;
    private headers: Object;
    private url: string;
    private send: string = null;

    // Prepares the XMLHttpRequest and stuff
    constructor() {
        this.request = new XMLHttpRequest();
        this.callbacks = new Object();
        this.headers = new Object();
    }

    // Start
    public start(method: HTTPMethods): void {
        // Open the connection
        this.request.open(HTTPMethods[method], this.url, true);

        // Set headers
        this.prepareHeaders();

        // Watch the ready state
        this.request.onreadystatechange = () => {
            // Only invoke when complete
            if (this.request.readyState === 4) {
                // Check if a callback if present
                if ((typeof this.callbacks[this.request.status]).toLowerCase() === 'function') {
                    this.callbacks[this.request.status]();
                } else { // Else check if there's a generic fallback present
                    if ((typeof this.callbacks['0']).toLowerCase() === 'function') {
                        // Call that
                        this.callbacks['0']();
                    }
                }
            }
        }

        this.request.send(this.send);
    }

    // Stop
    public stop(): void {
        this.request = null;
    }

    // Add post data
    public setSend(data: Object): void {
        // Storage array
        var store: Array<string> = new Array<string>();

        // Iterate over the object and them in the array with an equals sign inbetween
        for (var item in data) {
            store.push(encodeURIComponent(item) + "=" + encodeURIComponent(data[item]));
        }

        // Assign to send
        this.send = store.join('&');
    }

    // Set raw post
    public setRawSend(data: string) {
        this.send = data;
    }

    // Get response
    public response(): string {
        return this.request.responseText;
    }

    // Set charset
    public contentType(type: string, charset: string = null): void {
        this.addHeader('Content-Type', type + ';charset=' + (charset ? charset : 'utf-8'));
    }

    // Add a header
    public addHeader(name: string, value: string): void {
        // Attempt to remove a previous instance
        this.removeHeader(name);

        // Add the new header
        this.headers[name] = value;
    }

    // Remove a header
    public removeHeader(name: string): void {
        if ((typeof this.headers[name]).toLowerCase() !== 'undefined') {
            delete this.headers[name];
        }
    }

    // Prepare request headers
    public prepareHeaders(): void {
        for (var header in this.headers) {
            this.request.setRequestHeader(header, this.headers[header]);
        }
    }

    // Adds a callback
    public addCallback(status: number, callback: Function): void {
        // Attempt to remove previous instances
        this.removeCallback(status);

        // Add the new callback
        this.callbacks[status] = callback;
    }

    // Delete a callback
    public removeCallback(status: number): void {
        // Delete the callback if present
        if ((typeof this.callbacks[status]).toLowerCase() === 'function') {
            delete this.callbacks[status];
        }
    }

    // Sets the URL
    public setUrl(url: string): void {
        this.url = url;
    }
}
