/*
 * Sakura Yuuno JavaScript
 */

// Get or set cookies
function cookieData(action, name, data) {

    switch(action) {

        case 'get':
            return (result = new RegExp('(^|; )' + encodeURIComponent(name) + '=([^;]*)').exec(document.cookie)) ? result[2] : '';

        case 'set':
            document.cookie = name + '=' + data;
            return;

        default:
            return;

    }

}

// Toggling the menu on mobile devices
function mobileMenu(mode) {

    // Assign the elements to variables
    var ucpMenuBtn = document.getElementById('navMenuSite');
    var navMenuBtn = document.getElementById('navMenuUser');
    var mobMenuBtn = document.getElementById('mobileNavToggle');

    // Open or close the menus depending on the values
    if(mode) {

        // Alter the classes
        ucpMenuBtn.className = ucpMenuBtn.className + ' menu-hid';
        navMenuBtn.className = navMenuBtn.className + ' menu-hid';

        // Update the button
        mobMenuBtn.innerHTML = 'Close Menu';
        mobMenuBtn.setAttribute('onclick', 'mobileMenu(false);');

    } else {

        // Alter the classes
        ucpMenuBtn.className = ucpMenuBtn.className.replace(' menu-hid', '');
        navMenuBtn.className = navMenuBtn.className.replace(' menu-hid', '');

        // Update the button
        mobMenuBtn.innerHTML = 'Open Menu';
        mobMenuBtn.setAttribute('onclick', 'mobileMenu(true);');

    }

}

// Event watcher for the scroll-to-top button
window.onscroll = function() {

    // Assign the gotop button to a variable
    var gotop = document.getElementById('gotop');

    // If the vertical offset of the page is below 112px (just below the header) keep the button hidden
    if(this.pageYOffset < 112) {
        if(gotop.getAttribute('class').indexOf('hidden') < 0)
            gotop.setAttribute('class', gotop.getAttribute('class') + ' hidden');
    } else if(this.pageYOffset > 112) // Else show it
        gotop.setAttribute('class', gotop.getAttribute('class').replace(' hidden', ''));

};

// Get the current unix/epoch timestamp
function epochTime() {

    return Math.floor(Date.now() / 1000);

}

/*
"Delayed" for now (not really any use for it atm)
function notification(id, content, sound) {
    $('.notifications').hide().append('<div id="notif'+id+'">'+content+'</div>').fadeIn('slow');
    
    if(sound) {
        var sound = document.getElementById('notifsnd');
        
        sound.volume = 1.0;
        sound.currentTime = 0;
        sound.play();
    }
    
    window.setTimeout(function() {
        $('#notif'+id).fadeOut('slow',function() {
            $('#notif'+id).remove();
        });
    }, 2500);
    
    return true;
}

function notificationRequest() {
    var notificationURL = 'http://sys.flashii.net/udata?notifications';
    
    if(window.XMLHttpRequest) {
        request = new XMLHttpRequest();
    } else if(window.ActiveXObject) {
        try {
            request = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch(e) {
            try {
                request = new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch(e) {}
        }
    }
    
    if(!request) {
        return false;
    }
    
    request.onreadystatechange = function() {
        if(request.readyState === 4) {
            if(request.status === 200) {
                var notifGet = JSON.parse(request.responseText);
                
                notifGet[0].notifications.forEach(function(data) {
                    if(data.time >= epochTime()+7 && !$('#notif'+epochTime()).length) {
                        notification(data.id, data.content, true);
                    }
                });
                
                //if(epochTime() <= epochTime()+1 && !$('#notif'+epochTime()).length) {
                //    notification(epochTime(), notifGet[0].notifications[0].notif1, true);
                //}
            } else {
                notification('ERROR'+epochTime(), 'Error: Was not able to get notification data.',false);
            }
        }
    }
    request.open('GET', notificationURL);
    request.send();
    setTimeout(notificationRequest, 5000);
}*/

// Donate page specific features
function donatePage(id) {

    // Get the featureBoxDesc elements
    var featureBoxDesc = document.getElementsByClassName('featureBoxDesc');

    // If an id wasn't set assume that we're doing initialisation
    if(!id) {

        // Go over every element and add donateClosed to the end of the class
        for(var i = 0; i < featureBoxDesc.length; i++)
            featureBoxDesc[i].className = featureBoxDesc[i].className + ' donateClosed';

        // Then stop the execution of the function
        return;

    }

    // Get the second child of the featureBox (which is the description)
    var featureBox = document.getElementById(id).children[1];

    // Search for donateOpened in the class and if found...
    if(featureBox.className.search('donateOpened') > 0) {

        // replace it with nothing and add donateClosed to the class
        featureBox.className = featureBox.className.replace(' donateOpened', '');
        featureBox.className = featureBox.className + ' donateClosed';

    } else {

        // Else do the opposite of what was described above
        featureBox.className = featureBox.className.replace(' donateClosed', '');
        featureBox.className = featureBox.className + ' donateOpened';

    }

}

// Removing all elements with a certain class
function removeClass(className) {

    // Get the elements
    var objectCont = document.getElementsByClassName(className);

    // Use a while loop instead of a for loop (Array keys change) to remove each element
    while(objectCont.length > 0)
        objectCont[0].parentNode.removeChild(objectCont[0]);

}

// Removing an element by ID
function removeId(id) {

    // Get the element
    var objectCont = document.getElementById(id);

    // If the element exists use the parent node to remove it
    if(typeof(objectCont) != "undefined" && objectCont !== null)
        objectCont.parentNode.removeChild(objectCont);

}

// Show the full-page busy window
function ajaxBusyView(show, message, type) {

    // Get elements
    var busyCont    = document.getElementById('ajaxBusy');
    var busyStat    = document.getElementById('ajaxStatus');
    var busyAnim    = document.getElementById('ajaxAnimate');
    var pageContent = document.getElementById('contentwrapper');

    // Select the proper icon
    switch(type) {

        default:
        case 'busy':
            var busyAnimIco = 'fa fa-refresh fa-spin fa-4x';
            break;
        case 'ok':
            var busyAnimIco = 'fa fa-check fa-4x';
            break;
        case 'fail':
            var busyAnimIco = 'fa fa-remove fa-4x';
            break;

    }

    // If requested to show the window build it
    if(show) {

        // Make sure it doesn't exist already
        if(busyCont == null) {

            // Container
            var createBusyCont = document.createElement('div');
            createBusyCont.className = 'ajax-busy';
            createBusyCont.setAttribute('id', 'ajaxBusy');

            // Inner box
            var createBusyInner = document.createElement('div');
            createBusyInner.className = 'ajax-inner';
            createBusyCont.appendChild(createBusyInner);

            // Action description
            var createBusyMsg = document.createElement('h2');
            createBusyMsg.setAttribute('id', 'ajaxStatus');
            createBusyInner.appendChild(createBusyMsg);

            // FontAwesome icon
            var createBusySpin = document.createElement('div');
            createBusySpin.setAttribute('id', 'ajaxAnimate');
            createBusyInner.appendChild(createBusySpin);

            // Append the element to the actual page
            pageContent.appendChild(createBusyCont);

            // Reassign the previously assigned variables
            busyCont = document.getElementById('ajaxBusy');
            busyStat = document.getElementById('ajaxStatus');
            busyAnim = document.getElementById('ajaxAnimate');

        } // If the container already exists just continue and update the elements

        // Alter the icon
        busyAnim.className = busyAnimIco;

        // Change the message
        busyStat.innerHTML = (message == null ? 'Unknown' : message)

    } else { // If show is false remove the element...

        // ...but just do nothing if the container doesn't exist
        if(busyCont != null) {

            // Create the fadeout with a 10ms interval
            var fadeOut = setInterval(function() {

                // Set an opacity if it doesn't exist yet
                if(busyCont.style.opacity == null || busyCont.style.opacity == "")
                    busyCont.style.opacity = 1;

                // If the value isn't 0 yet start subtract .1 from the opacity
                if(busyCont.style.opacity > 0) {

                    busyCont.style.opacity = busyCont.style.opacity - .1;

                } else { // When we've reached 0 remove the container element and clear the fadeout interval

                    removeId('ajaxBusy');
                    clearInterval(fadeOut);

                }

            }, 10);

        }

    }

}

// Making a post request using AJAX
function ajaxPost(url, data) {

    // Create a new XMLHttpRequest
    var req = new XMLHttpRequest();

    // Open a post request
    req.open("POST", url, false);

    // Set the request header to a form
    req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    // Combine name and value with an = inbetween
    var query = [];
    for(var i in data)
        query.push(encodeURIComponent(i) +"="+ encodeURIComponent(data[i]));

    // Join the array and submit the request
    req.send(query.join("&"));

    // If the HTTP resonse was 200 return the page
    if(req.status === 200)
        return req.responseText;
    else // Else return nothing
        return "";

}

// Quickly building a form for god knows what reason
function generateForm(formId, formAttr, formData, appendTo) {

    // Create form elements and assign ID
    var form = document.createElement('form');
    form.setAttribute('id', formId);

    // Set additional attributes
    if(formAttr != null) {
        for(var i in formAttr)
            form.setAttribute(i, formAttr[i]);
    }

    // Generate input elements
    for(var i in formData) {
        var disposableVar = document.createElement('input');
        disposableVar.setAttribute('type', 'hidden');
        disposableVar.setAttribute('name', i);
        disposableVar.setAttribute('value', formData[i]);
        form.appendChild(disposableVar);
    }

    // Append to another element if requested
    if(appendTo != null)
        document.getElementById(appendTo).appendChild(form);

    // Return the completed form
    return form;

}

// Enter substitute
function formEnterCatch(key, id) {

    // 13 == Enter
    if(key.which == 13) {

        // Submit the form
        document.getElementById(id).click();

        // Return true if yeah
        return true;

    }

    // Return false if not
    return false;

}

// Submitting a form using an AJAX POST request
function submitPost(formId, busyView, msg) {

    // If requested display the busy thing
    if(busyView)
        ajaxBusyView(true, msg, 'busy');

    // Get form data
    var form = document.getElementById(formId);

    // Make sure the form id was proper and if not report an error
    if(form == null) {
        if(busyView) {
            ajaxBusyView(true, 'Invalid Form ID, contact the administrator.');
            setTimeout(function(){ajaxBusyView(false);}, 2000);
        }
        return;
    }

    // Make an object for the request parts
    var requestParts = new Object();

    // Get all children with a name attribute
    var children = form.querySelectorAll('[name]');

    // Sort children and make them ready for submission
    for(var i in children) {

        if(typeof children[i] == 'object')
            requestParts[children[i].name] = ((typeof children[i].type !== "undefined" && children[i].type.toLowerCase() == "checkbox") ? children[i].checked : children[i].value);

    }

    // Submit the AJAX request
    var request = ajaxPost(form.action, requestParts).split('|');

    // If using the busy view thing update the text displayed to the return of the request
    if(busyView)
        ajaxBusyView(true, request[1], (request[2] == '1' ? 'ok' : 'fail'));

    setTimeout(function(){
        if(busyView)
            ajaxBusyView(false);

        if(request[2] == '1')
            window.location = request[3];
    }, 2000); 

    return;

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
    if(position < 0 || position > 100)
        position = 0;

    // Do the first maths
    position = (position / (dir ? 25 : 20)) - 2.5;

    // If the negative flag is set inverse the number
    if(neg)
        position = -position;

    // Subtract another 2.5 to make the element not go all over the place
    position = position - 2.5; 

    // Return the proper position value
    return position;

}
