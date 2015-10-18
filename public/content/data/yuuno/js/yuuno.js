/*
 * Sakura Yuuno JavaScript
 */

// Get or set cookies
function cookieData(action, name, data) {

    switch(action) {

        case 'get':
            return (result = new RegExp('(^|; )' + encodeURIComponent(name) + '=([^;]*)').exec(document.cookie)) ? result[2] : '';

        case 'set':
            document.cookie = name + '=' + data + '; path=/';
            return null;

        default:
            return null;

    }

}

// Get the current unix/epoch timestamp
function epochTime() {

    return Math.floor(Date.now() / 1000);

}

// Create a notification box
function notifyUI(content) {

    // Grab the container and create an ID
    var container   = document.getElementById('notifications');
    var identifier  = 'sakura-notification-' + Date.now();

    // Create the notification element and children
    var notif           = document.createElement('div');
    var notifIcon       = document.createElement('div');
    var notifContent    = document.createElement('div');
    var notifTitle      = document.createElement('div');
    var notifText       = document.createElement('div');
    var notifClose      = document.createElement('div');
    var notifCloseIcon  = document.createElement('div');
    var notifClear      = document.createElement('div');
    var iconCont;

    // Add ID and class on notification container
    notif.className = 'notification-enter';
    notif.setAttribute('id', identifier);

    // Add icon
    notifIcon   .className = 'notification-icon';
    if(content.img.substring(0, 5) == "FONT:") {

        iconCont = document.createElement('div');
        iconCont.className = 'font-icon fa ' + content.img.replace('FONT:', '') + ' fa-4x';

    } else {

        iconCont = document.createElement('img');
        iconCont.setAttribute('alt', identifier);
        iconCont.setAttribute('src', content.img);

    }
    notifIcon   .appendChild(iconCont);
    notif       .appendChild(notifIcon);

    // Add content
    var notifTitleNode  = document.createTextNode(content.title);
    var notifTextNode   = document.createTextNode(content.text);
    notifContent    .className = 'notification-content';
    notifTitle      .className = 'notification-title';
    notifText       .className = 'notification-text';
    notifTitle      .appendChild(notifTitleNode);
    notifText       .appendChild(notifTextNode);
    if(content.link) {

        notif       .setAttribute('sakurahref', content.link);
        notifContent.setAttribute('onclick',    content.link.substring(0, 11) == 'javascript:' ? content.link.substring(11) : 'notifyOpen(this.parentNode.id);');

    }
    notifContent    .appendChild(notifTitle);
    notifContent    .appendChild(notifText);
    notif           .appendChild(notifContent);

    // Add close button
    notifClose  .className = 'notification-close';
    notifClose  .setAttribute('onclick', 'notifyClose(this.parentNode.id);');
    notifClose  .appendChild(notifCloseIcon);
    notif       .appendChild(notifClose);

    // Add .clear
    notifClear  .className = 'clear';
    notif       .appendChild(notifClear);

    // Append the notification to the document so it actually shows up to the user also add the link
    container.appendChild(notif);

    // Play sound if requested
    if(content.sound > 0) {

        // Create sound element and mp3 and ogg sources
        var sound       = document.createElement('audio');
        var soundMP3    = document.createElement('source');
        var soundOGG    = document.createElement('source');

        // Assign the proper attributes to the sources
        soundMP3.setAttribute('src',    '//' + sakuraVars.content_path + '/sounds/notify.mp3');
        soundMP3.setAttribute('type',   'audio/mp3');
        soundOGG.setAttribute('src',    '//' + sakuraVars.content_path + '/sounds/notify.ogg');
        soundOGG.setAttribute('type',   'audio/ogg');

        // Append the children
        sound.appendChild(soundMP3);
        sound.appendChild(soundOGG);

        // Play the sound
        sound.play();

    }

    // If keepalive is 0 keep the notification open "forever" (until the user closes it or changes the page)
    if(content.timeout > 0) {

        // Set set a timeout and execute notifyClose() after amount of milliseconds specified
        setTimeout(function() {

            // Use the later defined notifyClose function
            notifyClose(identifier);

        }, content.timeout);

    }

}

// Closing a notification box
function notifyClose(id) {

    // Get the element and assign it to a variable
    var element = document.getElementById(id);

    // Do the animation
    element.className = 'notification-exit';

    // Remove the element after 500 milliseconds (animation takes 400)
    setTimeout(function() {

        // Use the later defined removeId function
        removeId(id);

    }, 410);

}

// Opening a link to a notifcated thing (what even)
function notifyOpen(id) {

    var sakuraHref = document.getElementById(id).getAttribute('sakurahref');

    if(typeof sakuraHref !== 'undefined') {

        window.location = sakuraHref;

    }

}

// Request notifications
function notifyRequest(session) {

    // Check if the document isn't hidden
    if(document.hidden) {

        return;

    }

    // Create XMLHttpRequest and notifyURL
    var notificationWatcher = new XMLHttpRequest();
    var notifyURL           = '//' + sakuraVars.urlMain + '/settings.php?request-notifications=true&time=' + epochTime() + '&session=' + session;

    // Wait for the ready state to change
    notificationWatcher.onreadystatechange = function() {

        // Wait for it to reach the "complete" stage
        if(notificationWatcher.readyState === 4) {

            // Continue if the HTTP return was 200
            if(notificationWatcher.status === 200) {

                // Assign the JSON parsed content to a variable
                var notifyGet = JSON.parse(notificationWatcher.responseText);

                // If nothing was set stop
                if(typeof notifyGet == 'undefined') {

                    // Tell the user something went wrong...
                    notifyUI({
                        "title":    "An error occurred!",
                        "text":     "If this problem persists please report this to the administrator.",
                        "img":      "FONT:fa-exclamation-triangle",
                        "timeout":  60000,
                        "sound":    false
                    });

                    // ...then prevent the function from contiuing
                    return;

                }

                // Go over every return notification and pass the object to it
                for(var notifyID in notifyGet) {

                    notifyUI(notifyGet[notifyID]);

                }

            } else {

                // ELse tell the user there was an internal server error...
                notifyUI({
                    "title":    "An internal server error occurred!",
                    "text":     "If this problem persists please report this to the administrator.",
                    "img":      "FONT:fa-chain-broken",
                    "timeout":  60000,
                    "sound":    false
                });

            }

        }

    };

    // Make the request
    notificationWatcher.open('GET', notifyURL, true);
    notificationWatcher.send();

}

// Removing all elements with a certain class
function removeClass(className) {

    // Get the elements
    var objectCont = document.getElementsByClassName(className);

    // Use a while loop instead of a for loop (Array keys change) to remove each element
    while(objectCont.length > 0) {

        objectCont[0].parentNode.removeChild(objectCont[0]);

    }

}

// Removing an element by ID
function removeId(id) {

    // Get the element
    var objectCont = document.getElementById(id);

    // If the element exists use the parent node to remove it
    if(typeof(objectCont) != "undefined" && objectCont !== null) {

        objectCont.parentNode.removeChild(objectCont);

    }

}

// Show the full-page busy window
function ajaxBusyView(show, message, type) {

    // Get elements
    var busyCont    = document.getElementById('ajaxBusy');
    var busyStat    = document.getElementById('ajaxStatus');
    var busyAnim    = document.getElementById('ajaxAnimate');
    var pageContent = document.getElementById('contentwrapper');
    var busyAnimIco;

    // Select the proper icon
    switch(type) {

        case 'ok':
            busyAnimIco = 'fa fa-check fa-4x';
            break;
        case 'fail':
            busyAnimIco = 'fa fa-remove fa-4x';
            break;
        case 'busy':
        default:
            busyAnimIco = 'fa fa-refresh fa-spin fa-4x';
            break;

    }

    // If requested to show the window build it
    if(show) {

        // Make sure it doesn't exist already
        if(busyCont === null) {

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
        busyStat.innerHTML = (message === null ? 'Unknown' : message);

    } else { // If show is false remove the element...

        // ...but just do nothing if the container doesn't exist
        if(busyCont !== null) {

            // Create the fadeout with a 10ms interval
            var fadeOut = setInterval(function() {

                // Set an opacity if it doesn't exist yet
                if(busyCont.style.opacity === null || busyCont.style.opacity === "") {

                    busyCont.style.opacity = 1;

                }

                // If the value isn't 0 yet start subtract .1 from the opacity
                if(busyCont.style.opacity > 0) {

                    busyCont.style.opacity = busyCont.style.opacity - 0.1;

                } else { // When we've reached 0 remove the container element and clear the fadeout interval

                    removeId('ajaxBusy');
                    clearInterval(fadeOut);

                }

            }, 10);

        }

    }

}

// Making a post request using AJAX
function ajaxPost(url, data, callback) {

    // Combine name and value with an = inbetween
    var query = [];
    for(var i in data) {

        query.push(encodeURIComponent(i) +"="+ encodeURIComponent(data[i]));

    }

    // Join the array
    query = query.join("&");

    // Create XMLHttpRequest
    var request = new XMLHttpRequest();

    // Open a post request
    request.open('POST', url, true);

    // Wait for the readiness to change
    request.onreadystatechange = function() {

        // Wait for completion
        if(request.readyState === 4) {

            if(request.status === 200) {

                callback.call(request.responseText);

            } else {

                ajaxBusyView(false);

                notifyUI({
                    "title":    "An internal server error occurred!",
                    "text":     "If this problem persists please report this to the administrator.",
                    "img":      "FONT:fa-chain-broken",
                    "timeout":  60000,
                    "sound":    false
                });

                return null;

            }

        }

    };

    // Set headers
    request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    // Submit the request
    request.send(query);

}

// Convert href attribute to an object
function prepareAjaxLink(linkId, callback, attrs) {

    // Get the elements
    var link = typeof linkId === 'object' ? linkId : document.getElementById(linkId);

    // Catch null
    if(link == null) {

        return;

    }

    // Get the href value
    var hrefRaw = link.attributes.href.value;

    // Get the action path
    var action = hrefRaw.split('?')[0];

    // Split the request variables
    var variablesNotSplit = hrefRaw.split('?')[1].split('&');

    // Create variables object
    var variables = {};

    // Split the name and values of the variables
    for(var key in variablesNotSplit) {

        // Split name and value
        var newVar = variablesNotSplit[key].split('=');

        // Push it into the object
        variables[newVar[0]] = newVar[1];

    }

    // Add ajax=true
    variables['ajax'] = true;

    // Update link attributes
    link.setAttribute('href',       'javascript:void(0);');
    link.setAttribute('onclick',    callback +'(\''+ action +'\', JSON.parse(\''+ JSON.stringify(variables) +'\')'+ (typeof attrs != 'undefined' ? attrs : '') +');');

}

function prepareAjaxForm(formId, message, resetCaptchaOnFailure) {

    // Get the form
    var form = document.getElementById(formId);

    // Create the AJAX form input
    var createHidden = document.createElement('input');

    // Set the attributes
    createHidden.setAttribute('name', 'ajax');
    createHidden.setAttribute('value', 'true');
    createHidden.setAttribute('type', 'hidden');
    form.appendChild(createHidden);

    // Update form
    form.setAttribute('onsubmit', 'submitPost(\''+ form.action +'\', formToObject(\''+ formId +'\'), true, \''+ (message ? message : 'Please wait...') +'\', '+ (resetCaptchaOnFailure ? 'true' : 'false') +');');
    form.setAttribute('action', 'javascript:void(0);');

}

// Convert form to an object
function formToObject(formId) {

    // Get form data
    var form = document.getElementById(formId);

    // Make an object for the request parts
    var requestParts = {};

    // Get all children with a name attribute
    var children = form.querySelectorAll('[name]');

    // Sort children and make them ready for submission
    for(var i in children) {

        if(typeof children[i] == 'object') {

            requestParts[children[i].name] = ((typeof children[i].type !== "undefined" && children[i].type.toLowerCase() == "checkbox") ? (children[i].checked ? 1 : 0) : children[i].value);

        }

    }

    // Return the object
    return requestParts;

}

// Quickly building a form for god knows what reason
function generateForm(formId, formAttr, formData, appendTo) {

    // Create form elements and assign ID
    var i;
    var form = document.createElement('form');
    form.setAttribute('id', formId);

    // Set additional attributes
    if(formAttr !== null) {

        for(i in formAttr) {

            form.setAttribute(i, formAttr[i]);

        }

    }

    // Generate input elements
    for(i in formData) {

        var disposableVar = document.createElement('input');
        disposableVar.setAttribute('type', 'hidden');
        disposableVar.setAttribute('name', i);
        disposableVar.setAttribute('value', formData[i]);
        form.appendChild(disposableVar);

    }

    // Append to another element if requested
    if(appendTo !== null) {

        document.getElementById(appendTo).appendChild(form);

    }

    // Return the completed form
    return form;

}

// Submitting a form using an AJAX POST request
function submitPost(action, requestParts, busyView, msg, resetCaptchaOnFailure) {

    // If requested display the busy thing
    if(busyView) {

        ajaxBusyView(true, msg, 'busy');

    }

    // Submit the AJAX request
    var request = ajaxPost(action, requestParts, function() {

        submitPostHandler(this, busyView, resetCaptchaOnFailure);

    });

}

// Submitting a form using an AJAX POST request
function submitPostHandler(result, busyView, resetCaptchaOnFailure) {

    // Split result
    result = result.split('|');

    // If using the busy view thing update the text displayed to the return of the request
    if(busyView) {

        ajaxBusyView(true, result[0], (result[1] == '1' ? 'ok' : 'fail'));

    }

    // If request reset the recaptcha on failure
    if(resetCaptchaOnFailure && result[1] != '1' && sakuraVars.recaptchaEnabled != '0') {

        grecaptcha.reset();

    }

    setTimeout(function(){

        if(busyView) {

            ajaxBusyView(false);

        }

        if(result[1] == '1') {

            window.location = result[2];

        }

    }, 2000);

}

// Encode UTF-8
function utf8_encode(str) {

    return unescape(encodeURIComponent(str));

}

// Decode UTF-8
function utf8_decode(str) {

    return decodeURIComponent(escape(str));

}

// Calculate the amount of unique characters in a string
function uniqueChars(str) {

    // Create storage array and count var
    var usedChars   = [];
    var count       = 0;

    // Count the amount of unique characters
    for(var i = 0; i < str.length; i++) {

        // Check if we already counted this character
        if(usedChars.indexOf(str[i]) == -1) {

            // Push the character into the used array
            usedChars.push(str[i]);

            // Up the count
            count++;

        }

    }

    // Return the count
    return count;

}

// Alternative for Math.log2() since it's still experimental
function log2(num) {

    return Math.log(num) / Math.log(2);

}

// Calculate password entropy
function pwdEntropy(pwd) {

    // Decode utf-8 chars
    pwd = utf8_decode(pwd);

    // Count the amount of unique characters in the password and calculate the entropy
    return uniqueChars(pwd) * log2(256);

}

// Check if password is within the minimum entropy value
function checkPwdEntropy(pwd) {

    return (pwdEntropy(pwd) >= sakuraVars.minPwdEntropy);

}

// Check the length of a string
function checkStringLength(str, min, max) {

    // Get length of string
    var len = str.length;

    // Check if it meets the minimum
    if(len < min)
        return false;

    // Check if it meets the maximum
    if(len > max)
        return false;

    // If it passes both return true
    return true;

}

// Validate email address formats
function validateEmail(email) {

    // The regex
    var re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,48})+$/;
    // is of fix

    // Test it (which returns true or false)
    return re.test(email);

}

// Check registration variables
function registerVarCheck(id, mode, option) {

    // Get the element we're working with
    var input = document.getElementById(id);
    var check = null;

    // Use the proper mode
    switch(mode) {

        case 'confirmpw':
            option = document.getElementById(option);
            check = input.value === option.value;
            break;

        case 'password':
            check = checkPwdEntropy(input.value);
            break;

        case 'email':
            check = validateEmail(input.value);
            break;

        case 'username':
        default:
            check = checkStringLength(input.value, sakuraVars.minUserLen, sakuraVars.maxUserLen);
            break;

    }

    if(input.className.indexOf(check ? 'green' : 'red') < 0) {

        input.className = input.className + ' ' + (check ? 'green' : 'red');

    }

    if(input.className.indexOf(check ? 'red' : 'green') > 0) {

        input.className = input.className.replace(check ? 'red' : 'green', '');

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

// Smooth scrolling
function scrollToTop() {

    // Get the current position
    var windowY = window.pageYOffset - 100;

    // Move up
    window.scrollTo(0, windowY);

    // Keep executing this function till we're at the top
    if(windowY + 1 > 0) {

        setTimeout(function(){scrollToTop();}, 10);

    }

}

function replaceTag(tag) {
    return {'&': '&amp;', '<': '&lt;', '>': '&gt;'}[tag] || tag;
}

function safeTagsReplace(str) {
    return str.replace(/[&<>]/g, replaceTag);
}

function commentReply(id, session, category, action, avatar) {

    // Find subject post
    var replyingTo = document.getElementById('comment-' + id);

    // Check if it actually exists
    if(typeof replyingTo === 'undefined') {
        return false;
    }

    // Attempt to get previously created box
    var replyBox = document.getElementById('comment-reply-container-' + id);

    // Remove it if it already exists
    if(replyBox) {
        removeId('comment-reply-container-' + id);
        return false;
    }

    // Container
    var replyContainer = document.createElement('li');
    replyContainer.id = 'comment-reply-container-' + id;

    // Form
    var replyForm = document.createElement('form');
    replyForm.id = 'comment-reply-' + id;
    replyForm.action = action;
    replyForm.method = 'post';

    // Session
    var replyInput = document.createElement('input');
    replyInput.type = 'hidden';
    replyInput.name = 'session';
    replyInput.value = session;
    replyForm.appendChild(replyInput);

    // Category
    var replyInput = document.createElement('input');
    replyInput.type = 'hidden';
    replyInput.name = 'category';
    replyInput.value = category;
    replyForm.appendChild(replyInput);

    // Reply ID
    var replyInput = document.createElement('input');
    replyInput.type = 'hidden';
    replyInput.name = 'replyto';
    replyInput.value = id;
    replyForm.appendChild(replyInput);

    // Mode
    var replyInput = document.createElement('input');
    replyInput.type = 'hidden';
    replyInput.name = 'mode';
    replyInput.value = 'comment';
    replyForm.appendChild(replyInput);

    // Comment container
    var replyDiv = document.createElement('div');
    replyDiv.className = 'comment';

    // Avatar
    var replyAvatar = document.createElement('div');
    replyAvatar.className = 'comment-avatar';
    replyAvatar.style = 'background-image: url(' + avatar + ')';
    replyDiv.appendChild(replyAvatar);

    // Pointer
    var replyPoint = document.createElement('div');
    replyPoint.className = 'comment-pointer';
    replyDiv.appendChild(replyPoint);

    // Textarea
    var replyText = document.createElement('textarea');
    replyText.className = 'comment-content';
    replyText.name = 'comment';
    replyDiv.appendChild(replyText);

    // Submit
    var replySubmit = document.createElement('input');
    replySubmit.className = 'comment-submit';
    replySubmit.type = 'submit';
    replySubmit.name = 'submit';
    replySubmit.value = "\uf1d8";
    replyDiv.appendChild(replySubmit);

    // Append to form
    replyForm.appendChild(replyDiv);

    // Append form to container
    replyContainer.appendChild(replyForm);

    // Insert the HTML
    if(replyingTo.children[1].children.length > 0) {
        replyingTo.children[1].insertBefore(replyContainer, replyingTo.children[1].firstChild);
    } else {
        replyingTo.children[1].appendChild(replyContainer);
    }

    // Prepare AJAX submission
    prepareAjaxForm(replyForm.id, 'Replying...');

}

// Formatting money
Number.prototype.formatMoney = function(c, d, t) {

    var n = this,
        c = isNaN(c = Math.abs(c)) ? 2 : c,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        s = n < 0 ? "-" : "",
        i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;

    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");

};
