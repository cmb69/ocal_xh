/*!
 * Front-end JavaScript of Ocal_XH
 *
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2015 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

/*jslint browser: true, maxlen: 80 */

(function () {
    "use strict";

    function each(elements, callback) {
        var i;

        for (i = 0; i < elements.length; i += 1) {
            callback(elements[i]);
        }
    }

    function init() {
        var elements;

        elements = document.querySelectorAll(".ocal_pagination a");
        each(elements, function (element) {
            element.onclick = function () {
                var request, calendar;

                request = new XMLHttpRequest();
                request.open("GET", this.href + "&ocal_ajax=1");
                request.onreadystatechange = function () {
                    if (request.readyState === 4) {
                        calendar.outerHTML = request.responseText;
                        init();
                    }
                };
                request.send(null);
                calendar = element.parentNode.parentNode;
                calendar.style.opacity = 0.2;
                return false;
            };
        });
    }

    init();
}());
