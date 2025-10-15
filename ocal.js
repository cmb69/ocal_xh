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
        /** @type {number} */
        currentState: undefined,
        /** @type {boolean} */
        unsavedChanges: undefined,
        /** @type {string} */
        get occupancy() {
            return this.element.dataset.name;
        },
        /** @type {Config} */
        get config() {
            var child = /** @type {HTMLElement} */ (this.element.firstElementChild);
            return JSON.parse(child.dataset.ocalConfig);
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
        /** @type {HTMLButtonElement} */
        get saveButton() {
            return this.element.querySelector(".ocal_save");
        },
        /** @type {() => void} */
        init: function () {
            this.currentState = undefined;
            this.markClean();
            this.element.addEventListener("click", this);
        },
        /** @type {(event: Event) => void} */
        handleEvent: function (event) {
            switch (event.type) {
                case "click":
                    return this.handleClickEvent(/** @type {MouseEvent} */ (event));
                case "beforeunload":
                    this.warning(event);
            }
        },
        /** @type {(event: MouseEvent) => void} */
        handleClickEvent: function (event) {
            var target = /** @type {Element} */ (event.target);
            switch (target.classList[0]) {
                case "ocal_button":
                    return this.onModeOrPaginationClick(/** @type {MouseEvent} */ (event));
                case "ocal_state":
                    switch (target.localName) {
                        case "span":
                            return this.selectState(/** @type {HTMLElement} */ (event.target));
                        case "td":
                            return this.changeState(/** @type {HTMLElement} */ (event.target));
                    }
                    break;
                case "ocal_save":
                    return this.onSave();
            }
        },
        /** @type {(event: MouseEvent) => void} */
        onModeOrPaginationClick: function (event) {
            var target = /** @type {HTMLAnchorElement} */ (event.target);
            if (this.unsavedChanges && !window.confirm(this.config.message_unsaved_changes)) {
                event.preventDefault();
                return;
            }
            this.markClean();
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
            if (request.readyState !== 4) return;
            var matches = request.responseText.match(/<!--AJAX START-->[\s\S]*<!--AJAX END-->/);
            if (request.status === 200 && matches) {
                this.element.innerHTML = matches[0];
                this.init();
            } else {
                this.loaderbar.style.display = "none";
                this.statusbar.innerHTML =
                    '<p class="xh_fail">' + request.status + " " + request.statusText + "</p>";
            }
        },
        /** @type {() => void} */
        markClean: function () {
            this.unsavedChanges = false;
            removeEventListener("beforeunload", this);
            var saveButton = this.saveButton;
            if (saveButton) {
                saveButton.disabled = true;
            }
        },
        /** @type {() => void} */
        markDirty: function () {
            this.unsavedChanges = true;
            addEventListener("beforeunload", this);
            this.saveButton.disabled = false;
        },
        /** @type {(element: HTMLElement) => void} */
        changeState: function (element) {
            if (typeof this.currentState !== "number") return;
            var state = element.dataset.ocal_state;
            if (state !== undefined && +state !== this.currentState) {
                element.dataset.ocal_state = this.currentState.toString();
                this.statusbar.innerHTML = "";
                this.markDirty();
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
            if (request.readyState !== 4) return;
            this.loaderbar.style.display = "none";
            if (request.status === 200) {
                this.markClean();
                this.statusbar.innerHTML = request.responseText;
            } else if (request.responseText) {
                this.statusbar.innerHTML = request.responseText;
            } else {
                this.statusbar.innerHTML =
                    '<p class="xh_fail">' + request.status + " " + request.statusText + "</p>";
            }
        },
        /** @type {(element: HTMLElement) => void} */
        selectState: function (element) {
            if (element.dataset.ocal_state === undefined) return;
            this.stateButtons.forEach(function (element) {
                element.style.borderWidth = "";
            });
            this.currentState = +element.dataset.ocal_state;
            element.style.borderWidth = "3px";
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
            request.onreadystatechange = this.handleSaveReadyStateChange.bind(this, request);
            request.send(this.getPayload());
            this.loaderbar.style.display = "block";
        },
        /** @type {() => string} */
        getPayload: function () {
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
            return payload;
        },
        /** @type {(event: Event) => string} */
        warning: function (event) {
            var confirmation = this.config.message_unsaved_changes;
            // @ts-ignore
            event.returnValue = confirmation;
            return confirmation;
        },
    });

    /** @type {HTMLElement[]} */ (array(document.querySelectorAll(".ocal_container"))).forEach(
        function (element) {
            /** @type {typeof widget} */ (
                Object.create(widget, { element: { value: element } })
            ).init();
        }
    );
})();
