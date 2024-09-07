<?php
include 'db_connect.php';

// Consulta para obter os posts com o nome de usuário e a imagem de perfil
$sql = "SELECT posts.*, users.username, users.profile_image 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC";
$result = $conn->query($sql);

// Verifica se há resultados
if ($result->num_rows > 0) {
    // Itera sobre os resultados e exibe os posts
    while ($row = $result->fetch_assoc()) {
        echo "<div class='post'>";
        
        // Exibir nome do usuário e imagem de perfil
        echo "<h3>" . htmlspecialchars($row['username']) . "</h3>";
        if (!empty($row['profile_image'])) {
            echo "<img src='" . htmlspecialchars($row['profile_image']) . "' alt='Profile Image' style='width:50px; height:50px; border-radius:50%;'/>";
        }

        // Exibir o conteúdo do post
        echo "<p>" . htmlspecialchars($row['content']) . "</p>";

        // Exibir a imagem do post, se houver
        if (!empty($row['image_path'])) {
            echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='Post Image' style='max-width:100%; height:auto;'/>";
        }

        // Exibir a data de criação do post
        echo "<small>Postado em " . $row['created_at'] . "</small>";
        echo "</div>";
    }
} else {
    // Se não houver posts
    echo "<p>Sem posts no momento.</p>";
}

$conn->close();
?>
