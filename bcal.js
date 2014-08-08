/**
 * @file      The plugin's JavaScript.
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   $Id$
 */

(function () {
    "use strict";

    var currentState, saveButton;

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

    function warning(event) {
        var confirmation = "Unsaved changes!";

        (event || window.event).returnValue = confirmation;
        return confirmation;
    }

    function onClick(event) {
        var target;

        if (typeof currentState !== "number") {
            return;
        }
        event = event || window.event;
        target = event.target || event.srcElement;
        if (target.className.indexOf("bcal_state_") === 0) {
            target.className = "bcal_state_" + currentState;
            saveButton.disabled = false;
            addListener(window, "beforeunload", warning);
        }
    }

    function getCalendarStates(calendar) {
        var states, cells, i, cell, state;

        states = [];
        cells = calendar.getElementsByTagName("td");
        for (i = 0; i < cells.length; i += 1) {
            cell = cells[i];
            if (cell.className.indexOf("bcal_state_") === 0) {
                state = cell.className.substr(("bcal_state_").length);
                states.push(state);
            }
        }
        return states;
    }

    function getAllCalendarStates() {
        var states, tables, i, table, month;

        states = {};
        tables = document.getElementsByTagName("table");
        for (i = 0; i < tables.length; i += 1) {
            table = tables[i];
            if (table.className === "bcal_calendar") {
                month = table.getAttribute("data-month");
                states[month] = getCalendarStates(table);
            }
        }
        return states;
    }

    function addSaveHandler() {
        var buttons, i, button;

        function save() {
            var request, payload;

            request = new XMLHttpRequest();
            request.open("POST", location.href + "&bcal_save=1");
            request.setRequestHeader("Content-Type", "application/json");
            // FIXME: JSON.stringify
            payload = JSON.stringify(getAllCalendarStates());
            // FIXME: error reporting
            request.onreadystatechange = function () {
                if (request.readyState === 4 && request.status === 200) {
                    saveButton.disabled = true;
                    removeListener(window, "beforeunload", warning);
                }
            };
            request.send(payload);
        }

        buttons = document.getElementsByTagName("button");
        for (i = 0; i < buttons.length; i += 1) {
            button = buttons[i];
            if (button.className === "bcal_save") {
                saveButton = button;
                saveButton.onclick = save;
            }
        }
    }

    function makeClickable() {
        var tables, i, table;

        tables = document.getElementsByTagName("table");
        for (i = 0; i < tables.length; i += 1) {
            table = tables[i];
            if (table.className === "bcal_calendar") {
                table.onclick = onClick;
            }
        }
    }

    function init() {
        var states, i;

        function selectState(event) {
            var target, cells, i;

            event = event || window.event;
            target = event.target || event.srcElement;
            currentState = +target.className.substr("bcal_state_".length);
            target.style.borderWidth = "3px";
            cells = document.querySelectorAll(".bcal_calendar td");
            for (i = 0; i < cells.length; i += 1) {
                cells[i].style.cursor = "pointer";
            }
        }

        states = document.querySelectorAll(".bcal_toolbar span");
        for (i = 0; i < states.length; i += 1) {
            states[i].onclick = selectState;
        }
    }

    makeClickable();
    addSaveHandler();
    init();
}());
