/*
 * Sakura Misaki JavaScript
 */

// Get or set cookies
function cookieData(action, name, data) {
    switch(action) {
        case 'get':
            return (result = new RegExp('(^|; )' + encodeURIComponent(name) + '=([^;]*)').exec(document.cookie)) ? result[2] : '';

        case 'set':
            document.cookie = name + '=' + data;
            return null;

        default:
            return null;
    }
}

// Initialising the element parallax functionality
function initialiseParallax(id) {
    // Assign the element to a variable
    var parallax = document.getElementById(id);

    // Set proper position values
    parallax.style.top      = '-2.5px';
    parallax.style.bottom   = '-2.5px';
    parallax.style.left     = '-2.5px';
    parallax.style.right    = '-2.5px';

    // Add the event listener to the body element
    document.addEventListener("mousemove", function(e) {
        // Alter the position of the parallaxed element
        parallax.style.top      = convertParallaxPositionValue(e.clientY, true, false)  + 'px';
        parallax.style.bottom   = convertParallaxPositionValue(e.clientY, true, true)   + 'px';
        parallax.style.left     = convertParallaxPositionValue(e.clientX, false, false) + 'px';
        parallax.style.right    = convertParallaxPositionValue(e.clientX, false, true)  + 'px';
    });
}

// Converting the position value of the mouseover to a pixel value
function convertParallaxPositionValue(pos, dir, neg) {
    // Get the body element
    var body = document.getElementsByTagName('body')[0];

    // Get percentage of current position
    var position = (pos / (dir ? body.clientHeight : body.clientWidth)) * 100;

    // If someone decided to fuck with the inputs reset it to 0%
    if(position < 0 || position > 100) {
        position = 0;
    }

    // Do the first maths
    position = (position / (dir ? 25 : 20)) - 2.5;

    // If the negative flag is set inverse the number
    if(neg) {
        position = -position;
    }

    // Subtract another 2.5 to make the element not go all over the place
    position = position - 2.5;

    // Return the proper position value
    return position;
}

// Menu bar
window.addEventListener("scroll", function(e) {
    if(window.scrollY > 11) {
        var wrapper = document.getElementById('wrapper');
        var navbar = document.getElementById('navigation');
        wrapper.className = 'navFloat';
        navbar.className = 'floating';
    } else {
        var wrapper = document.getElementById('wrapper');
        var navbar = document.getElementById('navigation');
        wrapper.className = null;
        navbar.className = null;
    }
});
