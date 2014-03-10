$(document).ready(function() {
	$('input').focusout(function() {
		if ($(this).val() == '') {
			$(this).addClass('invalid');
			$(this).next().css('visibility', 'visible');
		}
		else {
			$(this).removeClass('invalid');
			$(this).next().css('visibility', 'hidden');
		}
	});

	$('#loginForm').submit(function(event) {
		//password is first so username gets focus if both are empty
		if ($('#password').val() == '') {
			$('#password').addClass('invalid');
			$('#passwordError').show();
			$('#password').focus();
		}
		if ($('#username').val() == '') {
			$('#username').addClass('invalid');
			$('#usernameError').show();
			$('#username').focus();
		}
		
		if ($('#username').val() != '' && $('#password').val() != '') {
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: {
					'action': 'login',
					'username': $('#username').val(),
					'password': $('#password').val()
				}
			}).done(function(data) {
				console.log(data);
				if (data.status == 'success') {
					console.log(data.redirect);
					window.location.replace(data.redirect);
				}
				else {
					$('#username').val('');
					$('#password').val('');
					$('#loginError').css('visibility', 'visible');
				}
			});
		}
		event.preventDefault();
	});
});