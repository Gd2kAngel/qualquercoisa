const btn_theme = document.querySelector(".theme-btn");
const theme_ball = document.querySelector(".theme-ball");

const localData = localStorage.getItem("theme");

if (localData == null) {
    localStorage.setItem("theme", "light");
}

if (localData == "dark") {
    document.body.classList.add("dark-mode");
    theme_ball.classList.add("dark");
} else if (localData == "light") {
    document.body.classList.remove("dark-mode");
    theme_ball.classList.remove("dark");
}

btn_theme.addEventListener("click", function () {
    document.body.classList.toggle("dark-mode");
    theme_ball.classList.toggle("dark");
    if (document.body.classList.contains("dark-mode")) {
        localStorage.setItem("theme", "dark");
    } else {
        localStorage.setItem("theme", "light");
    }
});