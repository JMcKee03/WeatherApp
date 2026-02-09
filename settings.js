


// Get the dark mode toggle checkbox
const darkModeToggle = document.getElementById('darkModeToggle');

// Check if dark mode was previously enabled and set the initial state
if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
    darkModeToggle.checked = true;
}

// Listen for changes on the checkbox
darkModeToggle.addEventListener('change', function () {
    // Toggle the dark-mode class
    document.body.classList.toggle('dark-mode', darkModeToggle.checked);

    // Store the dark mode state in localStorage
    if (darkModeToggle.checked) {
        localStorage.setItem('darkMode', 'enabled');
    } else {
        localStorage.setItem('darkMode', 'disabled');
    }
});


//          __________Notifications_______________
document.addEventListener("DOMContentLoaded", function () {
    const notificationsToggle = document.getElementById("notificationsToggle");

    notificationsToggle.addEventListener("change", function () {
        if (this.checked) {
            enableNotifications();
        } else {
            disableNotifications();
        }
    });
});

function enableNotifications() {
    if (Notification.permission === "granted") {
        new Notification("Notifications Enabled!");
    } else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                new Notification("Notifications Enabled!");
            } else {
                alert("You blocked notifications. Please allow them in your browser settings.");
            }
        });
    }
}

function disableNotifications() {
    alert("Notifications Disabled!");
}

// _____DELETE ACCOUNT SCRIPT_____

// Open delete confirmation modal
document.getElementById("deleteBtn").addEventListener("click", function() {
    document.getElementById("deleteFormContainer").style.display = "block";
});

// Close the modal
document.querySelectorAll(".close").forEach(function(element) {
    element.addEventListener("click", function() {
        document.getElementById("deleteFormContainer").style.display = "none";
    });
});
