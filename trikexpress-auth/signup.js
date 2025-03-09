document.getElementById("openTerms").addEventListener("click", function (event) {
    event.preventDefault();
    document.getElementById("termsModal").style.display = "block";
});

document.querySelector(".close").addEventListener("click", function () {
    document.getElementById("termsModal").style.display = "none";
});

window.onclick = function (event) {
    if (event.target === document.getElementById("termsModal")) {
        document.getElementById("termsModal").style.display = "none";
    }
};
