/*
 * Sakura Yuuno
 */
// Spawns a notification
function notifyUI(content) {
    // Grab the container and create an ID
    var cont = document.getElementById('notifications');
    var id = 'sakura-notification-' + Date.now();
    // Create the elements
    var alert = document.createElement('div');
    var aIcon = document.createElement('div');
    var aCont = document.createElement('div');
    var aTitle = document.createElement('div');
    var aText = document.createElement('div');
    var aClose = document.createElement('div');
    var aCIcon = document.createElement('div');
    var aClear = document.createElement('div');
    var aIconCont;
    // Add attributes to the main element
    alert.className = 'notification-enter';
    alert.id = id;
    // Add the icon
    if ((typeof content.img).toLowerCase() === 'undefined' && content.img == null && !(content.img.length > 1)) {
        aIconCont = document.createElement('div');
        aIconCont.className = 'font-icon fa fa-info fa-4x';
    }
    else if (content.img.substr(0, 5) == 'FONT:') {
        aIconCont = document.createElement('div');
        aIconCont.className = 'font-icon fa ' + content.img.replace('FONT:', '') + ' fa-4x';
    }
    else {
        aIconCont = document.createElement('img');
        aIconCont.alt = id;
        aIconCont.img = content.img;
    }
    aIcon.appendChild(aIconCont);
    aIcon.className = 'notification-icon';
    alert.appendChild(aIcon);
    // Add the content
    aCont.className = 'notification-content';
    aTitle.className = 'notification-title';
    aText.className = 'notifcation-text';
    aTitle.textContent = content.title;
    aText.textContent = content.text;
    // Check if a link exists and add if it does
    if ((typeof content.link).toLowerCase() !== 'undefined' && content.link !== null && content.link.length > 1) {
        alert.setAttribute('sakurahref', content.link);
        aCont.setAttribute('onclick', content.link.substr(0, 11) == 'javascript:' ? content.link.substring(11) : 'notifyOpen(this.parentNode.id);');
    }
    // Append stuff
    aCont.appendChild(aTitle);
    aCont.appendChild(aText);
    alert.appendChild(aCont);
    // Add the close button
    aClose.className = 'notification-close';
    aClose.setAttribute('onclick', 'notifyClose(this.parentNode.id);');
    aClose.appendChild(aCIcon);
    alert.appendChild(aClose);
    // Append the notification to the document
    cont.appendChild(alert);
    // Play sound if request
    if (content.sound) {
        // Create the elements
        var sound = document.createElement('audio');
        var mp3 = document.createElement('source');
        var ogg = document.createElement('source');
        // Assign attribs
        mp3.type = 'audio/mp3';
        ogg.type = 'audio/ogg';
        mp3.src = sakuraVars.content_path + '/sounds/notify.mp3';
        ogg.src = sakuraVars.content_path + '/sounds/notify.ogg';
        // Append
        sound.appendChild(mp3);
        sound.appendChild(ogg);
        // And play
        sound.play();
    }
    // If keepalive is 0 keep the notification open forever
    if (content.timeout > 0) {
        // Set a timeout and close after an amount
        setTimeout(function () {
            notifyClose(id);
        }, content.timeout);
    }
}
// Closing a notification
function notifyClose(id) {
    // Get the element
    var e = document.getElementById(id);
    // Add the animation
    e.className = 'notification-exit';
    // Remove after 410 ms
    setTimeout(function () {
        Sakura.removeById(id);
    }, 410);
}
// Opening an alerted link
function notifyOpen(id) {
    var sakuraHref = document.getElementById(id).getAttribute('sakurahref');
    if ((typeof sakuraHref).toLowerCase() !== 'undefined') {
        window.location.assign(sakuraHref);
    }
}
// Request notifications
function notifyRequest(session) {
    // Check if the document isn't hidden
    if (document.hidden) {
        return;
    }
    // Create AJAX object
    var get = new AJAX();
    get.setUrl('/settings.php?request-notifications=true&time=' + Sakura.epoch() + '&session=' + session);
    // Add callbacks
    get.addCallback(200, function () {
        // Assign the parsed JSON
        var data = JSON.parse(get.response());
        // Check if nothing went wrong
        if ((typeof data).toLowerCase() === 'undefined') {
            // Inform the user
            throw "No or invalid data was returned";
            // Stop
            return;
        }
        // Create an object for every notification
        for (var id in data) {
            notifyUI(data[id]);
        }
    });
    get.start(HTTPMethods.GET);
}
// Show the full page busy window
function ajaxBusyView(show, message, type) {
    if (message === void 0) { message = null; }
    if (type === void 0) { type = null; }
    // Get elements
    var cont = document.getElementById('ajaxBusy');
    var stat = document.getElementById('ajaxStatus');
    var anim = document.getElementById('ajaxAnimate');
    var body = document.getElementById('contentwrapper');
    var icon = 'fa fa-4x ';
    // Select the proper icon
    switch (type) {
        case 'ok':
            icon += 'fa-check';
            break;
        case 'fail':
            icon += 'fa-remove';
            break;
        case 'busy':
        default:
            icon += 'fa-refresh fa-spin';
            break;
    }
    // If request to show the window, build it
    if (show) {
        if ((typeof cont).toLowerCase() === 'undefined' || cont === null) {
            // Container
            var cCont = document.createElement('div');
            cCont.className = 'ajax-busy';
            cCont.id = 'ajaxBusy';
            // Inner
            var cInner = document.createElement('div');
            cInner.className = 'ajax-inner';
            cCont.appendChild(cInner);
            // Desc
            var cMsg = document.createElement('h2');
            cMsg.id = 'ajaxStatus';
            cInner.appendChild(cMsg);
            // Icon
            var cIco = document.createElement('div');
            cIco.id = 'ajaxAnimate';
            cInner.appendChild(cIco);
            // Append to document
            body.appendChild(cCont);
            // Reassign
            cont = document.getElementById('ajaxBusy');
            stat = document.getElementById('ajaxStatus');
            anim = document.getElementById('ajaxAnimate');
        }
        // Update the icon
        anim.className = icon;
        // Update the message
        stat.textContent = (message === null ? '' : message);
    }
    else {
        if (cont !== null) {
            var out = setInterval(function () {
                if (cont.style.opacity === null || cont.style.opacity === "") {
                    cont.style.opacity = "1";
                }
                // If the value isn't 0 yet subtract by .1
                if (parseInt(cont.style.opacity) > 0) {
                    cont.style.opacity = (parseInt(cont.style.opacity) - 0.1).toString();
                }
                else {
                    Sakura.removeById('ajaxBusy');
                    clearInterval(out);
                }
            }, 10);
        }
    }
}
// Making a post request using AJAX
function ajaxPost(url, data, callback) {
    // Create AJAX
    var request = new AJAX();
    // Set url
    request.setUrl(url);
    // Add callbacks
    request.addCallback(200, function () {
        callback.call(request.response());
    });
    request.addCallback(0, function () {
        ajaxBusyView(false);
        throw "POST Request failed";
    });
    // Add header
    request.addHeader('Content-Type', 'application/x-www-form-urlencoded');
    // Set the post data
    request.setSend(data);
    // Make the request
    request.start(HTTPMethods.POST);
    // Return the AJAX object
    return request;
}
// Convert a href attr to an object
function prepareAjaxLink(linkId, callback, attrs) {
    if (attrs === void 0) { attrs = null; }
    // Get element
    var link = (typeof linkId).toLowerCase() === 'object' ? linkId : document.getElementById(linkId);
    // Catch null
    if (link === null) {
        return;
    }
    // Get the raw HREF value
    var href = link.getAttribute('href');
    // Get the action
    var action = href.split('?')[0];
    // Split the request variables
    var varEarly = href.split('?')[1].split('&');
    // Create storage thing
    var variables = new Object();
    // Split them
    for (var k in varEarly) {
        // Split
        var newVar = varEarly[k].split('=');
        // Push
        variables[newVar[0]] = newVar[1];
    }
    // Add ajax=true
    variables['ajax'] = true;
    // Update link attributes
    link.setAttribute('href', 'javascript:void(0);');
    link.setAttribute('onclick', callback + '(\'' + action + '\', JSON.parse(\'' + JSON.stringify(variables) + '\')' + (typeof attrs != 'undefined' ? attrs : '') + ');');
}
// Prepare a form for an AJAX request
function prepareAjaxForm(formId, message, resetCaptcha) {
    if (resetCaptcha === void 0) { resetCaptcha = false; }
    // Get the form
    var form = document.getElementById(formId);
    // Create hidden ajax input
    var hide = document.createElement('input');
    // Set the attributes
    hide.name = 'ajax';
    hide.value = 'true';
    hide.type = 'hidden';
    form.appendChild(hide);
    // Update form
    form.setAttribute('onsubmit', 'submitPost(\'' + form.getAttribute('action') + '\', formToObject(\'' + formId + '\'), true, \'' + (message ? message : 'Please wait...') + '\', ' + (resetCaptcha ? 'true' : 'false') + ');');
    form.setAttribute('action', 'javascript:void(0);');
}
// Convert form to an object
function formToObject(formId) {
    // Get the form
    var form = document.getElementById(formId);
    // Make an object for the request parts
    var requestParts = new Object();
    // Get all the children with a name attr
    var children = form.querySelectorAll('[name]');
    // Sort the children and make them ready for submission
    for (var i in children) {
        if ((typeof children[i]).toLowerCase() === 'object') {
            requestParts[children[i].name] = ((typeof children[i].type !== "undefined" && children[i].type.toLowerCase() == "checkbox") ? (children[i].checked ? 1 : 0) : children[i].value);
        }
    }
    // Return the request parts
    return requestParts;
}
// Quickly building a form
function generateForm(formId, formAttr, formData, appendTo) {
    if (appendTo === void 0) { appendTo = null; }
    // Create form element
    var form = document.createElement('form');
    form.id = formId;
    // Set additional attrs
    for (var c in formAttr) {
        form.setAttribute(c, formAttr[c]);
    }
    // Set data
    for (var a in formData) {
        var b = document.createElement('input');
        b.type = 'hidden';
        b.name = a;
        b.value = formData[a];
        form.appendChild(b);
    }
    // Append to something if requested
    if (appendTo !== null) {
        document.getElementById(appendTo).appendChild(form);
    }
    return form;
}
// Submitting a post using AJAX
function submitPost(action, requestParts, busyView, msg, resetCaptcha) {
    // If requested display the busy thing
    if (busyView) {
        ajaxBusyView(true, msg, 'busy');
    }
    // Submit the AJAX
    var request = ajaxPost(action, requestParts, function () {
        submitPostHandler(request.response(), busyView, resetCaptcha);
    });
}
// Handling a submitted form using AJAX
function submitPostHandler(data, busyView, resetCaptcha) {
    // Split the result
    var result = data.split('|');
    // If using the bust view thing update the text displayed to the return of the request
    if (busyView) {
        ajaxBusyView(true, result[0], (result[1] == '1' ? 'ok' : 'fail'));
    }
    // Reset captcha
    if (resetCaptcha && result[1] != '1' && sakuraVars.recaptchaEnabled != '0') {
        grecaptcha.reset();
    }
    setTimeout(function () {
        if (busyView) {
            ajaxBusyView(false);
        }
        if (result[1] == '1') {
            window.location.assign(result[2]);
        }
    }, 2000);
}
// Check if a password is within the minimum entropy value
function checkPwdEntropy(pwd) {
    return (Sakura.entropy(pwd) >= sakuraVars.minPwdEntropy);
}
// Check registration variables
function registerVarCheck(id, mode, option) {
    if (option === void 0) { option = null; }
    // Get the element we're working with
    var input = document.getElementById(id);
    var check = null;
    // Use the proper mode
    switch (mode) {
        case 'confirmpw':
            option = document.getElementById(option);
            check = input.getAttribute('value') === option.value;
            break;
        case 'password':
            check = checkPwdEntropy(input.getAttribute('value'));
            break;
        case 'email':
            check = Sakura.validateEmail(input.getAttribute('value'));
            break;
        case 'username':
        default:
            check = Sakura.stringLength(input.getAttribute('value'), sakuraVars.minUserLen, sakuraVars.maxUserLen);
            break;
    }
    if (input.className.indexOf(check ? 'green' : 'red') < 0) {
        input.className = input.className + ' ' + (check ? 'green' : 'red');
    }
    if (input.className.indexOf(check ? 'red' : 'green') > 0) {
        input.className = input.className.replace(check ? 'red' : 'green', '');
    }
}
// Initialising the element parallax functionality
function initialiseParallax(id) {
    // Assign the element to a variable
    var parallax = document.getElementById(id);
    // Set proper position values
    parallax.style.top = '-2.5px';
    parallax.style.bottom = '-2.5px';
    parallax.style.left = '-2.5px';
    parallax.style.right = '-2.5px';
    // Add the event listener to the body element
    document.addEventListener("mousemove", function (e) {
        // Alter the position of the parallaxed element
        parallax.style.top = convertParallaxPositionValue(e.clientY, true, false) + 'px';
        parallax.style.bottom = convertParallaxPositionValue(e.clientY, true, true) + 'px';
        parallax.style.left = convertParallaxPositionValue(e.clientX, false, false) + 'px';
        parallax.style.right = convertParallaxPositionValue(e.clientX, false, true) + 'px';
    });
}
// Converting the position value of the mouseover to a pixel value
function convertParallaxPositionValue(pos, dir, neg) {
    // Get the body element
    var body = document.getElementsByTagName('body')[0];
    // Get percentage of current position
    var position = (pos / (dir ? body.clientHeight : body.clientWidth)) * 100;
    // If someone decided to fuck with the inputs reset it to 0%
    if (position < 0 || position > 100) {
        position = 0;
    }
    // Do the first maths
    position = (position / (dir ? 25 : 20)) - 2.5;
    // If the negative flag is set inverse the number
    if (neg) {
        position = -position;
    }
    // Subtract another 2.5 to make the element not go all over the place
    position = position - 2.5;
    // Return the proper position value
    return position;
}
// """"""""Smooth"""""""" scrolling
function scrollToTop() {
    // Get the current position
    var windowY = window.pageYOffset - 100;
    // Move up
    window.scrollTo(0, windowY);
    // Keep executing this function till we're at the top
    if (windowY + 1 > 0) {
        setTimeout(function () { scrollToTop(); }, 10);
    }
}
// Replace some special tags
function replaceTag(tag) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[tag] || tag;
}
// ^
function safeTagsReplace(str) {
    return str.replace(/[&<>]/g, replaceTag);
}
// Open a comment reply field
function commentReply(id, session, category, action, avatar) {
    // Find subject post
    var replyingTo = document.getElementById('comment-' + id);
    // Check if it actually exists
    if ((typeof replyingTo).toLowerCase() === 'undefined') {
        return;
    }
    // Attempt to get previously created box
    var replyBox = document.getElementById('comment-reply-container-' + id);
    // Remove it if it already exists
    if (replyBox) {
        Sakura.removeById('comment-reply-container-' + id);
        return;
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
    replyInput.value = id.toString();
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
    replyAvatar.style.backgroundImage = 'url(' + avatar + ')';
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
    if (replyingTo.children[1].children.length > 0) {
        replyingTo.children[1].insertBefore(replyContainer, replyingTo.children[1].firstChild);
    }
    else {
        replyingTo.children[1].appendChild(replyContainer);
    }
    // Prepare AJAX submission
    prepareAjaxForm(replyForm.id, 'Replying...');
}
// Inserting text into text box
// Borrowed from http://stackoverflow.com/questions/1064089/inserting-a-text-where-cursor-is-using-javascript-jquery (therefore not in Typescript format, fix this later)
function insertText(areaId, text) {
    var txtarea = document.getElementById(areaId);
    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
        "ff" : (document.selection ? "ie" : false));
    if (br == "ie") {
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart('character', -txtarea.value.length);
        strPos = range.text.length;
    }
    else if (br == "ff")
        strPos = txtarea.selectionStart;
    var front = (txtarea.value).substring(0, strPos);
    var back = (txtarea.value).substring(strPos, txtarea.value.length);
    txtarea.value = front + text + back;
    strPos = strPos + text.length;
    if (br == "ie") {
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart('character', -txtarea.value.length);
        range.moveStart('character', strPos);
        range.moveEnd('character', 0);
        range.select();
    }
    else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
    }
    txtarea.scrollTop = scrollPos;
}
// Inserting a bbcode
function insertBBcode(textarea, tag, arg) {
    if (arg === void 0) { arg = false; }
    var element = document.getElementById(textarea);
    var before = "[" + tag + (arg ? "=" : "") + "]";
    var after = "[/" + tag + "]";
    if (document.selection) {
        element.focus();
        var sel = document.selection.createRange();
        sel.text = before + sel.text + after;
        element.focus();
    }
    else if (element.selectionStart || element.selectionStart === 0) {
        var startPos = element.selectionStart;
        var endPos = element.selectionEnd;
        var scrollTop = element.scrollTop;
        element.value = element.value.substring(0, startPos) + before + element.value.substring(startPos, endPos) + after + element.value.substring(endPos, element.value.length);
        element.focus();
        element.selectionStart = startPos + before.length;
        element.selectionEnd = endPos + before.length;
        element.scrollTop = scrollTop;
    }
    else {
        element.value += before + after;
        element.focus();
    }
}
// Formatting money
Number.prototype.formatMoney = function (u, c, k) {
    var f = this, u = isNaN(u = Math.abs(u)) ? 2 : u, c = c == undefined ? "." : c, k = k == undefined ? "," : k, i = f < 0 ? "-" : "", n = parseInt(f = Math.abs(+f || 0).toFixed(u)) + "", g = (g = n.length) > 3 ? g % 3 : 0;
    return i + (g ? n.substr(0, g) + k : "") + n.substr(g).replace(/(\c{3})(?=\c)/g, "$1" + k) + (u ? c + Math.abs(f - n).toFixed(u).slice(2) : "");
};
