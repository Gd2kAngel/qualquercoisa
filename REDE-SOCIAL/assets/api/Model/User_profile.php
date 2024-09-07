<?php

class User_profile
{
    private $conn;

    // Construtor para inicializar a conexão com o banco de dados
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Método para buscar informações do perfil do usuário
    public function getProfileData($user_id)
    {
        $sql = "SELECT theme, profile_image, user_id FROM user_settings WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Retorna os dados do perfil
        }
        return null; // Se não houver dados, retorna nulo
    }

    // Método para atualizar as informações do perfil do usuário
    public function updateProfileData($user_id, $theme, $profile_image)
    {
        $sql = "UPDATE user_settings SET theme = ?, profile_image = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $theme, $profile_image, $user_id);
        return $stmt->execute(); // Retorna verdadeiro se a atualização for bem-sucedida
    }

    // (Opcional) Método para buscar os posts do usuário
    public function getUserPosts($user_id)
    {
        $sql = "SELECT content, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }
}
