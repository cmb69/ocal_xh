/**
 * @file      The plugin's JavaScript.
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   $Id$
 */

/**
 * The plugin namespace.
 */
var OCAL = OCAL || {};

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

    /**
     * The calendar widgets.
     */
    OCAL.Widget = function (element) {
        var that, elements, i;

        function onClick(event) {
            that.onClick(event);
        }

        function onSelectState(event) {
            that.onSelectState(event);
        }

        that = this;
        this.element = element;
        this.occupancy = element.getAttribute("data-name");

        elements = element.querySelectorAll(".ocal_calendar");
        for (i = 0; i < elements.length; i += 1) {
            elements[i].onclick = onClick;
        }

        elements = element.querySelectorAll(".ocal_toolbar span");
        for (i = 0; i < elements.length; i += 1) {
            elements[i].onclick = onSelectState;
        }

        this.saveButton = element.querySelector(".ocal_save");
        this.saveButton.onclick = function () {
            that.onSave();
        };
        this.statusbar = element.querySelector(".ocal_statusbar");
    };

    OCAL.Widget.prototype.warning = function (event) {
        var confirmation = OCAL.message_unsaved_changes;

        (event || window.event).returnValue = confirmation;
        return confirmation;
    };

    OCAL.Widget.prototype.onClick = function (event) {
        var target, state;

        if (typeof this.currentState !== "number") {
            return;
        }
        event = event || window.event;
        target = event.target || event.srcElement;
        if (target.className.indexOf("ocal_state") > -1) {
            state = +target.getAttribute("data-ocal_state");
            if (state !== this.currentState) {
                target.setAttribute("data-ocal_state", this.currentState);
                this.saveButton.disabled = false;
                this.statusbar.innerHTML = "";
                addListener(window, "beforeunload", this.warning);
            }
        }
    };

    OCAL.Widget.prototype.getCalendarStates = function (calendar) {
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
    };

    OCAL.Widget.prototype.getAllCalendarStates = function () {
        var states, calendars, i, calendar, month;

        states = {};
        calendars = this.element.querySelectorAll(".ocal_calendar");
        for (i = 0; i < calendars.length; i += 1) {
            calendar = calendars[i];
            month = calendar.getAttribute("data-month");
            states[month] = this.getCalendarStates(calendar);
        }
        return states;
    };

    OCAL.Widget.prototype.onReadyStateChange = function (request, loaderbar) {
        if (request.readyState === 4) {
            if (request.status === 200) {
                this.saveButton.disabled = true;
                removeListener(window, "beforeunload", this.warning);
                loaderbar.style.display = "none";
                this.statusbar.innerHTML = request.responseText;
            } else {
                loaderbar.style.display = "none";
                this.statusbar.innerHTML = "<p class=\"xh_fail\">"
                        + request.status + " " + request.statusText
                        + "</p>";
            }
        }
    };

    OCAL.Widget.prototype.onSave = function () {
        var that, request, tokenInput, payload, loaderbar;

        that = this;
        request = new XMLHttpRequest();
        request.open(
            "POST",
            location.href + "&ocal_name=" + this.occupancy + "&ocal_save=1"
        );
        tokenInput = this.element.querySelector("input[name=xh_csrf_token]");
        request.setRequestHeader("Content-Type",
                "application/x-www-form-urlencoded");
        payload = "ocal_states=" +
            encodeURIComponent(JSON.stringify(this.getAllCalendarStates())) +
            "&xh_csrf_token=" + tokenInput.value;
console.log(payload);
        request.onreadystatechange = function () {
            that.onReadyStateChange(request, loaderbar);
        };
        request.send(payload);
        loaderbar = this.element.querySelector(".ocal_loaderbar");
        loaderbar.style.display = "block";
    };

    OCAL.Widget.prototype.onSelectState = function (event) {
        var target, elements, cells, i;

        event = event || window.event;
        target = event.target || event.srcElement;
        elements = this.element.querySelectorAll(".ocal_toolbar span");
        for (i = 0; i < elements.length; i += 1) {
            elements[i].style.borderWidth = "";
        }
        this.currentState = +target.getAttribute("data-ocal_state");
        target.style.borderWidth = "3px";
        cells = this.element.querySelectorAll(".ocal_calendar td.ocal_state");
        for (i = 0; i < cells.length; i += 1) {
            cells[i].style.cursor = "pointer";
        }
    };

    function init() {
        var editors, i, editor;

        editors = document.querySelectorAll(".ocal_calendars");
        for (i = 0; i < editors.length; i += 1) {
            editor = new OCAL.Widget(editors[i]);
        }
    }

    init();
}());
