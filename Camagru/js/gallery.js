function feedImages(size, offset)
{
	var params = {
		action: 'feedImages',
		feed_offset: offset,
		feed_size: size
	};
	wc('#loading_feed').removeClass('hidden');
	wc('#end_feed').addClass('hidden');
	wc.ajax(camagru_home_link + 'gallery', params, function(response) {
		wc('#loading_feed').addClass('hidden');
		wc('#end_feed').removeClass('hidden');
		response = JSON.tryParse(response);
		if (response && response.status)
		{
			if (response.status == 'OK')
			{
				if (response.data.feed)
				{
					var tmp = document.createElement('div');
					tmp.append(response.data.feed);
					var feed = tmp.querySelectorAll('.feed');
					for (var i= 0; i < feed.length; i++)
						wc('#images_feed_container').appendChild(feed[i]);
					tmp.remove();
				}
			}
			else if (response.status == 'KO')
			{
				wc('error').html(response.data.message).removeClass('hidden');
			}
		}
		else
		{
			wc('error').html('Error fetching the images feed...').removeClass('hidden');
		}
	});
}

function feedComments(size, offset)
{
	var params = {
		action: 'feedComments',
		id_image: wc('#comments_feed_container').data('id'),
		feed_offset: offset,
		feed_size: size
	};
	wc('#loading_feed').removeClass('hidden');
	wc('#end_feed').addClass('hidden');
	wc.ajax(camagru_home_link + 'gallery', params, function(response) {
		wc('#loading_feed').addClass('hidden');
		wc('#end_feed').removeClass('hidden');
		response = JSON.tryParse(response);
		if (response && response.status)
		{
			if (response.status == 'OK')
			{
				if (response.data.feed)
				{
					var tmp = document.createElement('div');
					tmp.append(response.data.feed);
					var feed = tmp.querySelectorAll('.feed');
					for (var i= 0; i < feed.length; i++)
						wc('#comments_feed_container').appendChild(feed[i]);
					tmp.remove();
				}
			}
			else if (response.status == 'KO')
			{
				wc('error').html(response.data.message).removeClass('hidden');
			}
		}
		else
		{
			wc('error').html('Error fetching the comments feed...').removeClass('hidden');
		}
	});
}

function feed(size, offset)
{
	if (wc('#loading_feed').hasClass('hidden'))
	{
		if (wc('#images_feed_container'))
			feedImages(size, offset);
		if (wc('#comments_feed_container'))
			feedComments(size, (offset - 1 >= 0) ? offset - 1 : 0);
	}
}

document.ready(function () {
	var last_feed_height = 0;
	feed(3, 0);

	document.on('scroll', function(e) {
		if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight
			&& document.body.offsetHeight > last_feed_height)
		{
			last_feed_height = document.body.offsetHeight;
			var offset = document.querySelectorAll('.feed').length;
			feed(3, offset);
		}
	});

	document.on('click', '#end_feed', function (e) {
		e.preventDefault();
		var offset = document.querySelectorAll('.feed').length;
		feed(3, offset);
	});

	document.on('click', '#logout', function (e) {
		e.preventDefault();
		wc.post(camagru_home_link + 'login', {action:'logout'});
	});

	document.on('click', '.delete_image', function (e) {
		e.preventDefault();
		var params = {
			action: 'deleteImage',
			id_image: e.target.data('id')
		};
		showLoadingOverlay();
		wc.ajax(camagru_home_link + 'workshop', params, function (response) {
			hideLoadingOverlay();
			response = JSON.tryParse(response);
			if (response && response.status)
			{
				if (response.status == 'OK')
				{
					if (wc('#comments_feed_container'))
						window.location.assign(camagru_home_link);
					var elem = e.target;
					while (elem && !elem.hasClass('feed') && elem != document)
						elem = elem.parentNode;
					if (elem.hasClass('feed'))
						elem.remove();
				}
				else if (response.status == 'KO')
				{
					wc('error').html(response.data.message).removeClass('hidden');
				}
			}
			else
			{
				wc('error').html('Error deleting the image...').removeClass('hidden');
			}
		});
	});

	document.on('click', '.like', function (e) {
		e.preventDefault();
		e.target.disabled = true;
		var id_img = e.target.data('id');
		var params = {
			action: 'likeUnlikeImage',
			id_image: id_img
		};
		showLoadingOverlay();
		wc.ajax(camagru_home_link + 'gallery', params, function (response) {
			hideLoadingOverlay();
			e.target.disabled = false;
			response = JSON.tryParse(response);
			if (response && response.status)
			{
				if (response.status == 'OK')
				{
					wc('#likes_count_' + id_img).html('Likes: ' + response.data.likes);
					if (e.target.hasClass('unlike'))
					{
						e.target.removeClass('unlike');
						e.target.html('Like');
					}
					else
					{
						e.target.addClass('unlike');
						e.target.html('Unlike');
					}
				}
				else if (response.status == 'KO')
				{
					wc('error').html(response.data.message).removeClass('hidden');
				}
			}
			else
			{
				wc('error').html('Error deleting the image...').removeClass('hidden');
			}
		});
	});

	document.on('click', '.feed_image', function (e) {
		e.preventDefault();
		var id_img = e.target.data('id');
		var params = {
			action: 'viewImage',
			id_image: id_img
		};
		wc.post(camagru_home_link + 'gallery', params);
	});

	document.on('click', '#comment_button', function (e) {
		e.preventDefault();
		wc('#comment_form').removeClass('hidden');
		e.target.addClass('hidden');
	});

	document.on('click', '#cancel_comment', function (e) {
		e.preventDefault();
		wc('#comment_form').addClass('hidden');
		wc('#comment_button').removeClass('hidden');
	});

	document.on('click', '#send_comment', function (e) {
		e.preventDefault();
		e.target.disabled = true;
		var id_img = e.target.data('id');
		var comment_text = wc('#comment_text').value;
		var params = {
			action: 'commentImage',
			id_image: id_img,
			comment_text: comment_text
		};
		showLoadingOverlay();
		wc.ajax(camagru_home_link + 'gallery', params, function (response) {
			hideLoadingOverlay();
			console.log(response);
			e.target.disabled = false;
			response = JSON.tryParse(response);
			if (response && response.status)
			{
				if (response.status == 'OK')
				{
					wc('#comments_count_' + id_img).html('Comments: ' + response.data.comments);
					wc('#comment_text').value = '';
					wc('#cancel_comment').click();
					wc('#end_feed').click();
				}
				else if (response.status == 'KO')
				{
					wc('error').html(response.data.message).removeClass('hidden');
				}
			}
			else
			{
				wc('error').html('Error uploading the comment...').removeClass('hidden');
			}
		});
	});

	document.on('input', '#comment_text', function (e) {
		if (e.target.value.length > 3)
			wc('#send_comment').disabled = false;
		else
			wc('#send_comment').disabled = true;
	});
	var send_button = wc('#send_comment');
	if (send_button)
		send_button.disabled = true;

	document.on('click', '.delete_comment', function (e) {
		e.preventDefault();
		var params = {
			action: 'deleteComment',
			id_comment: e.target.data('id')
		};
		showLoadingOverlay();
		wc.ajax(camagru_home_link + 'gallery', params, function (response) {
			hideLoadingOverlay();
			response = JSON.tryParse(response);
			if (response && response.status)
			{
				if (response.status == 'OK')
				{
					wc('#comments_count_' + wc('#send_comment').data('id')).html('Comments: ' + response.data.comments);
					var elem = e.target;
					while (elem && !elem.hasClass('feed') && elem != document)
						elem = elem.parentNode;
					if (elem.hasClass('feed'))
						elem.remove();
				}
				else if (response.status == 'KO')
				{
					wc('error').html(response.data.message).removeClass('hidden');
				}
			}
			else
			{
				wc('error').html('Error deleting the image...').removeClass('hidden');
			}
		});
	});
});