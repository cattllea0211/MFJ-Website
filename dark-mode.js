// Check and apply dark mode preference on page load
document.addEventListener("DOMContentLoaded", function () {
    const darkModeToggle = document.getElementById("darkModeToggle");

    // Check if dark mode is enabled in localStorage
    const isDarkMode = localStorage.getItem("darkMode") === "true";

    // Apply dark mode
    if (isDarkMode) {
        document.body.classList.add("dark-mode");
        darkModeToggle.checked = true; // Set the toggle to "on"
    }

    // Attach the toggleDarkMode function to the checkbox
    darkModeToggle.addEventListener("change", toggleDarkMode);
});

// Toggle dark mode
function toggleDarkMode() {
    const isDarkModeEnabled = document.body.classList.toggle("dark-mode");

    // Save the state in localStorage
    localStorage.setItem("darkMode", isDarkModeEnabled);
}
