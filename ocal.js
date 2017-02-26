/*!
 * Copyright 2014-2017 Christoph M. Becker
 *
 * This file is part of Ocal_XH.
 *
 * Ocal_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Ocal_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Ocal_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

/*jslint browser: true, maxlen: 80 */

var OCAL = OCAL || {};

(function () {
    "use strict";

    var init, unsavedChanges;

    function on(element, event, listener) {
        if (typeof element.addEventListener !== "undefined") {
            element.addEventListener(event, listener);
        } else if (typeof element.attachEvent !== "undefined") {
            element.attachEvent("on" + event, listener);
        }
    }

    function off(element, event, listener) {
        if (typeof element.removeEventListener !== "undefined") {
            element.removeEventListener(event, listener);
        } else if (typeof element.detachEvent !== "undefined") {
            element.detachEvent("on" + event, listener);
        }
    }

    function find(element, selector) {
        var elements;

        if (typeof element.querySelectorAll !== "undefined") {
            elements = element.querySelectorAll(selector);
        } else {
            elements = [];
        }
        return elements;
    }

    function each(elements, func) {
        var i, n;

        for (i = 0, n = elements.length; i < n; i += 1) {
            func(elements[i]);
        }
    }

    function warning(event) {
        var confirmation = OCAL.message_unsaved_changes;

        (event || window.event).returnValue = confirmation;
        return confirmation;
    }

    function makeEditor(element) {
        var occupancy, saveButtons, currentState;

        function onClick(event) {
            var target, state;

            if (typeof currentState !== "number") {
                return;
            }
            event = event || window.event;
            target = event.target || event.srcElement;
            if (target.className.indexOf("ocal_state") > -1) {
                state = +target.getAttribute("data-ocal_state");
                if (state !== currentState) {
                    target.setAttribute("data-ocal_state", currentState);
                    each(saveButtons, function (button) {
                        button.disabled = false;
                    });
                    each(find(element, ".ocal_statusbar"), function (bar) {
                        bar.innerHTML = "";
                    });
                    on(window, "beforeunload", warning);
                    unsavedChanges = true;
                }
            }
        }

        function getCalendarStates(calendar) {
            var states, cells;

            states = [];
            cells = calendar.getElementsByTagName("td");
            each(cells, function (cell) {
                if (cell.className.indexOf("ocal_state") > -1) {
                    states.push(cell.getAttribute("data-ocal_state"));
                }
            });
            return states;
        }

        function getAllCalendarStates() {
            var states, date;

            states = {};
            each(find(element, ".ocal_calendar"), function (calendar) {
                date = calendar.getAttribute("data-ocal_date");
                states[date] = getCalendarStates(calendar);
            });
            return states;
        }

        function doReadyStateChange(request) {
            if (request.readyState === 4) {
                each(find(element, ".ocal_loaderbar"), function (bar) {
                    bar.style.display = "none";
                });
                if (request.status === 200) {
                    each(saveButtons, function (button) {
                        button.disabled = true;
                    });
                    off(window, "beforeunload", warning);
                    unsavedChanges = false;
                    each(find(element, ".ocal_statusbar"), function (bar) {
                        bar.innerHTML = request.responseText;
                    });
                } else {
                    each(find(element, ".ocal_statusbar"), function (bar) {
                        bar.innerHTML = "<p class=\"xh_fail\">"
                            + request.status + " " + request.statusText
                            + "</p>";
                    });
                }
            }
        }

        function onSelectState(event) {
            var target, elements, cells;

            event = event || window.event;
            target = event.target || event.srcElement;
            elements = find(element, ".ocal_toolbar");
            each(elements, function (element) {
                each(element.getElementsByTagName("span"), function (element) {
                    element.style.borderWidth = "";
                });
            });
            currentState = +target.getAttribute("data-ocal_state");
            target.style.borderWidth = "3px";
            cells = find(element, ".ocal_calendar td.ocal_state");
            each(cells, function (cell) {
                cell.style.cursor = "pointer";
            });
        }

        function onSave() {
            var request, tokenInput, payload, states;

            function onReadyStateChange() {
                doReadyStateChange(request);
            }

            request = new XMLHttpRequest();
            request.open(
                "POST",
                location.href.replace(/#.*$/, "") + "&ocal_name=" +
                    occupancy + "&ocal_save=1"
            );
            tokenInput = find(element, "input[name=xh_csrf_token]")[0];
            request.setRequestHeader("Content-Type",
                    "application/x-www-form-urlencoded");
            states = JSON.stringify(getAllCalendarStates());
            payload = "ocal_states=" + encodeURIComponent(states) +
                "&xh_csrf_token=" + tokenInput.value;
            request.onreadystatechange = onReadyStateChange;
            request.send(payload);
            each(find(element, ".ocal_loaderbar"), function (bar) {
                bar.style.display = "block";
            });
        }

        occupancy = element.getAttribute("data-name");

        each(find(element, ".ocal_calendar"), function (element) {
            element.onclick = onClick;
        });

        each(find(element, ".ocal_toolbar span"), function (element) {
            element.onclick = onSelectState;
        });

        saveButtons = find(element, ".ocal_save");
        each(saveButtons, function (button) {
            button.onclick = onSave;
        });
    }

    function onModeOrPaginationClick(event) {
        var target, request, calendar, url;

        if (unsavedChanges) {
            if (window.confirm(OCAL.message_unsaved_changes)) {
                unsavedChanges = false;
                off(window, "beforeunload", warning);
            } else {
                return false;
            }
        }
        event = event || window.event;
        target = event.target || event.srcElement;
        calendar = target.parentNode.parentNode;
        request = new XMLHttpRequest();
        url = target.href + "&ocal_name=" + calendar.getAttribute("data-name");
        request.open("GET", url);
        request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        request.onreadystatechange = function () {
            if (request.readyState === 4) {
                if (request.status === 200) {
                    calendar.outerHTML = request.responseText;
                    init();
                } else {
                    each(find(calendar, ".ocal_loaderbar"), function (bar) {
                        bar.style.display = "none";
                    });
                    each(find(calendar, ".ocal_statusbar"), function (bar) {
                        bar.innerHTML = "<p class=\"xh_fail\">" +
                            request.status + " " + request.statusText + "</p>";
                    });
                }
            }
        };
        request.send(null);
        each(find(calendar, ".ocal_loaderbar"), function (bar) {
            bar.style.display = "block";
        });
        return false;
    }

    init = function () {
        var elements;

        unsavedChanges = false;
        elements = find(document, ".ocal_pagination a, .ocal_mode a");
        each(elements, function (element) {
            element.onclick = onModeOrPaginationClick;
        });
        if (OCAL.isAdmin) {
            elements = find(document, ".ocal_calendars, .ocal_week_calendars");
            each(elements, makeEditor);
        }
    };

    on(window, "load", init);
}());
