<?php
class UserSettings
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function updateSettings($user_id, $theme, $profile_image)
    {
        // Verifica se `$profile_image` é um array (esperado de um upload)
        if (is_array($profile_image) && isset($profile_image['tmp_name']) && $profile_image['tmp_name']) {
            // Diretório onde a imagem será salva
            $target_dir = "uploads/";

            // Nome do arquivo a ser salvo
            $target_file = $target_dir . basename($profile_image["name"]);

            // Verifica se o upload é realmente uma imagem
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $check = getimagesize($profile_image["tmp_name"]);
            if ($check === false) {
                return false; // O arquivo não é uma imagem
            }

            // Verifica se o arquivo já existe
            if (file_exists($target_file)) {
                return false; // O arquivo já existe
            }

            // Verifica o tamanho do arquivo (exemplo: máximo de 5MB)
            if ($profile_image["size"] > 5000000) {
                return false; // O arquivo é muito grande
            }

            // Limita os formatos permitidos (JPG, JPEG, PNG, GIF)
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                return false; // Formato de arquivo não permitido
            }

            // Tenta mover o arquivo enviado para o diretório de uploads
            if (!move_uploaded_file($profile_image["tmp_name"], $target_file)) {
                return false; // Erro ao mover o arquivo
            }

            // Armazena o caminho do arquivo para ser salvo no banco de dados
            $profile_image = $target_file;
        } else {
            // Caso `$profile_image` não seja um array, mantemos a string já passada
            $profile_image = $profile_image;
        }

        // Verifica se já existem configurações para o usuário
        $sql = "SELECT * FROM user_settings WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Atualiza as configurações do usuário
            $sql = "UPDATE user_settings SET theme = ?, profile_image = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ssi', $theme, $profile_image, $user_id);
        } else {
            // Insere novas configurações para o usuário
            $sql = "INSERT INTO user_settings (user_id, theme, profile_image) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('iss', $user_id, $theme, $profile_image);
        }

        return $stmt->execute();
    }



    public function getUserSettings($user_id)
    {
        $sql = "SELECT theme, profile_image FROM user_settings WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }
}
