<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit();
}

require_once(__DIR__ . '/../assets/api/Model/User_settings.php');
require_once(__DIR__ . '/../assets/api/Context.php');

$context = new Context();
$conn = $context->getConnection();
$userSettings = new UserSettings($conn);

$user_id = $_SESSION['user'];
$currentSettings = $userSettings->getUserSettings($user_id);

// Definir tema padrão como 'light'
$theme = 'light';

// Verifica o tema atual do usuário
if ($currentSettings && isset($currentSettings['theme'])) {
    $theme = $currentSettings['theme'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Título da Página</title>
    <link rel="stylesheet" href="../css/seu-estilo.css">
</head>

<body class="<?php echo htmlspecialchars($theme); ?>-mode">