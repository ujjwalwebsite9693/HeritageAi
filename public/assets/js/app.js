// public/assets/js/app.js - Client interaction utilities

document.addEventListener("DOMContentLoaded", function() {
    // Smooth dismiss for flash notifications
    document.querySelectorAll('.flash-alert').forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.4s ease';
            alert.style.opacity = '0';
            setTimeout(function() { alert.remove(); }, 400);
        }, 6000);
    });

    // Auto-attach CSRF tokens to AJAX requests if present
    var metaCsrf = document.querySelector('input[name="csrf_token"]');
    if (metaCsrf) {
        window.csrfToken = metaCsrf.value;
    }
});

// Utility to copy academic citation
function copyText(elementId) {
    var text = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(text).then(function() {
        alert("Copied to clipboard!");
    });
}

