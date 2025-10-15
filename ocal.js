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

/**
 * @typedef {Object} Config
 * @prop {string} message_unsaved_changes
 * @prop {boolean} isAdmin
 */

/** @type {<T>(arrayLike: ArrayLike<T>) => T[]} */
function array(arrayLike) {
    return Array.prototype.slice.call(arrayLike);
}

/** @type {Config} */
var config;

/** @type {() => void} */
var init;

/** @type {boolean} */
var unsavedChanges;

/** @type {(event: Event) => string} */
function warning(event) {
    let confirmation = config.message_unsaved_changes;
    // @ts-ignore
    event.returnValue = confirmation;
    return confirmation;
}

/** @readonly */
var editor = Object.seal({
    /** @readonly @type {HTMLElement} */
    element: undefined,
    /** @type {string} */
    occupancy: undefined,
    /** @type {number} */
    currentState: undefined,
    /** @type {() => void} */
    init: function () {
        this.occupancy = this.element.dataset.name;
        /** @type {NodeListOf<HTMLButtonElement>} */ (
            this.element.querySelectorAll(".ocal_save")
        ).forEach((button) => {
            button.disabled = false;
        });
        this.element.addEventListener("click", this);
    },
    /** @type {(event: Event) => void} */
    handleEvent: function (event) {
        var target = /** @type {Element} */ (event.target);
        switch (target.classList[0]) {
            case "ocal_state":
                switch (target.localName) {
                    case "span":
                        return this.onSelectState(/** @type {MouseEvent} */ (event));
                    case "td":
                        return this.onClick(event);
                }
                break;
            case "ocal_save":
                return this.onSave();
        }
    },
    /** @type {(event: Event) => void} */
    onClick: function (event) {
        if (!(event.target instanceof HTMLElement) || typeof this.currentState !== "number") return;
        let target = event.target;
        if (target.classList.contains("ocal_state")) {
            if (target.dataset.ocal_state !== undefined) {
                if (parseInt(target.dataset.ocal_state) !== this.currentState) {
                    target.dataset.ocal_state = this.currentState.toString();
                    this.element.querySelectorAll(".ocal_statusbar").forEach((bar) => {
                        bar.innerHTML = "";
                    });
                    addEventListener("beforeunload", warning);
                    unsavedChanges = true;
                }
            }
        }
    },
    /** @type {(calendar: HTMLElement) => number[]} */
    getCalendarStates: function (calendar) {
        return /** @type {HTMLTableCellElement[]} */ (
            array(calendar.querySelectorAll("td.ocal_state"))
        ).map((cell) => {
            return +cell.dataset.ocal_state;
        });
    },
    /** @type {() => {[x: string]: number[]}} */
    getAllCalendarStates: function () {
        /** @type {{[x: string]: number[]}} */
        var states = {};
        this.element.querySelectorAll(".ocal_calendar").forEach((calendar) => {
            if (!(calendar instanceof HTMLElement) || calendar.dataset.ocal_date === undefined)
                return;
            states[calendar.dataset.ocal_date] = this.getCalendarStates(calendar);
        });
        return states;
    },
    /** @type {(request: XMLHttpRequest) => void} */
    doReadyStateChange: function (request) {
        if (request.readyState === 4) {
            this.element.querySelectorAll(".ocal_loaderbar").forEach((bar) => {
                if (!(bar instanceof HTMLElement)) return;
                bar.style.display = "none";
            });
            if (request.status === 200) {
                removeEventListener("beforeunload", warning);
                unsavedChanges = false;
                this.element.querySelectorAll(".ocal_statusbar").forEach((bar) => {
                    bar.innerHTML = request.responseText;
                });
            } else {
                this.element.querySelectorAll(".ocal_statusbar").forEach((bar) => {
                    if (request.responseText) {
                        bar.innerHTML = request.responseText;
                    } else {
                        bar.innerHTML =
                            '<p class="xh_fail">' +
                            request.status +
                            " " +
                            request.statusText +
                            "</p>";
                    }
                });
            }
        }
    },
    /** @type {(event: MouseEvent) => void} */
    onSelectState: function (event) {
        if (!(event.target instanceof HTMLElement)) return;
        let target = event.target;
        if (target.dataset.ocal_state === undefined) return;
        this.element.querySelectorAll(".ocal_toolbar").forEach((element) => {
            element.querySelectorAll("span").forEach((element) => {
                element.style.borderWidth = "";
            });
        });
        this.currentState = parseInt(target.dataset.ocal_state);
        target.style.borderWidth = "3px";
        this.element.querySelectorAll(".ocal_calendar td.ocal_state").forEach((cell) => {
            if (!(cell instanceof HTMLElement)) return;
            cell.style.cursor = "pointer";
        });
    },
    /** @type {() => void} */
    onSave: function () {
        let request = new XMLHttpRequest();
        request.open(
            "POST",
            location.href.replace(/#.*$/, "") + "&ocal_name=" + this.occupancy + "&ocal_action=save"
        );
        request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        let states = JSON.stringify(this.getAllCalendarStates());
        let payload = "ocal_states=" + encodeURIComponent(states);
        let tokenInput = this.element.querySelector("input[name=ocal_token]");
        if (tokenInput instanceof HTMLInputElement) {
            payload += "&ocal_token=" + encodeURIComponent(tokenInput.value);
        }
        const checksumInput = this.element.querySelector("input[name=ocal_checksum]");
        if (checksumInput instanceof HTMLInputElement) {
            payload += "&ocal_checksum=" + encodeURIComponent(checksumInput.value);
        }
        request.onreadystatechange = () => this.doReadyStateChange(request);
        request.send(payload);
        this.element.querySelectorAll(".ocal_loaderbar").forEach((bar) => {
            if (!(bar instanceof HTMLElement)) return;
            bar.style.display = "block";
        });
    }
});

/** @type {(element: HTMLElement) => void} */
function makeEditor(element) {
    /** @type {typeof editor} */ (Object.create(editor, { element: { value: element } })).init();
}

/** @type {(event: MouseEvent) => void|false} */
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
                calendar.querySelectorAll(".ocal_loaderbar").forEach((bar) => {
                    if (!(bar instanceof HTMLElement)) return;
                    bar.style.display = "none";
                });
                calendar.querySelectorAll(".ocal_statusbar").forEach((bar) => {
                    bar.innerHTML =
                        '<p class="xh_fail">' + request.status + " " + request.statusText + "</p>";
                });
            }
        }
    };
    request.send(null);
    calendar.querySelectorAll(".ocal_loaderbar").forEach((bar) => {
        if (!(bar instanceof HTMLElement)) return;
        bar.style.display = "block";
    });
    return false;
}

init = () => {
    unsavedChanges = false;
    let element = document.querySelector(
        ".ocal_calendars, .ocal_week_calendars, .ocal_lists, .ocal_week_lists"
    );
    if (!(element instanceof HTMLElement) || element.dataset.ocalConfig === undefined) return;
    config = JSON.parse(element.dataset.ocalConfig);
    document.querySelectorAll(".ocal_pagination a, .ocal_mode a").forEach((element) => {
        if (!(element instanceof HTMLElement)) return;
        element.onclick = onModeOrPaginationClick;
    });
    if (config.isAdmin) {
        document.querySelectorAll(".ocal_calendars, .ocal_week_calendars").forEach((element) => {
            if (!(element instanceof HTMLElement) || element.dataset.name === undefined) return;
            makeEditor(element);
        });
    }
};

init();
