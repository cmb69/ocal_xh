/**
 * @file      The plugin's JavaScript.
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   $Id$
 */

/*global OCAL */

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
        var occupancy, currentState, saveButton, statusbar;

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
                if (cell.className.indexOf("ocal_state") > -1) {
                    states.push(cell.getAttribute("data-ocal_state"));
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
            var request, tokenInput, payload, loaderbar;

            function onReadyChangeState() {
                if (request.readyState === 4) {
                    if (request.status === 200) {
                        saveButton.disabled = true;
                        removeListener(window, "beforeunload", warning);
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

            request = new XMLHttpRequest();
            request.open(
                "POST",
                location.href + "&ocal_name=" + occupancy + "&ocal_save=1"
            );
            tokenInput = element.querySelector("input[name=xh_csrf_token]");
            request.setRequestHeader("Content-Type",
                    "application/x-www-form-urlencoded");
            payload = "ocal_states=" +
                    encodeURIComponent(JSON.stringify(getAllCalendarStates())) +
                    "&xh_csrf_token=" + tokenInput.value;
            request.onreadystatechange = onReadyChangeState;
            request.send(payload);
            loaderbar = element.querySelector(".ocal_loaderbar");
            loaderbar.style.display = "block";
        }

        function onSelectState(event) {
            var target, elements, cells, i;

            event = event || window.event;
            target = event.target || event.srcElement;
            elements = element.querySelectorAll(".ocal_toolbar span");
            for (i = 0; i < elements.length; i += 1) {
                elements[i].style.borderWidth = "";
            }
            currentState = +target.getAttribute("data-ocal_state");
            target.style.borderWidth = "3px";
            cells = element.querySelectorAll(".ocal_calendar td.ocal_state");
            for (i = 0; i < cells.length; i += 1) {
                cells[i].style.cursor = "pointer";
            }
        }

        function init() {
            var elements, i;

            occupancy = element.getAttribute("data-name");

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
