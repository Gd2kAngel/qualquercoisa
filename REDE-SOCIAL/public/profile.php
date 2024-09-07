<?php
session_start();

// Conexão com o banco de dados
$conn = new mysqli('localhost', 'root', '', 'rede_social');

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Função para buscar os dados do usuário
function buscarDadosUsuario($conn, $id) {
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_assoc();
}

// Simulação do ID do usuário logado (substitua com o ID real ou um método de autenticação)
$usuario_id = 1; // Exemplo: ID do usuário logado

$user = buscarDadosUsuario($conn, $usuario_id);

if (!$user) {
    die("Usuário não encontrado.");
}

// Verificar se foi clicado no botão de seguir
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['seguir'])) {
    $seguido_id = $_POST['seguir_id'];

    // Verificar se já segue
    $sql = "SELECT * FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $usuario_id, $seguido_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 0) {
        // Inserir novo seguidor
        $sql = "INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $usuario_id, $seguido_id);
        $stmt->execute();
    }
}

// Upload de nova foto de perfil ou capturar imagem via câmera
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_FILES['nova_foto']) || isset($_POST['imagem_capturada']))) {
    $upload_dir = 'uploads/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (isset($_FILES['nova_foto'])) {
        $arquivo = $_FILES['nova_foto'];
        $arquivo_nome = basename($arquivo['name']);
        $caminho_arquivo = $upload_dir . $arquivo_nome;

        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($arquivo['type'], $tipos_permitidos)) {
            if (move_uploaded_file($arquivo['tmp_name'], $caminho_arquivo)) {
                // Atualizar o caminho da foto no banco de dados
                $sql = "UPDATE usuarios SET foto_perfil = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $caminho_arquivo, $usuario_id);
                $stmt->execute();
                $_SESSION['foto_perfil'] = $caminho_arquivo;
            } else {
                echo "Erro ao enviar a imagem.";
            }
        } else {
            echo "Apenas arquivos JPEG, PNG ou GIF são permitidos.";
        }
    } elseif (isset($_POST['imagem_capturada'])) {
        $imagem_data = $_POST['imagem_capturada'];
        $imagem_data = str_replace('data:image/png;base64,', '', $imagem_data);
        $imagem_data = base64_decode($imagem_data);
        $arquivo_nome = 'captura_' . time() . '.png';
        $caminho_arquivo = $upload_dir . $arquivo_nome;
        file_put_contents($caminho_arquivo, $imagem_data);

        // Atualizar o caminho da foto no banco de dados
        $sql = "UPDATE usuarios SET foto_perfil = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $caminho_arquivo, $usuario_id);
        $stmt->execute();
        $_SESSION['foto_perfil'] = $caminho_arquivo;
    }
}

// Definir a foto de perfil padrão, se não houver uma salva
$foto_perfil = isset($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : $user['foto_perfil'];

// Função para buscar as postagens do usuário
function buscarPostagens($conn, $usuario_id) {
    $sql = "SELECT * FROM postagens WHERE usuario_id = ? ORDER BY data DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

$postagens = buscarPostagens($conn, $usuario_id);

// Função para buscar estatísticas do usuário
function buscarEstatisticas($conn, $usuario_id) {
    $seguidores_query = "SELECT COUNT(*) AS total FROM seguidores WHERE seguido_id = ?";
    $seguindo_query = "SELECT COUNT(*) AS total FROM seguidores WHERE seguidor_id = ?";
    
    $stmt = $conn->prepare($seguidores_query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $seguidores_result = $stmt->get_result()->fetch_assoc();
    $seguidores = $seguidores_result['total'];
    
    $stmt = $conn->prepare($seguindo_query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $seguindo_result = $stmt->get_result()->fetch_assoc();
    $seguindo = $seguindo_result['total'];
    
    return ['seguidores' => $seguidores, 'seguindo' => $seguindo];
}

$estatisticas = buscarEstatisticas($conn, $usuario_id);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo $user['nome']; ?></title>
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>

<body>
    <header>
        <div class="perfil-header" style="background-image: url('<?php echo $foto_perfil; ?>');">
            <div class="perfil-info">
                <img class="foto-perfil" id="foto-perfil" src="<?php echo $foto_perfil; ?>" alt="Foto de perfil de <?php echo $user['nome']; ?>" onclick="abrirModal();">
                <h1>Gabriel</h1>
            </div>
        </div>
    </header>

    <section class="informacoes-pessoais">
        <h2>Informações Pessoais</h2>
        <p><strong>Data de Nascimento:</strong> 25/05/2004</p>
        <p><strong>Profissão:</strong> Desenvolvedor</p>
        <p><strong>Interesses:</strong> Programação em Web</p>
        <p><strong>Email:</strong> bielgsa2004@gmail.com</p>
        <p><strong>Telefone:</strong> (17)99979-0361</p>
        <p><strong>Localização:</strong> Guaíra SP</p>
        <p><strong>Estado Civil:</strong> Solteiro</p>
    </section>

    <section class="perfil-estatisticas">
        <div class="estatistica">
            <strong><?php echo $estatisticas['seguidores']; ?></strong>
            <p>Seguidores</p>
        </div>
        <div class="estatistica">
            <strong><?php echo $estatisticas['seguindo']; ?></strong>
            <p>Seguindo</p>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="seguir_id" value="<?php echo $user['id']; ?>">
            <button type="submit" name="seguir">Seguir</button>
        </form>
    </section>

    <section class="postagens">
        <h2>Postagens Recentes</h2>
        <?php foreach ($postagens as $postagem): ?>
            <div class="postagem">
                <h3><?php echo $postagem['titulo']; ?></h3>
                <p><small><?php echo $postagem['data']; ?></small></p>
                <p><?php echo $postagem['conteudo']; ?></p>
            </div>
        <?php endforeach; ?>
    </section>

    <footer>
        <p>&copy; 2024 Rede Social - Todos os direitos reservados.</p>
    </footer>

    <!-- Modal para visualização da imagem e alteração de foto -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="fechar" onclick="fecharModal()">&times;</span>
            <img id="imagemModal" src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" style="width: 100%;">
            <form action="" method="post" enctype="multipart/form-data">
                <input type="file" name="nova_foto" accept="image/*">
                <button type="submit">Enviar nova foto</button>
            </form>
            <button onclick="abrirCamera()">Usar câmera</button>
            <form id="form-camera" style="display: none;" method="post">
                <input type="hidden" name="imagem_capturada" id="imagem_capturada">
                <button type="submit">Salvar foto</button>
            </form>
            <video id="camera" style="display: none;"></video>
            <button id="capturar" style="display: none;">Capturar Foto</button>
            <canvas id="canvas" style="display:none;"></canvas>
        </div>
    </div>

            <script>
                function abrirModal() {
    document.getElementById('modal').style.display = 'block';
}

function fecharModal() {
    document.getElementById('modal').style.display = 'none';
}

function abrirCamera() {
var video = document.getElementById('camera');
var canvas = document.getElementById('canvas');
var context = canvas.getContext('2d');
var capturar = document.getElementById('capturar');


video.style.display = 'block';
capturar.style.display = 'block';

// Solicitar acesso à câmera
navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
.then(function(stream) {
    video.srcObject = stream;
    video.play();
})
.catch(function(error) {
    console.error("Erro ao acessar a câmera: ", error);
});


capturar.onclick = function() {

canvas.width = video.videoWidth;
canvas.height = video.videoHeight;

context.drawImage(video, 0, 0, canvas.width, canvas.height);

// Converter a imagem para uma URL de dados
var dataUrl = canvas.toDataURL('image/png');
document.getElementById('imagem_capturada').value = dataUrl;

let stream = video.srcObject;
if (stream) {
    let tracks = stream.getTracks();
    tracks.forEach(track => track.stop());
}

document.getElementById('form-camera').submit();
};
}
            </script>
</body>

</html>

<?php
$conn->close();
?>