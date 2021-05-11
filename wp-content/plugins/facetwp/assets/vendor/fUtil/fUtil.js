/* fUtil - 2021-01-16 */

window.fUtil = (() => {

    class fUtil {

        constructor(selector) {
            if (typeof selector === 'string' || selector instanceof String) {
                this.nodes = Array.from(document.querySelectorAll(selector));
            }
            else if (typeof selector === 'object' && selector.nodeName) {
                this.nodes = [selector];
            }
            else if (typeof selector === 'function') {
                this.ready(selector);
            }
            else {
                this.nodes = [document];
            }
        }

        static isset(input) {
            return typeof input !== 'undefined';
        }

        static post(url, data, settings) {
            var settings = Object.assign({}, {
                done: () => {},
                fail: () => {}
            }, settings);

            return fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(json => settings.done(json))
            .catch(err => settings.fail(err));
        }

        static each(items, callback) {
            if (typeof items === 'object' && items !== null) {
                if (Array.isArray(items)) {
                    items.forEach((item, index) => callback(item, index));
                }
                else {
                    Object.keys(items).forEach(item => callback(items[item], item));
                }
            }
        }

        ready(callback) {
            if (typeof callback !== 'function') return;

            if (document.readyState === 'interactive' || document.readyState === 'complete') {
                return callback();
            }

            document.addEventListener('DOMContentLoaded', callback, false);
        }

        addClass(className) {
            this.nodes.forEach(node => node.classList.add(className));
            return this;
        }

        removeClass(className) {
            this.nodes.forEach(node => node.classList.remove(className));
            return this;
        }

        hasClass(className) {
            return $.isset(this.nodes.find(node => node.classList.contains(className)));
        }

        toggleClass(className) {
            this.nodes.forEach(node => node.classList.toggle(className));
            return this;
        }

        prev(selector) {
            if (!this.nodes.length) return;

            var sibling = this.nodes[0].previousElementSibling;

            if (selector) {
                while (sibling) {
                    if (sibling.matches(selector)) break;
                    sibling = sibling.previousElementSibling;
                }
            }

            this.nodes = (null !== sibling) ? [sibling] : [];

            return this;
        }

        next(selector) {
            if (!this.nodes.length) return;

            var sibling = this.nodes[0].nextElementSibling;

            if (selector) {
                while (sibling) {
                    if (sibling.matches(selector)) break;
                    sibling = sibling.nextElementSibling;
                }
            }

            this.nodes = (null !== sibling) ? [sibling] : [];

            return this;
        }

        closest(selector) {
            if (!this.nodes.length) return;

            var nodes = this.nodes[0].closest(selector);

            this.nodes = (null !== nodes) ? [nodes] : [];

            return this;
        }

        on(eventName, selector, callback) {
            if (!$.isset(selector)) return;
            if (!$.isset(callback)) {
                var callback = selector;
                var selector = null;
            }

            // Reusable callback
            var checkForMatch = (e) => {
                if (null === selector || e.target.matches(selector) || null !== e.target.closest(selector)) {
                    callback(e);
                }
            };

            this.nodes.forEach(node => {

                // Attach a unique ID to each node
                if (!$.isset(node._id)) {
                    node._id = $.event.count;
                    $.event.store[$.event.count] = node;
                    $.event.count++;
                }

                var id = node._id;

                // Store the raw callback, needed for .off()
                checkForMatch._str = callback.toString();

                if (!$.isset($.event.map[id])) {
                    $.event.map[id] = {};
                }
                if (!$.isset($.event.map[id][eventName])) {
                    $.event.map[id][eventName] = {};
                }
                if (!$.isset($.event.map[id][eventName][selector])) {
                    $.event.map[id][eventName][selector] = [];
                }

                // Use $.event.map to store event references
                // removeEventListener needs named callbacks, so we're creating
                // one for every handler
                let length = $.event.map[id][eventName][selector].push(checkForMatch);

                node.addEventListener(eventName, $.event.map[id][eventName][selector][length - 1]);
            });

            return this;
        }

        off(eventName, selector, callback) {
            if (!$.isset(callback)) {
                var callback = selector;
                var selector = null;
            }

            this.nodes.forEach(node => {
                var id = node._id;

                $.each($.event.map[id], (selectors, theEventName) => {
                    $.each(selectors, (callbacks, theSelector) => {
                        $.each(callbacks, (theCallback, index) => {
                            if (
                                (!eventName || theEventName === eventName) &&
                                (!selector || theSelector === selector) &&
                                (!callback || theCallback._str === callback.toString())
                            ) {
                                node.removeEventListener(theEventName, $.event.map[id][theEventName][theSelector][index]);
                                delete $.event.map[id][theEventName][theSelector][index];
                            }
                        });
                    });
                });
            });

            return this;
        }

        trigger(eventName, extraData) {
            this.nodes.forEach(node => node.dispatchEvent(new CustomEvent(eventName, extraData)));
            return this;
        }

        find(selector) {
            if (!this.nodes.length) return;

            this.nodes = Array.from(this.nodes[0].querySelectorAll(selector));
            return this;
        }

        attr(attributeName, value) {
            if (!this.nodes.length) return;

            if (!$.isset(value)) {
                return this.nodes[0].getAttribute(attributeName);
            }

            this.nodes.forEach(node => node.setAttribute(attributeName, value));
            return this;

        }

        data(key, value) {
            if (!this.nodes.length) return;

            if (!$.isset(value)) {
                return this.nodes[0].dataset[key];
            }

            this.nodes.forEach(node => node.dataset[key] = value);
            return this;
        }

        html(htmlString) {
            if (!this.nodes.length) return;

            if (!$.isset(htmlString)) {
                return this.nodes[0].innerHTML;
            }

            this.nodes.forEach(node => node.innerHTML = htmlString);
            return this;
        }

        text(textString) {
            if (!this.nodes.length) return;

            if (!$.isset(textString)) {
                return this.nodes[0].textContent;
            }

            this.nodes.forEach(node => node.textContent = textString);
            return this;
        }

        val(value) {
            if (!this.nodes.length) return;

            if (!$.isset(value)) {
                return this.nodes[0].value;
            }

            this.nodes.forEach(node => node.value = value);
            return this;
        }
    }

    // This needs to be a function (not a class)
    var $ = selector => new fUtil(selector);

    // Set object methods
    $.post = fUtil.post;
    $.isset = fUtil.isset;
    $.each = fUtil.each;
    $.event = {
        map: {},
        store: [],
        count: 0
    };
    return $;
})();
