/* eslint-disable no-inner-declarations */
(function() {
    var darkSwitch = document.getElementById("darkSwitch");
    if (darkSwitch) {
        initTheme();
        darkSwitch.addEventListener("click", function(event) {
            resetTheme();
        });

        function initTheme() {
            if (localStorage.getItem("darkSwitch") === null) {
                document.body.removeAttribute("data-theme");
            } else if (localStorage.getItem("darkSwitch") === "dark") {
                document.body.setAttribute("data-theme", "dark");
            } else {
                document.body.setAttribute("data-theme", "night");
            }
        }
        function resetTheme() {
            if (localStorage.getItem("darkSwitch") === null) {
                document.body.setAttribute("data-theme", "dark");
                localStorage.setItem('darkSwitch', 'dark');
            } else if (localStorage.getItem("darkSwitch") === "dark") {
                document.body.setAttribute("data-theme", "night");
                localStorage.setItem('darkSwitch', 'night');
            } else {
                document.body.removeAttribute("data-theme");
                localStorage.removeItem('darkSwitch');
            }
        }
    }
})();
