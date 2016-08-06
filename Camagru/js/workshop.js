document.ready(function () {
	var currentMask;
	var video = document.getElementById("camera");
	var play = document.getElementById("play");
	var pause = document.getElementById("pause");
	var stop = document.getElementById("stop");
	var capture = document.getElementById("capture");

	function disableButtons(disPlay, disPause, disStop, disCapture) {
		play.disabled = disPlay;
		pause.disabled = disPause;
		capture.disabled = disCapture;
		stop.disabled = disStop;
	}

	disableButtons(true, true, true, true);

	setCamera(video, function (data) {
		if (data)
		{
			wc('#no_cam_feedback').addClass("hidden");
			disableButtons(true, false, false, false);
			video.play();
		}
		else
		{
			wc('#camera').addClass("hidden");
			wc('#canvas').removeClass("hidden");
		}
	});

	function processLastImagesList(response)
	{
		if (response instanceof Object == false)
			response = JSON.tryParse(response);
		if (response && response.status)
		{
			if (response.status == 'OK')
			{
				wc('#last_images_list').html(response.data.images_list);
			}
			else if (response.status == 'KO')
			{
				wc('error').html(response.data.message).removeClass('hidden');
			}
		}
		else
		{
			wc('error').html('Error fetching last images...').removeClass('hidden');
		}
	}
	showLoadingOverlay();
	wc.ajax(camagru_home_link + 'workshop', {action:'getLastMixedImages'}, function (response) {
		hideLoadingOverlay();
		processLastImagesList(response);
	});

	document.on('click', '#logout', function (e) {
		e.preventDefault();
		wc.post(camagru_home_link + 'login', {action:'logout'});
	});

	document.on('click', 'img.mask_button', function (e) {
		e.preventDefault();
		currentMask = e.target.data('id');
		wc('#preview-overlay').src = e.target.src;
	});
	wc('img.mask_button').click();

	play.on("click", function() {
		disableButtons(true, false, false, false);
		video.play();
	});

	pause.on("click", function() {
		disableButtons(false, true, false, false);
		video.pause();
	});

	stop.on("click", function() {
		disableButtons(true, true, true, true);
		video.pause();
		video.src = "";
		wc('#camera').addClass("hidden");
		wc('#canvas').removeClass("hidden");
	});

	wc('input[type=file]').on('change', function(e) {
		e.preventDefault();
		if (e.target.files && e.target.files[0])
		{
			var reader = new FileReader();
			reader.onload = function (e) {
				var canvas = wc("canvas");
				var context = canvas.getContext('2d');
				var img = new Image;
				img.src = e.target.result;
				img.width = 800;
				img.height = 600;
				context.drawImage(img, 0, 0, 800, 600);
				disableButtons(true, true, true, false);
			};
			reader.readAsDataURL(e.target.files[0]);
			stop.click();
		}
	});

	capture.on("click", function () {
		var canvas = wc("canvas");
		if (video.hasClass('hidden') == false)
		{
			var context = canvas.getContext('2d');
			context.drawImage(video, 0, 0, 800, 600);
		}
		var dt = canvas.toDataURL('image/jpeg');
		var params = {
			'action': 'MixImages',
			'raw_photo': dt,
			'mask': currentMask ? currentMask : 0
		};
		showLoadingOverlay();
		wc.ajax(camagru_home_link + "workshop", params, function (response) {
			console.log(response);
			hideLoadingOverlay();
			processLastImagesList(response);
		});
	});

	document.on('click', '.last_images', function (e) {
		e.preventDefault();
		var id_img = e.target.data('id');
		var params = {
			action: 'viewImage',
			id_image: id_img
		};
		wc.post(camagru_home_link + 'gallery', params);
	});
});