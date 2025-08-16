window.AuthScripts = {
    showHidePass: function (t) {
        t.classList.toggle("show");
        let e = t.parentElement.querySelector("input");
        e && (e.type === "password" ? (e.type = "text", t.classList.add("active")) : (e.type = "password", t.classList.remove("active")))
    }
};
document.addEventListener("DOMContentLoaded", function () {
    AuthScripts.init()
});
