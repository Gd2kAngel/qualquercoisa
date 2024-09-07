<?php
session_start();

require_once(__DIR__ . '/../assets/api/Model/User_settings.php');
require_once(__DIR__ . '/../assets/api/Context.php');

if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit();
}

$context = new Context();
$conn = $context->getConnection();
$userSettings = new UserSettings($conn);

$user_id = $_SESSION['user'];
$default_image = '/assets/images/default_image.jpg';
$theme = 'light'; // Default theme

// Buscar as configurações atuais do usuário
$currentSettings = $userSettings->getUserSettings($user_id);
if ($currentSettings && isset($currentSettings['theme'])) {
    $theme = $currentSettings['theme'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $theme = isset($_POST['theme']) ? $_POST['theme'] : 'light';

    // Verificar se o usuário enviou uma nova imagem de perfil
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = basename($_FILES['profile_image']['name']);
        $uploadFileDir = __DIR__ . '/../assets/uploads/';
        $dest_path = $uploadFileDir . $fileName;

        // Verificar se a pasta de uploads existe, se não, criar
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        // Tentar mover o arquivo para o diretório de uploads
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $profile_image = '../assets/uploads/' . $fileName;
        } else {
            echo "Falha ao mover o arquivo para o destino.";
            $profile_image = $default_image;
        }
    } else {
        // Se não foi enviado um novo arquivo, mantemos a imagem existente ou usamos a padrão
        $profile_image = isset($currentSettings['profile_image']) ? $currentSettings['profile_image'] : $default_image;
    }

    if (is_numeric($user_id) && $user_id > 0) {
        $user_id = (int)$user_id;
        $success = $userSettings->updateSettings($user_id, $theme, $profile_image);

        if ($success) {
            echo "Configurações atualizadas com sucesso!";
        } else {
            echo "Falha ao atualizar configurações.";
        }
    } else {
        echo "Erro: ID do usuário inválido.";
    }
}
?>



<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/settings.css">
</head>

<body class="<?php echo htmlspecialchars($theme); ?>-mode">

    <!--MIDDLE PAGE-->

    <section class="home">
        <h1>Profile Settings</h1>
        <form action="settings.php" method="post" enctype="multipart/form-data">
            <label for="theme">Theme:</label>
            <select id="theme" name="theme">
                <option value="light" <?php if ($theme == 'light') echo 'selected'; ?>>Light</option>
                <option value="dark" <?php if ($theme == 'dark') echo 'selected'; ?>>Dark</option>
            </select>

            <label for="profile_image">Profile Image:</label>
            <input type="file" id="profile_image" name="profile_image">

            <button type="submit">Save Settings</button>
        </form>
    </section>

    <!--RIGHT SIDEBAR-->

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
                <a href="../index.php">
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
                <a href="./profile.php">
                    <i class="bx bx-user-circle"></i>
                    <span class="title">Profile</span>
                </a>
                <span class="tooltip">Profile</span>
            </li>
            <li>
                <a href="./settings.php">
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

    <!-- <script src="../assets/js/settings.js"></script> -->
    <script src="../assets/js/index.js"></script>
</body>

</html>