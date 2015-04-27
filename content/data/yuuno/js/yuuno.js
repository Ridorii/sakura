/*
 * Sakura Yuuno JavaScript
 */

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

function mobileMenu(mode) {
    var ucpMenuBtn = document.getElementById('navMenuSite');
    var navMenuBtn = document.getElementById('navMenuUser');
    var mobMenuBtn = document.getElementById('mobileNavToggle');

    if(mode) {
        ucpMenuBtn.className = ucpMenuBtn.className + ' menu-hid';
        navMenuBtn.className = navMenuBtn.className + ' menu-hid';
        
        mobMenuBtn.innerHTML = 'Close Menu';
        mobMenuBtn.setAttribute('onclick', 'mobileMenu(false);');
    } else {
        ucpMenuBtn.className = ucpMenuBtn.className.replace(' menu-hid', '');
        navMenuBtn.className = navMenuBtn.className.replace(' menu-hid', '');
        
        mobMenuBtn.innerHTML = 'Open Menu';
        mobMenuBtn.setAttribute('onclick', 'mobileMenu(true);');
    }
}

window.onscroll = function() {
    var gotop = document.getElementById('gotop');
    
        if(this.pageYOffset < 112) {
            if(gotop.getAttribute('class').indexOf('hidden') < 0)
                gotop.setAttribute('class', gotop.getAttribute('class') + ' hidden');
        } else if(this.pageYOffset > 112)
            gotop.setAttribute('class', gotop.getAttribute('class').replace(' hidden', ''));
};

function epochTime() {
    var time = Date.now();
    time = time / 1000;
    return Math.floor(time);
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

function donatePage(id) {
    var featureBoxDesc = document.getElementsByClassName('featureBoxDesc');

    if(!id) {
        for(var i = 0; i < featureBoxDesc.length; i++)
            featureBoxDesc[i].className = featureBoxDesc[i].className + ' donateClosed';
        
        return;
    }
    
    var featureBox = document.getElementById(id).children[1];
    
    if(featureBox.className.search('donateOpened') > 0) {
        featureBox.className = featureBox.className.replace(' donateOpened', '');
        featureBox.className = featureBox.className + ' donateClosed';
        
        return;
    } else {
        featureBox.className = featureBox.className.replace(' donateClosed', '');
        featureBox.className = featureBox.className + ' donateOpened';
        
        return;
    }
    
    return;
}

function removeClass(className) {
    var objectCont = document.getElementsByClassName(className);

    while(objectCont.length > 0)
        objectCont[0].parentNode.removeChild(objectCont[0]);
}

function removeId(id) {
    var objectCont = document.getElementById(id);

    if(typeof(objectCont) != "undefined" && objectCont !== null)
        objectCont.parentNode.removeChild(objectCont);
}

function ajaxBusyView(show, message, type) {
    var busyCont    = document.getElementById('ajaxBusy');
    var busyStat    = document.getElementById('ajaxStatus');
    var busyAnim    = document.getElementById('ajaxAnimate');
    var pageContent = document.getElementById('contentwrapper');

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

    if(show) {
        if(busyCont == null) {
            var createBusyCont = document.createElement('div');
            createBusyCont.className = 'ajax-busy';
            createBusyCont.setAttribute('id', 'ajaxBusy');

            var createBusyInner = document.createElement('div');
            createBusyInner.className = 'ajax-inner';
            createBusyCont.appendChild(createBusyInner);

            var createBusyMsg = document.createElement('h2');
            createBusyMsg.setAttribute('id', 'ajaxStatus');
            createBusyInner.appendChild(createBusyMsg);

            var createBusySpin = document.createElement('div');
            createBusySpin.setAttribute('id', 'ajaxAnimate');
            createBusyInner.appendChild(createBusySpin);

            pageContent.appendChild(createBusyCont);
            
            busyCont = document.getElementById('ajaxBusy');
            busyStat = document.getElementById('ajaxStatus');
            busyAnim = document.getElementById('ajaxAnimate');
        }

        busyAnim.className = busyAnimIco;

        if(message == null)
            busyStat.innerHTML = 'Please wait';
        else
            busyStat.innerHTML = message;
    } else {
        if(busyCont != null) {
            var fadeOut = setInterval(function() {
                if(busyCont.style.opacity == null || busyCont.style.opacity == "")
                    busyCont.style.opacity = 1;

                if(busyCont.style.opacity > 0) {
                    busyCont.style.opacity = busyCont.style.opacity - .1;
                } else {
                    removeId('ajaxBusy');
                    clearInterval(fadeOut);
                }
            }, 10);
        }
    }
}

function ajaxPost(url, data) {
    var req = new XMLHttpRequest();
    req.open("POST", url, false);
    req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    var query = [];
    for(var i in data)
        query.push(encodeURIComponent(i) +"="+ encodeURIComponent(data[i]));

    req.send(query.join("&"));

    if(req.status === 200)
        return req.responseText;
    else
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
