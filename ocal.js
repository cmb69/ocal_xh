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

    /** @readonly */
    var widgetProto = Object.seal({
        /** @readonly @type {HTMLElement} */
        element: undefined,

        /** @type {number} */
        currentState: undefined,

        /** @type {boolean} */
        unsavedChanges: undefined,

        /** @type {NodeListOf<HTMLScriptElement>} */
        get templates() {
            return this.element.querySelectorAll("script[type='text/x-template']");
        },

        /** @type {string} */
        get occupancy() {
            return this.element.dataset.name;
        },

        /** @type {Config} */
        get config() {
            var child = /** @type {HTMLElement} */ (this.element.firstElementChild);
            return JSON.parse(child.dataset.ocalConfig);
        },

        /** @type {HTMLInputElement} */
        get checksumInput() {
            return this.element.querySelector("input[name=ocal_checksum]");
        },

        /** @type {NodeListOf<HTMLElement>} */
        get stateButtons() {
            return this.element.querySelectorAll(".ocal_toolbar span");
        },

        /** @type {HTMLElement} */
        get statusbar() {
            return this.element.querySelector(".ocal_statusbar");
        },

        /** @type {HTMLButtonElement} */
        get saveButton() {
            return this.element.querySelector(".ocal_save");
        },

        /** @type {(again?: boolean) => void} */
        init: function (again) {
            this.templates.forEach(function (script) {
                script.outerHTML = script.text;
            });
            this.currentState = undefined;
            this.markClean();
            this.element.addEventListener("click", this);
            addEventListener("popstate", this);
            if (!again) {
                var url = this.buildUrl(location.href);
                history.replaceState(this.updatedHistoryState(url), "", url);
            }
        },

        /** @type {(event: Event) => void} */
        handleEvent: function (event) {
            switch (event.type) {
                case "popstate":
                    return this.handlePopstateEvent(/** @type {PopStateEvent} */ (event));
                case "click":
                    return this.handleClickEvent(/** @type {MouseEvent} */ (event));
                case "beforeunload":
                    event.preventDefault();
            }
        },

        /** @type {(event: PopStateEvent) => void} */
        handlePopstateEvent: function (event) {
            if (event.state && this.occupancy in event.state.ocalUrls) {
                this.load(event.state.ocalUrls[this.occupancy], true);
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
            if (this.unsavedChanges && !window.confirm(this.config.message_unsaved_changes)) {
                event.preventDefault();
                return;
            }
            var target = /** @type {HTMLAnchorElement} */ (event.target);
            var url = this.buildUrl(target.href);
            this.load(url);
            event.preventDefault();
        },

        /** @type {(url: string) => Object} */
        updatedHistoryState: function (url) {
            var state = history.state || Object.create(null);
            var urls = state.ocalUrls || Object.create(null);
            urls[this.occupancy] = url;
            state.ocalUrls = urls;
            return state;
        },

        /** @type {(url: string, reload?: boolean) => void} */
        load: function (url, reload) {
            this.markClean();
            var request = new XMLHttpRequest();
            request.open("GET", url);
            request.setRequestHeader("X-CMSimple-XH-Request", "ocal");
            request.onreadystatechange = this.handleLoadReadyStateChange.bind(
                this,
                request,
                url,
                reload
            );
            request.send(null);
            this.statusbar.innerHTML = "<progress></progress>";
        },

        /** @type {(request: XMLHttpRequest, url: string, reload: boolean) => void} */
        handleLoadReadyStateChange: function (request, url, reload) {
            if (request.readyState !== 4) return;
            var matches = request.responseText.match(/<!--AJAX START-->[\s\S]*<!--AJAX END-->/);
            if (request.status === 200 && matches) {
                this.element.innerHTML = matches[0];
                if (!reload) history.pushState(this.updatedHistoryState(url), "", url);
                this.init(true);
            } else {
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
            var cells = /** @type {HTMLTableCellElement[]} */ (
                Array.prototype.slice.call(calendar.querySelectorAll("td.ocal_state"))
            );
            return [
                calendar.dataset.ocal_date,
                cells.map(function (cell) {
                    return +cell.dataset.ocal_state;
                }),
            ];
        },

        /** @type {() => {[x: string]: number[]}} */
        getAllCalendarStates: function () {
            var calendars = /** @type {HTMLElement[]} */ (
                Array.prototype.slice.call(this.element.querySelectorAll(".ocal_calendar"))
            );
            return calendars.map(this.getCalendarStates.bind(this)).reduce(function (acc, pair) {
                acc[pair[0]] = pair[1];
                return acc;
            }, /** @type {{[x: string]: number[]}} */ ({}));
        },

        /** @type {(request: XMLHttpRequest) => void} */
        handleSaveReadyStateChange: function (request) {
            if (request.readyState !== 4) return;
            if (request.status === 200) {
                this.markClean();
                var statusbar = this.statusbar;
                statusbar.innerHTML = request.responseText;
                this.checksumInput.value = statusbar.firstChild.textContent;
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
            var cells = /** @type {NodeListOf<HTMLElement>} */ (
                this.element.querySelectorAll(".ocal_calendar td.ocal_state")
            );
            cells.forEach(function (cell) {
                cell.style.cursor = "pointer";
            });
        },

        /** @type {() => void} */
        onSave: function () {
            var request = new XMLHttpRequest();
            request.open("POST", this.buildUrl(location.href, true));
            request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            request.onreadystatechange = this.handleSaveReadyStateChange.bind(this, request);
            request.send(this.getPayload());
            this.statusbar.innerHTML = "<progress></progress>";
        },

        /** @type {(url: string, save?: boolean) => string} */
        buildUrl: function (url, save) {
            var matches = url.match(/(.*)(#.*)?/);
            var res = matches[1];
            if (res.indexOf("?") < 0) {
                res += "?";
            }
            var name = "&ocal_name=" + this.occupancy;
            if (!new RegExp(name).test(res)) {
                res += name;
            }
            if (save) {
                res += "&ocal_action=save";
            }
            if (matches[2]) {
                res += matches[2];
            }
            return res;
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
            var checksumInput = this.checksumInput;
            if (checksumInput) {
                payload += "&ocal_checksum=" + encodeURIComponent(checksumInput.value);
            }
            return payload;
        },
    });

    var containers = /** @type {NodeListOf<HTMLElement>} */ (
        document.querySelectorAll(".ocal_container")
    );
    containers.forEach(function (element) {
        var widget = /** @type {typeof widgetProto} */ (
            Object.create(widgetProto, { element: { value: element } })
        );
        widget.init();
    });
})();
