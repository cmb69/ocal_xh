/*!
 * Copyright (c) Christoph M. Becker
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

// @ts-check

/** @type {Object} */
var config;

/** @type {function(void): void} */
var init;

/** @type {boolean} */
var unsavedChanges;

/**
 * @param {Event} event
 * @returns {string}
 */
function warning(event) {
    let confirmation = config.message_unsaved_changes;
    // @ts-ignore
    event.returnValue = confirmation;
    return confirmation;
}

/**
 * @param {HTMLElement} element
 * @param {string} occupancy
 * @returns {void}
 */
function makeEditor(element, occupancy) {
    /** @type {number} */
    var currentState;

    /**
     * @param {Event} event
     * @returns {void}
     */
    function onClick(event) {
        if (!(event.target instanceof HTMLElement) || typeof currentState !== "number") return;
        let target = event.target;
        if (target.classList.contains("ocal_state")) {
            if (target.dataset.ocal_state !== undefined) {
                if (parseInt(target.dataset.ocal_state) !== currentState) {
                    target.dataset.ocal_state = currentState.toString();
                    element.querySelectorAll(".ocal_statusbar").forEach(bar => {
                        bar.innerHTML = "";
                    });
                    addEventListener("beforeunload", warning);
                    unsavedChanges = true;
                }
            }
        }
    }

    /**
     * @param {HTMLElement} calendar 
     * @returns {Array<number>}
     */
    function getCalendarStates(calendar) {
        let states = [];
        calendar.querySelectorAll("td").forEach(cell => {
            if (cell.classList.contains("ocal_state")) {
                states.push(+(cell.dataset.ocal_state || ""));
            }
        });
        return states;
    }

    /**
     * @returns {Object<string, Array<number>>}
     */
    function getAllCalendarStates() {
        /** @type {Object<string, Array<number>>} */
        var states = {};
        element.querySelectorAll(".ocal_calendar").forEach(calendar => {
            if (!(calendar instanceof HTMLElement) || calendar.dataset.ocal_date === undefined) return;
            states[calendar.dataset.ocal_date] = getCalendarStates(calendar);
        });
        return states;
    }

    /**
     * @param {XMLHttpRequest} request
     * @returns {void}
     */
    function doReadyStateChange(request) {
        if (request.readyState === 4) {
            element.querySelectorAll(".ocal_loaderbar").forEach(bar => {
                if (!(bar instanceof HTMLElement)) return;
                bar.style.display = "none";
            });
            if (request.status === 200) {
                removeEventListener("beforeunload", warning);
                unsavedChanges = false;
                element.querySelectorAll( ".ocal_statusbar").forEach(bar => {
                    bar.innerHTML = request.responseText;
                });
            } else {
                element.querySelectorAll(".ocal_statusbar").forEach(bar => {
                    if (request.responseText) {
                        bar.innerHTML = request.responseText;
                    } else {
                        bar.innerHTML = "<p class=\"xh_fail\">" + request.status + " " + request.statusText + "</p>";
                    }
                });
            }
        }
    }

    /**
     * @param {MouseEvent} event
     * @returns {void}
     */
    function onSelectState(event) {
        if (!(event.target instanceof HTMLElement)) return;
        let target = event.target;
        if (target.dataset.ocal_state === undefined) return;
        element.querySelectorAll(".ocal_toolbar").forEach(element => {
            element.querySelectorAll("span").forEach(element => {
                element.style.borderWidth = "";
            });
        });
        currentState = parseInt(target.dataset.ocal_state);
        target.style.borderWidth = "3px";
        element.querySelectorAll(".ocal_calendar td.ocal_state").forEach(cell => {
            if (!(cell instanceof HTMLElement)) return;
            cell.style.cursor = "pointer";
        });
    }

    /**
     * @returns {void}
     */
    function onSave() {
        let request = new XMLHttpRequest();
        request.open(
            "POST",
            location.href.replace(/#.*$/, "") + "&ocal_name=" + occupancy + "&ocal_action=save"
        );
        request.setRequestHeader("Content-Type",
                "application/x-www-form-urlencoded");
        let states = JSON.stringify(getAllCalendarStates());
        let payload = "ocal_states=" + encodeURIComponent(states);
        let tokenInput = element.querySelector("input[name=ocal_token]");
        if (tokenInput instanceof HTMLInputElement) {
            payload += "&ocal_token=" + encodeURIComponent(tokenInput.value);
        }
        const checksumInput = element.querySelector("input[name=ocal_checksum]");
        if (checksumInput instanceof HTMLInputElement) {
            payload += "&ocal_checksum=" + encodeURIComponent(checksumInput.value);
        }
        request.onreadystatechange = () => doReadyStateChange(request);
        request.send(payload);
        element.querySelectorAll(".ocal_loaderbar").forEach(bar => {
            if (!(bar instanceof HTMLElement)) return;
            bar.style.display = "block";
        });
    }

    element.querySelectorAll(".ocal_calendar").forEach(element => {
        if (!(element instanceof HTMLElement)) return;
        element.onclick = onClick;
    });

    element.querySelectorAll(".ocal_toolbar span").forEach(element => {
        if (!(element instanceof HTMLElement)) return;
        element.onclick = onSelectState;
    });

    element.querySelectorAll(".ocal_save").forEach(button => {
        if (!(button instanceof HTMLButtonElement)) return;
        button.onclick = onSave;
        button.disabled = false;
    });
}

/**
 * @param {MouseEvent} event
 * @returns {void|false}
 */
function onModeOrPaginationClick(event) {
    if (!(event.target instanceof HTMLAnchorElement)) return;
    let target = event.target;
    if (target.parentElement === null || target.parentElement.parentElement === null) return;
    if (unsavedChanges) {
        if (window.confirm(config.message_unsaved_changes)) {
            unsavedChanges = false;
            removeEventListener("beforeunload", warning);
        } else {
            return false;
        }
    }
    let calendar = target.parentElement.parentElement;
    let request = new XMLHttpRequest();
    request.open("GET", target.href + "&ocal_name=" + calendar.dataset.name);
    request.setRequestHeader("X-CMSimple-XH-Request", "ocal");
    request.onreadystatechange = () => {
        if (request.readyState === 4) {
            if (request.status === 200) {
                calendar.outerHTML = request.responseText;
                init();
            } else {
                calendar.querySelectorAll(".ocal_loaderbar").forEach(bar => {
                    if (!(bar instanceof HTMLElement)) return;
                    bar.style.display = "none";
                });
                calendar.querySelectorAll(".ocal_statusbar").forEach(bar => {
                    bar.innerHTML = "<p class=\"xh_fail\">" +
                        request.status + " " + request.statusText + "</p>";
                });
            }
        }
    };
    request.send(null);
    calendar.querySelectorAll(".ocal_loaderbar").forEach(bar => {
        if (!(bar instanceof HTMLElement)) return;
        bar.style.display = "block";
    });
    return false;
}

init = () => {
    unsavedChanges = false;
    let element = document.querySelector(".ocal_calendars, .ocal_week_calendars, .ocal_lists, .ocal_week_lists");
    if (!(element instanceof HTMLElement) || element.dataset.ocalConfig === undefined) return;
    config = JSON.parse(element.dataset.ocalConfig);
    document.querySelectorAll(".ocal_pagination a, .ocal_mode a").forEach(element => {
        if (!(element instanceof HTMLElement)) return;
        element.onclick = onModeOrPaginationClick;
    });
    if (config.isAdmin) {
        document.querySelectorAll(".ocal_calendars, .ocal_week_calendars").forEach(element => {
            if (!(element instanceof HTMLElement) || element.dataset.name === undefined) return;
            makeEditor(element, element.dataset.name);
        });
    }
};

init();
