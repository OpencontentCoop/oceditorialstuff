$(document).ready(function () {
    var touch = false;
    if (window.Modernizr) {
        touch = Modernizr.touch;
    }
    if (!touch) {
        $("body").on("mouseenter", ".has-tooltip", function () {
            var el;
            el = $(this);
            if (el.data("tooltip") === undefined) {
                el.tooltip({
                    placement: el.data("placement") || "top",
                    container: el.data("container") || "body"
                });
            }
            return el.tooltip("show");
        });
        $("body").on("mouseleave", ".has-tooltip", function () {
            return $(this).tooltip("hide");
        });
    }
    $('.nav-pills, .nav-tabs').tabdrop();
});

var hash = document.location.hash;
var prefix = "tab_";
if (hash) {
    $('.nav-tabs a[href=' + hash.replace(prefix, "") + ']').tab('show');
}
// Change hash for page-reload
$('.nav-tabs a').on('shown.bs.tab', function (e) {
    window.location.hash = e.target.hash.replace("#", "#" + prefix);
});
$('[data-load-remote]').on('click', function (e) {
    e.preventDefault();
    var $this = $(this);
    $($this.data('remote-target')).html('<em>Loading...</em>');
    var remote = $this.data('load-remote');
    if (remote) {
        $($this.data('remote-target')).load(remote);
    }
});
