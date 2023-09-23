window.bootstrap_alert = function () { }
window.bootstrap_alert.warning = function (message) {
    window.scrollTo(0, 0);
    $('#alert_placeholder').append('<div class="alert alert-dismissible alert-warning alert-block"><a class="close" data-mdb-dismiss="alert">×</a><span>'+message+'</span></div>')
}
window.bootstrap_alert.danger = function (message) {
    window.scrollTo(0, 0);
    $('#alert_placeholder').append('<div class="alert alert-dismissible alert-danger alert-block"><a class="close" data-mdb-dismiss="alert">×</a><span>'+message+'</span></div>')
}
window.bootstrap_alert.success = function (message) {
    window.scrollTo(0, 0);
    $('#alert_placeholder').append('<div class="alert alert-dismissible alert-success alert-block"><a class="close" data-mdb-dismiss="alert">×</a><span>'+message+'</span></div>')
}
window.bootstrap_alert.info = function (message) {
    window.scrollTo(0, 0);
    $('#alert_placeholder').append('<div class="alert alert-dismissible alert-info alert-block"><a class="close" data-mdb-dismiss="alert">×</a><span>'+message+'</span></div>')
}
