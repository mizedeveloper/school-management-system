<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

$chave_secreta = 'minha_chave_secreta_super_segura';

function buscarUsuariosPorTipo($conn, $tipo, $chave) {
    $stmt = $conn->prepare("
        SELECT 
            id, nome, usuario, 
            CAST(AES_DECRYPT(FROM_BASE64(senha_visivel), ?) AS CHAR) AS senha_descriptografada,
            tipo 
        FROM usuarios 
        WHERE tipo = ? 
        ORDER BY nome ASC
    ");
    $stmt->bind_param("ss", $chave, $tipo);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    return $usuarios;
}

$professores = buscarUsuariosPorTipo($conn, 'professor', $chave_secreta);
$alunos = buscarUsuariosPorTipo($conn, 'aluno', $chave_secreta);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Lista de Usuários</title>
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
    <a href="usuarios.php" class="active"><i class="fas fa-users"></i> Usuários</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
</div>

<div class="main-content">
    <header class="professor-header">
        <h1>Usuários do Sistema</h1>
        <p>Senhas são exibidas descriptografadas para referência do administrador.</p>
    </header>

    <div class="user-list">
        <h2>Professores</h2>
        <?php if (count($professores) === 0): ?>
            <p>Nenhum professor cadastrado.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Usuário</th>
                        <th>Senha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($professores as $prof): ?>
                        <tr>
                            <td data-label="Nome"><?= htmlspecialchars($prof['nome']) ?></td>
                            <td data-label="Usuário"><?= htmlspecialchars($prof['usuario']) ?></td>
                            <td data-label="Senha"><?= htmlspecialchars($prof['senha_descriptografada']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="user-list">
        <h2>Alunos</h2>
        <?php if (count($alunos) === 0): ?>
            <p>Nenhum aluno cadastrado.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Usuário</th>
                        <th>Senha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno): ?>
                        <tr>
                            <td data-label="Nome"><?= htmlspecialchars($aluno['nome']) ?></td>
                            <td data-label="Usuário"><?= htmlspecialchars($aluno['usuario']) ?></td>
                            <td data-label="Senha"><?= htmlspecialchars($aluno['senha_descriptografada']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
