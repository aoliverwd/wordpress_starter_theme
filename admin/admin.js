(function () {
    "use strict";

    window.addEventListener("load", function () {

        const toggle_switches = document.querySelectorAll("label.toggle_switch input");
        if (toggle_switches) {
            toggle_switches.forEach(function (this_switch) {
                this_switch.onclick = function () {
                    if (this_switch.checked) {
                        this_switch.value = "checked";
                    } else {
                        this_switch.value = "not_checked";
                        this_switch.checked = false;
                    }
                }
            });
        }

    });
}());