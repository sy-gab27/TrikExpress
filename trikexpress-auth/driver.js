document.addEventListener("DOMContentLoaded", function () {
    // Sidebar toggle function
    window.toggleSidebar = function () {
        const sidebar = document.getElementById("sidebar");
        sidebar.classList.toggle("active");
    };
});
