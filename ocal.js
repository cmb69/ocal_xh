/**
 * @file      The plugin's JavaScript.
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2015 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

/*jslint browser: true, maxlen: 80 */

/**
 * The plugin namespace.
 */
var OCAL = OCAL || {};

(function () {
    "use strict";

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

    function find(element, className) {
        var elements;

        if (typeof element.getElementsByClassName !== "undefined") {
            elements = element.getElementsByClassName(className);
        } else if (typeof element.querySelectorAll !== "undefined") {
            elements = element.querySelectorAll("." + className);
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

    /**
     * The calendar widgets.
     *
     * @class
     */
    function makeEditor(element) {
        var elements, occupancy, saveButton, statusbar, currentState;

        function warning(event) {
            var confirmation = OCAL.message_unsaved_changes;

            (event || window.event).returnValue = confirmation;
            return confirmation;
        }

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
                    saveButton.disabled = false;
                    statusbar.innerHTML = "";
                    on(window, "beforeunload", warning);
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
            var states, calendars, date;

            states = {};
            calendars = element.querySelectorAll(".ocal_calendar");
            each(calendars, function (calendar) {
                date = calendar.getAttribute("data-ocal_date");
                states[date] = getCalendarStates(calendar);
            });
            return states;
        }

        function doReadyStateChange(request, loaderbar) {
            if (request.readyState === 4) {
                if (request.status === 200) {
                    saveButton.disabled = true;
                    off(window, "beforeunload", warning);
                    loaderbar.style.display = "none";
                    statusbar.innerHTML = request.responseText;
                } else {
                    loaderbar.style.display = "none";
                    statusbar.innerHTML = "<p class=\"xh_fail\">"
                            + request.status + " " + request.statusText
                            + "</p>";
                }
            }
        }

        function onSelectState(event) {
            var target, elements, cells;

            event = event || window.event;
            target = event.target || event.srcElement;
            elements = find(element, "ocal_toolbar");
            each(elements, function (element) {
                each(element.getElementsByTagName("span"), function (element) {
                    element.style.borderWidth = "";
                });
            });
            currentState = +target.getAttribute("data-ocal_state");
            target.style.borderWidth = "3px";
            cells =
                element.querySelectorAll(".ocal_calendar td.ocal_state");
            each(cells, function (cell) {
                cell.style.cursor = "pointer";
            });
        }

        function onSave() {
            var request, tokenInput, payload, loaderbar, states;

            function onReadyStateChange() {
                doReadyStateChange(request, loaderbar);
            }

            request = new XMLHttpRequest();
            request.open(
                "POST",
                location.href.replace(/#.*$/, "") + "&ocal_name=" +
                    occupancy + "&ocal_save=1"
            );
            tokenInput =
                element.querySelector("input[name=xh_csrf_token]");
            request.setRequestHeader("Content-Type",
                    "application/x-www-form-urlencoded");
            states = JSON.stringify(getAllCalendarStates());
            payload = "ocal_states=" + encodeURIComponent(states) +
                "&xh_csrf_token=" + tokenInput.value;
            loaderbar = element.querySelector(".ocal_loaderbar");
            request.onreadystatechange = onReadyStateChange;
            request.send(payload);
            loaderbar.style.display = "block";
        }

        occupancy = element.getAttribute("data-name");

        elements = element.querySelectorAll(".ocal_calendar");
        each(elements, function (element) {
            element.onclick = onClick;
        });

        elements = element.querySelectorAll(".ocal_toolbar span");
        each(elements, function (element) {
            element.onclick = onSelectState;
        });

        saveButton = element.querySelector(".ocal_save");
        saveButton.onclick = onSave;
        statusbar = element.querySelector(".ocal_statusbar");
    }

    function init() {
        var editors;

        editors = document.querySelectorAll(
            ".ocal_calendars, .ocal_week_calendars"
        );
        each(editors, makeEditor);
    }

    init();
}());
