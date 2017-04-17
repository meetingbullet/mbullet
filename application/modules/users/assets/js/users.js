$(document).ready(function() {
    // Set avatar preview as user input
    if (document.getElementById('user-avatar')) {
        document.getElementById('user-avatar').onchange = function(evt) {
            var tgt = evt.target || window.event.srcElement,
                files = tgt.files;

            // FileReader support
            if (FileReader && files && files.length) {
                var fr = new FileReader();
                fr.onload = function() {
                    document.getElementById("user-avatar-preview").src = fr.result;
                }
                fr.readAsDataURL(files[0]);
            }
            // Not supported
            else {
                // fallback -- perhaps submit the input to an iframe and temporarily store
                // them on the server until the user's session ends.
            }
        }
    }
})