function toggleSidebar() {
    let sidebar = document.querySelector(".sidebar");
    let content = document.querySelector(".content");

    if (sidebar.style.left === "0px") {
        sidebar.style.left = "-250px";
        content.style.marginLeft = "0";
    } else {
        sidebar.style.left = "0px";
        content.style.marginLeft = "250px";
    }
}
