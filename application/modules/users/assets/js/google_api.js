function onSignIn(googleUser) {
	// Useful data for your client-side scripts:
	var profile = googleUser.getBasicProfile();
	// console.log("ID: " + profile.getId()); // Don't send this directly to your server!
	// console.log('Full Name: ' + profile.getName());
	// console.log('Given Name: ' + profile.getGivenName());
	// console.log('Family Name: ' + profile.getFamilyName());
	// console.log("Image URL: " + profile.getImageUrl());
	// console.log("Email: " + profile.getEmail());

	// The ID token you need to pass to your backend:
	var id_token = googleUser.getAuthResponse().id_token;
	var email = profile.getEmail();
	// console.log("ID Token: " + id_token);

	$.get(
		location.href, {
			'login_via_google': true,
			'gg_token': id_token
		}
	).done(function (data) {
		data = JSON.parse(data);
		if (data.status == 'success') {
			location.href = data.redirect;
		}
	});
};

function signOut(redirect) {
	gapi.load('auth2', function () {
		gapi.auth2.init().then(function () {
			var auth2 = gapi.auth2.getAuthInstance();
			if (auth2.isSignedIn.get() == true) {
				auth2.disconnect().then(function () {
					console.log('User signed out.');
					location.href = redirect;
				});
			} else {
				location.href = redirect;
			}
		});
	});


};