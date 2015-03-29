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

var RecaptchaOptions = {
    theme : 'custom',
    custom_theme_widget: 'recaptcha_widget'
};

function switch_text(type) {
    var responseField = document.getElementById('recaptcha_response_field');

    if(type == "audio")
        responseField.setAttribute('placeholder', 'Enter the words you hear');
    else if(type == "image")
        responseField.setAttribute('placeholder', 'Enter the words above');
    else
        responseField.setAttribute('placeholder', 'undefined rolls undefined and gets undefined');
}
