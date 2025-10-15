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

(function () {
    "use strict";

    /**
     * @typedef {Object} Config
     * @prop {string} message_unsaved_changes
     * @prop {boolean} isAdmin
     */

    /** @type {<T>(arrayLike: ArrayLike<T>) => T[]} */
    function array(arrayLike) {
        return Array.prototype.slice.call(arrayLike);
    }

    /** @readonly */
    var widget = Object.seal({
        /** @readonly @type {HTMLElement} */
        element: undefined,
        /** @type {string} */
        occupancy: undefined,
        /** @type {number} */
        currentState: undefined,
        /** @type {boolean} */
        unsavedChanges: undefined,
        /** @type {Config} */
        get config() {
            return JSON.parse(this.element.dataset.ocalConfig);
        },
        /** @type {HTMLElement[]} */
        get stateButtons() {
            return array(this.element.querySelectorAll(".ocal_toolbar span"));
        },
        /** @type {HTMLElement} */
        get loaderbar() {
            return this.element.querySelector(".ocal_loaderbar");
        },
        /** @type {HTMLElement} */
        get statusbar() {
            return this.element.querySelector(".ocal_statusbar");
        },
        /** @type {() => void} */
        init: function () {
            var classList = this.element.classList;
            this.unsavedChanges = false;
            if (
                this.config.isAdmin &&
                (classList.contains("ocal_calendars") || classList.contains("ocal_week_calendars"))
            ) {
                this.occupancy = this.element.dataset.name;
            }
            this.element.addEventListener("click", this);
        },
        /** @type {(event: Event) => void} */
        handleEvent: function (event) {
            var target = /** @type {Element} */ (event.target);
            switch (event.type) {
                case "click":
                    switch (target.classList[0]) {
                        case "ocal_button":
                            return this.onModeOrPaginationClick(/** @type {MouseEvent} */ (event));
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
                    break;
                case "beforeunload":
                    this.warning(event);
            }
        },
        /** @type {(event: MouseEvent) => void} */
        onModeOrPaginationClick: function (event) {
            var target = /** @type {HTMLAnchorElement} */ (event.target);
            if (this.unsavedChanges) {
                if (window.confirm(this.config.message_unsaved_changes)) {
                    this.unsavedChanges = false;
                    removeEventListener("beforeunload", this);
                } else {
                    event.preventDefault();
                }
            }
            var request = new XMLHttpRequest();
            request.open("GET", target.href + "&ocal_name=" + this.element.dataset.name);
            request.setRequestHeader("X-CMSimple-XH-Request", "ocal");
            request.onreadystatechange = this.handleLoadReadyStateChange.bind(this, request);
            request.send(null);
            this.loaderbar.style.display = "block";
            event.preventDefault();
        },
        /** @type {(request: XMLHttpRequest) => void} */
        handleLoadReadyStateChange: function (request) {
            if (request.readyState === 4) {
                if (request.status === 200) {
                    this.element.outerHTML = request.responseText;
                    init();
                } else {
                    this.loaderbar.style.display = "none";
                    this.statusbar.innerHTML =
                        '<p class="xh_fail">' + request.status + " " + request.statusText + "</p>";
                }
            }
        },
        /** @type {(event: Event) => void} */
        onClick: function (event) {
            if (typeof this.currentState !== "number") return;
            var target = /** @type {HTMLElement} */ (event.target);
            if (target.classList.contains("ocal_state")) {
                if (target.dataset.ocal_state !== undefined) {
                    if (+target.dataset.ocal_state !== this.currentState) {
                        target.dataset.ocal_state = this.currentState.toString();
                        this.statusbar.innerHTML = "";
                        addEventListener("beforeunload", this);
                        this.unsavedChanges = true;
                        this.enableSaveButton(true);
                    }
                }
            }
        },
        /** @type {(calendar: HTMLElement) => [string, number[]]} */
        getCalendarStates: function (calendar) {
            return [
                calendar.dataset.ocal_date,
                /** @type {HTMLTableCellElement[]} */ (
                    array(calendar.querySelectorAll("td.ocal_state"))
                ).map(function (cell) {
                    return +cell.dataset.ocal_state;
                }),
            ];
        },
        /** @type {() => {[x: string]: number[]}} */
        getAllCalendarStates: function () {
            var calendars = /** @type {HTMLElement[]} */ (
                array(this.element.querySelectorAll(".ocal_calendar"))
            );
            return calendars.map(this.getCalendarStates.bind(this)).reduce(function (acc, pair) {
                acc[pair[0]] = pair[1];
                return acc;
            }, /** @type {{[x: string]: number[]}} */ ({}));
        },
        /** @type {(request: XMLHttpRequest) => void} */
        handleSaveReadyStateChange: function (request) {
            if (request.readyState === 4) {
                this.loaderbar.style.display = "none";
                if (request.status === 200) {
                    removeEventListener("beforeunload", this);
                    this.unsavedChanges = false;
                    this.enableSaveButton(false);
                    this.statusbar.innerHTML = request.responseText;
                } else {
                    if (request.responseText) {
                        this.statusbar.innerHTML = request.responseText;
                    } else {
                        this.statusbar.innerHTML =
                            '<p class="xh_fail">' +
                            request.status +
                            " " +
                            request.statusText +
                            "</p>";
                    }
                }
            }
        },
        /** @type {(event: MouseEvent) => void} */
        onSelectState: function (event) {
            var target = /** @type {HTMLElement} */ (event.target);
            if (target.dataset.ocal_state === undefined) return;
            this.stateButtons.forEach(function (element) {
                element.style.borderWidth = "";
            });
            this.currentState = +target.dataset.ocal_state;
            target.style.borderWidth = "3px";
            /** @type {HTMLElement[]} */ (
                array(this.element.querySelectorAll(".ocal_calendar td.ocal_state"))
            ).forEach(function (cell) {
                cell.style.cursor = "pointer";
            });
        },
        /** @type {() => void} */
        onSave: function () {
            var request = new XMLHttpRequest();
            var url =
                location.href.replace(/#.*$/, "") +
                "&ocal_name=" +
                this.occupancy +
                "&ocal_action=save";
            request.open("POST", url);
            request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            var states = JSON.stringify(this.getAllCalendarStates());
            var payload = "ocal_states=" + encodeURIComponent(states);
            var tokenInput = /** @type {HTMLInputElement} */ (
                this.element.querySelector("input[name=ocal_token]")
            );
            if (tokenInput) {
                payload += "&ocal_token=" + encodeURIComponent(tokenInput.value);
            }
            var checksumInput = /** @type {HTMLInputElement} */ (
                this.element.querySelector("input[name=ocal_checksum]")
            );
            if (checksumInput) {
                payload += "&ocal_checksum=" + encodeURIComponent(checksumInput.value);
            }
            request.onreadystatechange = this.handleSaveReadyStateChange.bind(this, request);
            request.send(payload);
            this.loaderbar.style.display = "block";
        },
        /** @type {(enable: boolean) => void} */
        enableSaveButton: function (enable) {
            /** @type {HTMLButtonElement} */ (this.element.querySelector(".ocal_save")).disabled =
                !enable;
        },
        /** @type {(event: Event) => string} */
        warning: function (event) {
            var confirmation = this.config.message_unsaved_changes;
            // @ts-ignore
            event.returnValue = confirmation;
            return confirmation;
        },
    });

    /** @type {() => void} */
    function init() {
        var sel =
            ".ocal_calendars[data-ocal-config], .ocal_week_calendars[data-ocal-config], " +
            ".ocal_lists[data-ocal-config], .ocal_week_lists[data-ocal-config]";
        var elements = /** @type {HTMLElement[]} */ (array(document.querySelectorAll(sel)));
        elements.forEach(function (element) {
            /** @type {typeof widget} */ (
                Object.create(widget, { element: { value: element } })
            ).init();
        });
    }

    init();
})();
