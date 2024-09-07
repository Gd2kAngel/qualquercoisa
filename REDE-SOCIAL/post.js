document.getElementById('postForm').addEventListener('submit', function (event) {
    event.preventDefault(); 

    const formData = new FormData(this);
    const message = document.getElementById('message');

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'create_post.php', true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            message.innerHTML = xhr.responseText;
            document.getElementById('content').value = ''; 
            document.getElementById('image').value = ''; 
            loadPosts(); 
        } else {
            message.innerHTML = "Erro ao enviar o post.";
        }
    };

    xhr.send(formData);
});

function loadPosts() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_posts.php', true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById('feed').innerHTML = xhr.responseText;
        } else {
            document.getElementById('feed').innerHTML = "Erro ao carregar os posts.";
        }
    };

    xhr.send();
}

window.onload = loadPosts;

setInterval(loadPosts, 10000);