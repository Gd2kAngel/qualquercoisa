<?php
session_start();
include 'db_connect.php';

$default_image = '/assets/images/default_image.jpg';
$user_id = 1; // Simulação do usuário logado
$profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : $default_image;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['content'])) {
        $content = htmlspecialchars($_POST['content']);
        $post_image_path = null;

        // Verifica se o usuário enviou uma imagem junto com o post
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $postTmpPath = $_FILES['image']['tmp_name'];
            $postFileName = basename($_FILES['image']['name']);
            $postUploadFileDir = __DIR__ . '/../uploads/posts/';
            $post_dest_path = $postUploadFileDir . $postFileName;

            if (!is_dir($postUploadFileDir)) {
                mkdir($postUploadFileDir, 0777, true);
            }

            if (move_uploaded_file($postTmpPath, $post_dest_path)) {
                $post_image_path = '../uploads/posts/' . $postFileName;
            }
        }

        // Insere o post no banco de dados
        $sql = "INSERT INTO posts (user_id, content, image_path) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $content, $post_image_path);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Erro ao criar o post: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['comentario'])) {
        $comentario = htmlspecialchars($_POST['comentario']);
        $post_id = intval($_POST['post_id']);

        $sql = "INSERT INTO comentarios (user_id, post_id, comentario) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $user_id, $post_id, $comentario);

        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Erro ao criar o comentário: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['delete_post_id'])) {
        // Remover o post
        $delete_post_id = intval($_POST['delete_post_id']);

        // Exclui o post do banco de dados
        $sql = "DELETE FROM posts WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $delete_post_id, $user_id);

        if ($stmt->execute()) {
            // Redireciona para evitar reenvio de formulário
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Erro ao remover o post: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Consulta para buscar posts e usuários
$posts = $conn->query("SELECT posts.*, users.username, users.profile_image 
                       FROM posts 
                       JOIN users ON posts.user_id = users.id 
                       ORDER BY posts.created_at DESC")->fetch_all(MYSQLI_ASSOC);

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="base.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <title>JustU - Posts e Comentários</title>
</head>
<body>
    
    <section class="left-sidebar">
        <div class="container" id="preferences-container">
            <div class="card">
                <img src="./assets/images-preferences/anime.jpg" alt="">
                <h3>ANIME</h3>
            </div>
        </div>
    </section>

    <div class="circle-container">
    <div class="circle" data-modal="modal1"></div>
    <div class="circle" data-modal="modal2"></div>
    <div class="circle" data-modal="modal3"></div>
    <div class="circle">
        <i class="bx bx-plus"></i> <!-- Ícone de "mais" para o último círculo -->
    </div>
</div>

<!-- Modais ocultos -->
<div class="modal" id="modal1">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Conteúdo da Modal 1</p>
    </div>
</div>
<div class="modal" id="modal2">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Conteúdo da Modal 2</p>
    </div>
</div>
<div class="modal" id="modal3">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Conteúdo da Modal 3</p>
    </div>
</div>
<div class="modal" id="modal4">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Conteúdo da Modal 4</p>
    </div>
</div>

    <div class="home-content">
        <!-- Formulário para criação de post -->
        <div class="post-form">
            <form id="postForm" method="POST" enctype="multipart/form-data">
                <textarea id="content" name="content" placeholder="O que você está pensando?" required></textarea>
                <input type="file" id="image" name="image" accept="image/*">
                <button type="submit">Postar</button>
            </form>
            <div id="message"></div>
        </div>

        <div class="feed">
    <?php foreach ($posts as $post): ?>
        <div class="post">
            <h3><?php echo htmlspecialchars($post['username']); ?></h3>
            <p><?php echo htmlspecialchars($post['content']); ?></p>
            <?php if ($post['image_path']): ?>
                <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image">
            <?php endif; ?>

            <!-- Mostrar o botão de remover apenas se o post pertencer ao usuário logado -->
            <?php if ($post['user_id'] == $user_id): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" class="btn-delete" onclick="return confirm('Tem certeza que deseja remover este post?')">Remover Post</button>
                </form>
            <?php endif; ?>

            <!-- Formulário para comentário -->
            <form method="POST">
                <textarea name="comentario" placeholder="Comente aqui..." required></textarea>
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <button type="submit">Comentar</button>
            </form>

            <?php
            $comentarios = $conn->query("SELECT comentarios.*, users.username 
                                         FROM comentarios 
                                         JOIN users ON comentarios.user_id = users.id 
                                         WHERE post_id = " . $post['id'] . " 
                                         ORDER BY comentarios.created_at DESC")->fetch_all(MYSQLI_ASSOC);
            ?>
            <div class="comentarios">
                <?php foreach ($comentarios as $comentario): ?>
                    <div class="comentario">
                        <strong><?php echo htmlspecialchars($comentario['username']); ?>:</strong>
                        <p><?php echo htmlspecialchars($comentario['comentario']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
    </div>

    <section class="sidebar">
        <div class="nav-header">
            <p class="logo">JustU</p>
            <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" id="picprof">
            <i class="bx bx-menu btn-menu"></i>
        </div>
        <ul class="nav-links">
            <li>
                <i class="bx bx-search search-btn"></i>
                <input type="text" placeholder="Search..." />
                <span class="tooltip">Search</span>
            </li>
            <li>
                <a href="index.php">
                    <i class="bx bx-home-alt-2"></i>
                    <span class="title">Home Page</span>
                </a>
                <span class="tooltip">Home Page</span>
            </li>
            <li>
                <a href="#">
                    <i class="bx bx-plus-circle"></i>
                    <span class="title">Create</span>
                </a>
                <span class="tooltip">Create</span>
            </li>
            <li>
                <a href="#">
                    <i class="bx bx-bookmark"></i>
                    <span class="title">Bookmarks</span>
                </a>
                <span class="tooltip">Bookmarks</span>
            </li>
            <li>
                <a href="./public/profile.php">
                    <i class="bx bx-user-circle"></i>
                    <span class="title">Profile</span>
                </a>
                <span class="tooltip">Profile</span>
            </li>
            <li>
                <a href="./public/settings.php">
                    <i class="bx bx-cog"></i>
                    <span class="title">Settings</span>
                </a>
                <span class="tooltip">Settings</span>
            </li>
            <li id="logout">
                <a href="./public/logout.php">
                    <i class="bx bx-log-out"></i>
                    <span class="title">DESLOGAR</span>
                </a>
                <span class="tooltip">DESLOGAR</span>
            </li>
        </ul>
    </section>

    <script src="./assets/js/index.js"></script>
    <script src="./assets/js/settings.js"></script>
</body>
</html>
