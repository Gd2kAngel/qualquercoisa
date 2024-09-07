<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = 1; // Substitua pelo ID do usuário autenticado
    $content = $_POST['content'];
    $image_path = null;

    // Verifica se foi feito o upload de uma imagem
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";

        // Verifica se o diretório existe, se não, cria
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Cria o diretório com permissões de leitura, escrita e execução
        }

        $image_path = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));

        // Verifica se o arquivo é uma imagem
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            echo "O arquivo não é uma imagem.";
            exit;
        }

        // Verifica o tamanho do arquivo
        if ($_FILES["image"]["size"] > 5000000) { // Limite de 5MB
            echo "Desculpe, seu arquivo é muito grande.";
            exit;
        }

        // Permitir apenas certos formatos de imagem
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Desculpe, apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
            exit;
        }

        // Move o arquivo para o diretório de upload
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
            echo "Desculpe, ocorreu um erro ao fazer o upload do seu arquivo.";
            exit;
        }
    }

    $sql = "INSERT INTO posts (user_id, content, image_path) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $content, $image_path);

    if ($stmt->execute()) {
        echo "Post criado com sucesso!";
    } else {
        echo "Erro ao criar o post: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
