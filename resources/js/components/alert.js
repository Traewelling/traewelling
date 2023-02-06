bootstrap_alert = function() {}
bootstrap_alert.warning = function(message) {
    window.scrollTo(0, 0);
    $('#alert_placeholder').append('<div class="alert alert-warning alert-block"><a class="close" data-mdb-dismiss="alert">×</a><span>'+message+'</span></div>')
}
bootstrap_alert.danger = function(message) {
    window.scrollTo(0, 0);
    $('#alert_placeholder').append('<div class="alert alert-danger alert-block"><a class="close" data-mdb-dismiss="alert">×</a><span>'+message+'</span></div>')
}
bootstrap_alert.success = function(message) {
    window.scrollTo(0, 0);
    $('#alert_placeholder').append('<div class="alert alert-success alert-block"><a class="close" data-mdb-dismiss="alert">×</a><span>'+message+'</span></div>')
}
bootstrap_alert.info = function(message) {
    window.scrollTo(0, 0);
    $('#alert_placeholder').append('<div class="alert alert-info alert-block"><a class="close" data-mdb-dismiss="alert">×</a><span>'+message+'</span></div>')
}
