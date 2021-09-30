/*!
 * WmpciPop 1.0.0
 * https://astoundify.com
 *
 * Copyright (c) 2021 Astoundify
 */

(function (WmpciPop, undefined) {

    'use strict';

    // set default settings
    var settings = {

            // set default cover id
            coverId: 'wmpci-popup-cover',

            // duration (in days) before it pops up again
            expires: 1,

            // close if someone clicks an element with this class and prevent default action
            closeClassNoDefault: 'wmpci-popup-close',

            // close if someone clicks an element with this class and continue default action
            closeClassDefault: 'wmpci-close-go',

            // change the cookie name
            cookieName: '_Wmpci_Popup',

            // on popup open function callback
            onPopUpOpen: null,

            // on popup close function callback
            onPopUpClose: null,

            // hash to append to url to force display of popup
            forceHash: 'splash',

            // hash to append to url to delay popup for 1 day
            delayHash: 'go',

            // close if the user clicks escape
            closeOnEscape: true,

            // set an optional delay (in milliseconds) before showing the popup
            delay: 10000,

            // automatically close the popup after a set amount of time (in milliseconds)
            hideAfter: null
        },


        // grab the elements to be used
        $el = {
            html: document.getElementsByTagName('html')[0],
            cover: document.getElementById(settings.coverId),
            closeClassDefaultEls: document.querySelectorAll('.' + settings.closeClassDefault),
            closeClassNoDefaultEls: document.querySelectorAll('.' + settings.closeClassNoDefault)
        },


        /**
         * Helper methods
         */

        util = {

            hasClass: function(el, name) {
                return new RegExp('(\\s|^)' + name + '(\\s|$)').test(el.className);
            },

            addClass: function(el, name) {
                if (!util.hasClass(el, name)) {
                    el.className += (el.className ? ' ' : '') + name;
                }
            },

            removeClass: function(el, name) {
                if (util.hasClass(el, name)) {
                    el.className = el.className.replace(new RegExp('(\\s|^)' + name + '(\\s|$)'), ' ').replace(/^\s+|\s+$/g, '');
                }
            },

            addListener: function(target, type, handler) {
                if (target.addEventListener) {
                    target.addEventListener(type, handler, false);
                } else if (target.attachEvent) {
                    target.attachEvent('on' + type, handler);
                }
            },

            removeListener: function(target, type, handler) {
                if (target.removeEventListener) {
                    target.removeEventListener(type, handler, false);
                } else if (target.detachEvent) {
                    target.detachEvent('on' + type, handler);
                }
            },

            isFunction: function(functionToCheck) {
                var getType = {};
                return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
            },

            setCookie: function(name, days) {
                var date = new Date();
                date.setTime(+ date + (days * 86400000));
                document.cookie = name + '=true; expires=' + date.toGMTString() + '; path=/';
            },

            hasCookie: function(name) {
                if (document.cookie.indexOf(name) !== -1) {
                    return true;
                }
                return false;
            },

            // check if there is a hash in the url
            hashExists: function(hash) {
                if (window.location.hash.indexOf(hash) !== -1) {
                    return true;
                }
                return false;
            },

            preventDefault: function(event) {
                if (event.preventDefault) {
                    event.preventDefault();
                } else {
                    event.returnValue = false;
                }
            },

            mergeObj: function(obj1, obj2) {
                for (var attr in obj2) {
                    obj1[attr] = obj2[attr];
                }
            }
        },


        /**
         * Private Methods
         */

        // close popup when user hits escape button
        onDocUp = function(e) {
            if (settings.closeOnEscape) {
                if (e.keyCode === 27) {
                    WmpciPop.close();
                }
            }
        },

        openCallback = function() {

            // if not the default setting
            if (settings.onPopUpOpen !== null) {

                // make sure the callback is a function
                if (util.isFunction(settings.onPopUpOpen)) {
                    settings.onPopUpOpen.call();
                } else {
                    throw new TypeError('WmpciPop open callback must be a function.');
                }
            }
        },

        closeCallback = function() {

            // if not the default setting
            if (settings.onPopUpClose !== null) {

                // make sure the callback is a function
                if (util.isFunction(settings.onPopUpClose)) {
                    settings.onPopUpClose.call();
                } else {
                    throw new TypeError('WmpciPop close callback must be a function.');
                }
            }
        };



    /**
     * Public methods
     */

    WmpciPop.open = function() {

        var i, len;

        if (util.hashExists(settings.delayHash)) {
            util.setCookie(settings.cookieName, 1); // expire after 1 day
            return;
        }

        util.addClass($el.html, 'wmpci-popup-open');

        // bind close events and prevent default event
        if ($el.closeClassNoDefaultEls.length > 0) {
            for (i=0, len = $el.closeClassNoDefaultEls.length; i < len; i++) {
                util.addListener($el.closeClassNoDefaultEls[i], 'click', function(e) {
                    if (e.target === this) {
                        util.preventDefault(e);
                        WmpciPop.close();
                    }
                });
            }
        }

        // bind close events and continue with default event
        if ($el.closeClassDefaultEls.length > 0) {
            for (i=0, len = $el.closeClassDefaultEls.length; i < len; i++) {
                util.addListener($el.closeClassDefaultEls[i], 'click', function(e) {
                    if (e.target === this) {
                        WmpciPop.close();
                    }
                });
            }
        }

        // bind escape detection to document
        util.addListener(document, 'keyup', onDocUp);
        openCallback();
    };

    WmpciPop.close = function(e) {
        util.removeClass($el.html, 'wmpci-popup-open');
        util.setCookie(settings.cookieName, settings.expires);

        // unbind escape detection to document
        util.removeListener(document, 'keyup', onDocUp);
        closeCallback();
    };

    WmpciPop.init = function(options) {
        if (navigator.cookieEnabled) {
            util.mergeObj(settings, options);

            // check if there is a cookie or hash before proceeding
            if (!util.hasCookie(settings.cookieName) || util.hashExists(settings.forceHash)) {
                if (settings.delay === 0) {
                    WmpciPop.open();
                } else {
                    // delay showing the popup
                    setTimeout(WmpciPop.open, settings.delay);
                }
                if (settings.hideAfter) {
                    // hide popup after the set amount of time
                    setTimeout(WmpciPop.close, settings.hideAfter + settings.delay);
                }
            }
        }
    };

    // alias
    WmpciPop.start = function(options) {
        WmpciPop.init(options);
    };


}(window.WmpciPop = window.WmpciPop || {}));


// Custom Code
jQuery( document ).ready(function() {

    var popup_sett = {
                        'coverId'       : 'wmpci-popup-wrp',
                        'delay'         : (parseInt(Wmpci_Popup.delay) * 1000),
                        'closeOnEscape' : Wmpci_Popup.close_on_esc,
                        'expires'       : parseInt(Wmpci_Popup.exp_time),
                        'hideAfter'     : (parseInt(Wmpci_Popup.hide_time) * 1000)
                        };

    if( Wmpci_Popup.enable == 1 ){
        WmpciPop.start( popup_sett );
    }
});
