$('#commentform').on('submit', function (e) {
	e.preventDefault();
	
	if($('#commentform').hasClass("bl-processing")){
		return;
	}
	
	$('#commentform').addClass("bl-processing");
	
	$.ajax({
		url: opBlog.ajax,
		data: $('#commentform').serialize(),
		method: 'POST',
		headers: { "cache-control": "no-cache" },
		beforeSend: function(){
			$('#new_comment_blog_error').hide();
			$('#new_comment_blog_error').html('');
			$('#submitComment').addClass('processing');
		},
		complete: function() {
			$('#commentform').removeClass('bl-processing');
			$('#submitComment').removeClass('processing');
			prestashop.emit('submitCompleteNrtForm', null);
		},
		success: function(resp) {
			if (resp['error']) {
				$.each(resp['error'], function(index, value) {
					$('#new_comment_blog_error').append('<div>'+value+'</div>');
				});
				$('#new_comment_blog_error').slideDown();
			}
			if (resp['success']) {
				$('#new_comment_blog_error').hide();
				alert(resp['success']);
				window.location.reload();
			}
		}
	});
});

var addComment = {
	moveForm : function(commId, parentId, respondId, postId) {

		var t = this, div, comm = t.I(commId), respond = t.I(respondId), cancel = t.I('cancel-comment-reply-link'), parent = t.I('comment_parent'), post = t.I('comment_post_ID');
		if (!comm || !respond || !cancel || !parent){
			return;
		}
					
		t.respondId = respondId;
		postId = postId || false;

		if (!t.I('sm-temp-form-div')) {
			div = document.createElement('div');
			div.id = 'sm-temp-form-div';
			div.style.display = 'none';
			respond.parentNode.insertBefore(div, respond);
		}

		comm.parentNode.insertBefore(respond, comm.nextSibling);
		if (post && postId){
			post.value = postId;
		}
		parent.value = parentId;
		cancel.style.display = '';

		cancel.onclick = function() {
			var t = addComment, temp = t.I('sm-temp-form-div'), respond = t.I(t.respondId);

			if (!temp || !respond){
				return;
			}

			t.I('comment_parent').value = '0';

			temp.parentNode.insertBefore(respond, temp);
			temp.parentNode.removeChild(temp);
			this.style.display = 'none';
			this.onclick = null;
			return false;
		};

		try { t.I('comment').focus(); }
		catch(e) {}

		return false;
	},

	I : function(e) {
		var elem = document.getElementById(e);
		if(!elem){
			return document.querySelector('[name="'+e+'"]');
		}else{
			return elem;
		}
	}
}; 