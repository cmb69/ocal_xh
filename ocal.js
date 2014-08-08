/**
 * @file      The plugin's JavaScript.
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   $Id$
 */

(function () {
    "use strict";

    function addListener(element, event, listener) {
        if (typeof element.addEventListener !== "undefined") {
            element.addEventListener(event, listener);
        } else if (typeof element.attachEvent !== "undefined") {
            element.attachEvent("on" + event, listener);
        }
    }

    function removeListener(element, event, listener) {
        if (typeof element.removeEventListener !== "undefined") {
            element.removeEventListener(event, listener);
        } else if (typeof element.detachEvent !== "undefined") {
            element.detachEvent("on" + event, listener);
        }
    }

    function initEditor(element) {
        var currentState, saveButton, statusbar;

        function warning(event) {
            var confirmation = "Unsaved changes!";

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
            if (target.className.indexOf("ocal_state_") === 0) {
                state = +target.className.substr(("ocal_state_").length);
                if (state !== currentState) {
                    target.className = "ocal_state_" + currentState;
                    saveButton.disabled = false;
                    statusbar.innerHTML = "";
                    addListener(window, "beforeunload", warning);
                }
            }
        }

        function getCalendarStates(calendar) {
            var states, cells, i, cell;

            states = [];
            cells = calendar.getElementsByTagName("td");
            for (i = 0; i < cells.length; i += 1) {
                cell = cells[i];
                if (cell.className.indexOf("ocal_state_") === 0) {
                    states.push(cell.className.substr(("ocal_state_").length));
                }
            }
            return states;
        }

        function getAllCalendarStates() {
            var states, calendars, i, calendar, month;

            states = {};
            calendars = element.querySelectorAll(".ocal_calendar");
            for (i = 0; i < calendars.length; i += 1) {
                calendar = calendars[i];
                month = calendar.getAttribute("data-month");
                states[month] = getCalendarStates(calendar);
            }
            return states;
        }

        function onSave() {
            var request, payload, loaderbar;

            // FIXME: error reporting
            function onReadyChangeState() {
                if (request.readyState === 4 && request.status === 200) {
                    saveButton.disabled = true;
                    removeListener(window, "beforeunload", warning);
                    loaderbar.style.display = "none";
                    statusbar.innerHTML = request.responseText;
                }
            }

            request = new XMLHttpRequest();
            request.open("POST", location.href + "&ocal_save=1");
            request.setRequestHeader("Content-Type", "application/json");
            payload = JSON.stringify(getAllCalendarStates());
            request.onreadystatechange = onReadyChangeState;
            request.send(payload);
            loaderbar = element.querySelector(".ocal_loaderbar");
            loaderbar.style.display = "block";
        }

        function onSelectState(event) {
            var target, cells, i;

            event = event || window.event;
            target = event.target || event.srcElement;
            currentState = +target.className.substr("ocal_state_".length);
            target.style.borderWidth = "3px";
            cells = element.querySelectorAll(".ocal_calendar td");
            for (i = 0; i < cells.length; i += 1) {
                cells[i].style.cursor = "pointer";
            }
        }

        function init() {
            var elements, i;

            elements = element.querySelectorAll(".ocal_calendar");
            for (i = 0; i < elements.length; i += 1) {
                elements[i].onclick = onClick;
            }

            elements = element.querySelectorAll(".ocal_toolbar span");
            for (i = 0; i < elements.length; i += 1) {
                elements[i].onclick = onSelectState;
            }

            saveButton = element.querySelector(".ocal_save");
            saveButton.onclick = onSave;
            statusbar = element.querySelector(".ocal_statusbar");
        }

        init();
    }

    function init() {
        var editors, i;

        editors = document.querySelectorAll(".ocal_calendars");
        for (i = 0; i < editors.length; i += 1) {
            initEditor(editors[i]);
        }
    }

    init();
}());
