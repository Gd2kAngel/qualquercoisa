const btn_menu = document.querySelector(".btn-menu");
const side_bar = document.querySelector(".sidebar");
const searchBtn = document.querySelector(".search-btn");
const searchInput = document.querySelector(".search-input");

let isSidebarOpen = false;

btn_menu.addEventListener("click", function () {
    side_bar.classList.toggle("expand");
    changebtn();
    isSidebarOpen = !isSidebarOpen;
});

searchBtn.addEventListener("click", function () {
    if (!isSidebarOpen) {
        side_bar.classList.add("expand");
        changebtn();
        isSidebarOpen = true;
    } else {
        searchInput.focus();
    }
});

function changebtn() {
    btn_menu.classList.toggle("bx-menu-alt-right");
    btn_menu.classList.toggle("bx-menu");
}

document.querySelectorAll('.circle').forEach(circle => {
    circle.addEventListener('click', function() {
        var modalId = this.getAttribute('data-modal');
        document.getElementById(modalId).style.display = 'block';
    });
});

document.querySelectorAll('.close').forEach(closeBtn => {
    closeBtn.addEventListener('click', function() {
        this.closest('.modal').style.display = 'none';
    });
});

// Fechar modal ao clicar fora dela
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
};
