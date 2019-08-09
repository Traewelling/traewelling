var postId = 0;
var postBodyElement = null;

$(document).on('click','.edit',function(event) {
    console.log('edit');
    event.preventDefault();

    postBodyElement = event.target.parentNode.parentNode.childNodes[1];
    var postBody = postBodyElement.textContent;
    postId = event.target.parentNode.parentNode.dataset['postid'];
    $('#post-body').val(postBody);
    $('#edit-modal').modal();
});

$(document).on('click','.delete',function(event) {
    console.log('delete');
    event.preventDefault();
    postId = event.target.parentNode.parentNode.dataset['postid'];
    $.ajax({
        method: 'POST',
        url: urlDelete,
        data: {postId: postId, _token: token}
    })
        .done(function (msg) {
            event.target.parentNode.parentNode.parentNode.remove();
        });
});

$(document).on('click', '#modal-save', function () {
    $.ajax({
        method: 'POST',
        url: urlEdit,
        data: {body: $('#post-body').val(), postId: postId, _token: token}
    })
        .done(function (msg) {
            $(postBodyElement).text(msg['new_body']);
            $('#edit-modal').modal('hide');
        });
});


$(document).on('click', '.like', function(event) {
    event.preventDefault();
    postId = event.target.parentNode.parentNode.dataset['postid'];
    var isLike = event.target.previousElementSibling == null;
    $.ajax({
        method: 'POST',
        url: urlLike,
        data: {isLike: isLike, postId: postId, _token: token}
    })
        .done(function() {
            event.target.innerText = isLike ? event.target.innerText == 'Like' ? 'You like this post' : 'Like' : event.target.innerText == 'Dislike' ? 'You don\'t like this post' : 'Dislike';
            if (isLike) {
                event.target.nextElementSibling.innerText = 'Dislike';
            } else {
                event.target.previousElementSibling.innerText = 'Like';
            }
        });
});
