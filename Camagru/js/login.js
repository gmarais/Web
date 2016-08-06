document.ready(function () {
// VALIDATION
	document.on('keydown', function (e) {
		if (e.keyCode == 13)
		{
			var submit = wc('#login');
			if (submit)
				return submit.click();
			submit = wc('#register');
			if (submit)
				return submit.click();
		}
	});
// LOGIN
	document
	.on('click', '#no_account', function (e) {
		e.preventDefault();
		var params = {
			action:'RenderHelper',
			helper_name: 'registerForm.html'
		};
		showLoadingOverlay();
		wc.ajax(camagru_home_link + 'login', params, function (data) {
			hideLoadingOverlay();
			wc('content').html(data);
			wc('#register').disabled = true;
		});
	})
	.on('click', '#login', function (e) {
		e.preventDefault();
		var params = {
			action:'login',
			nickname: wc('#nickname_input').value,
			password: wc('#password_input').value
		};
		showLoadingOverlay();
		wc.ajax(camagru_home_link + 'login', params, function (response) {
			hideLoadingOverlay();
			if (response)
				response = JSON.tryParse(response);
			if (response && response.status)
			{
				if (response.status == 'OK')
				{
					window.location.assign(camagru_home_link + 'workshop');
				}
				else if (response.status == 'KO')
				{
					wc('error').html(response.data.message).removeClass('hidden');
				}
			}
			else
			{
				wc('error').html('Error processing the request...').removeClass('hidden');
			}
		});
	});
// REGISTER
	function checkForm()
	{
		var nickname = wc('#nickname_input').value;
		var password = wc('#password_input').value;
		var confirm = wc('#confirm_input').value;
		var email = wc('#email_input').value;
		var reg_email = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		var error = wc('error');
		if (nickname.length < 3)
		{
			error.html('Nickname is too short...').removeClass('hidden');
			return false;
		}
		if (nickname.length > 24)
		{
			error.html('Nickname is too long...').removeClass('hidden');
			return false;
		}
		if (password.length < 6 || !password.match(/\d\D|\D\d/))
		{
			error.html('Password is not secure...').removeClass('hidden');
			return false;
		}
		else
		{
			if (password !== confirm)
			{
				error.html('Password does not match confirm...').removeClass('hidden');
				return false;
			}
		}
		if (!reg_email.test(email))
		{
			error.html('Email is invalid...').removeClass('hidden');
			return false;
		}
		error.addClass('hidden');
		return true;
	}
	document
	.on('click', '#has_account', function (e) {
		e.preventDefault();
		var params = {
			action:'RenderHelper',
			helper_name: 'loginForm.html'
		};
		showLoadingOverlay();
		wc.ajax(camagru_home_link + 'login', params, function (data) {
			hideLoadingOverlay();
			wc('content').html(data);
		});
	})
	.on('input', '#nickname_input', function (e) {
		if (wc('#register'))
			wc('#register').disabled = !checkForm();
	})
	.on('input', '#password_input', function (e) {
		if (wc('#register'))
			wc('#register').disabled = !checkForm();
	})
	.on('input', '#confirm_input', function (e) {
		wc('#register').disabled = !checkForm();
	})
	.on('input', '#email_input', function (e) {
		wc('#register').disabled = !checkForm();
	})
	.on('click', '#register', function (e) {
		e.preventDefault();
		var params = {
			action:'register',
			email: wc('#email_input').value,
			nickname: wc('#nickname_input').value,
			password: wc('#password_input').value
		};
		showLoadingOverlay();
		wc.ajax(camagru_home_link + 'login', params, function (response) {
			hideLoadingOverlay();
			if (response)
				response = JSON.tryParse(response);
			if (response && response.status)
			{
				if (response.status == 'OK')
				{
					showLoadingOverlay();
					wc.ajax(camagru_home_link + 'login', {action:'renderHelper', helper_name:'verifyYourEmail.html'}, function (data) {
						hideLoadingOverlay();
						wc('content').html(data);
					});
				}
				else if (response.status == 'KO')
				{
					wc('error').html(response.data.message).removeClass('hidden');
					wc('#register').disabled = true;
				}
			}
			else
			{
				wc('error').html('Error processing the request...').removeClass('hidden');
				wc('#register').disabled = true;
			}
		});
	});
});