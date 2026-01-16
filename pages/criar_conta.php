<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

$erro = '';
$sucesso = '';
$usuario_gerado = '';
$senha_gerada = '';

$chave_secreta = 'minha_chave_secreta_super_segura'; // ALTERE ESSA CHAVE PARA SUA

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $tipo = $_POST['tipo'] ?? '';

    if (!$nome || !in_array($tipo, ['aluno', 'professor'])) {
        $erro = "Preencha corretamente o nome e selecione o tipo de conta.";
    } else {
        // Encontrar maior índice para tipo para gerar usuário correto
        $prefixo = ($tipo === 'aluno') ? 'a' : 'p';

        $stmt = $conn->prepare("SELECT usuario FROM usuarios WHERE tipo = ? ORDER BY LENGTH(usuario) DESC, usuario DESC LIMIT 1");
        $stmt->bind_param("s", $tipo);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            // Extrai número do usuário, ex: a12 => 12
            preg_match('/\d+$/', $result['usuario'], $matches);
            $ultimo_num = $matches ? intval($matches[0]) : 0;
            $proximo_num = $ultimo_num + 1;
        } else {
            $proximo_num = 1;
        }

        $usuario_gerado = $prefixo . $proximo_num;

        // Gerar senha aleatória 8 chars hex
        $senha_gerada = bin2hex(random_bytes(4));

        // Hash para login
        $senha_hash = password_hash($senha_gerada, PASSWORD_DEFAULT);

        // Inserir com AES_ENCRYPT e TO_BASE64 para senha_visivel
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, usuario, senha, senha_visivel, tipo) VALUES (?, ?, ?, TO_BASE64(AES_ENCRYPT(?, ?)), ?)");
$stmt->bind_param("ssssss", $nome, $usuario_gerado, $senha_hash, $senha_gerada, $chave_secreta, $tipo);


        if ($stmt->execute()) {
            $sucesso = "Conta criada com sucesso!";
        } else {
            $erro = "Erro ao criar usuário: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Criar Conta</title>
    <link rel="stylesheet" href="../css/style.css" />
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="../img/logo.png" alt="Logo" class="logo" />
    </div>
    <a href="admin.php"><i class="fas fa-tachometer-alt"></i> Início</a>
    <a href="criar_conta.php"><i class="fas fa-user-plus"></i> Criar Conta</a>
    <a href="usuarios.php"><i class="fas fa-users"></i> Usuários</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
</div>

<div class="main-content">
    <header class="professor-header">
        <h1>Criar Nova Conta</h1>
        <p>Usuário e senha são gerados automaticamente</p>
    </header>

    <div class="form-container">
        <?php if ($erro): ?>
            <div class="error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="success"><?= htmlspecialchars($sucesso) ?></div>
            <p><strong>Usuário:</strong> <?= htmlspecialchars($usuario_gerado) ?></p>
            <p><strong>Senha:</strong> <?= htmlspecialchars($senha_gerada) ?></p>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <label for="nome">Nome Completo</label>
            <input type="text" id="nome" name="nome" required />

            <label for="tipo">Tipo de Conta</label>
            <select id="tipo" name="tipo" required>
                <option value="" disabled selected>Selecione</option>
                <option value="aluno">Aluno</option>
                <option value="professor">Professor</option>
            </select>

            <button type="submit">Criar Conta</button>
        </form>
    </div>
</div>

</body>
</html>
