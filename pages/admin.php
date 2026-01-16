<?php
session_start();
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

// Aqui você pode buscar a lista de usuários etc para mostrar

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Painel Admin - Sistema Escolar</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="../img/logo.png" alt="Logo" class="logo" />
    </div>
    <a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i> Início</a>
    <a href="criar_conta.php"><i class="fas fa-user-plus"></i> Criar Conta</a>
    <a href="usuarios.php"><i class="fas fa-users"></i> Usuários</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
</div>

<div class="main-content">
    <header class="professor-header">
        <h1>Painel do Administrador</h1>
        <p>Bem-vindo, <?= htmlspecialchars($_SESSION['nome']) ?></p>
    </header>

    <section>
        <div class="info-blocks">
            <div class="info-block admins">
                <i class="fas fa-user-shield"></i>
                <div>Total Admins: 
                <?php
                    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'admin'");
                    $row = $result->fetch_assoc();
                    echo $row['total'];
                ?>
                </div>
            </div>

            <div class="info-block students">
                <i class="fas fa-user-graduate"></i>
                <div>Total Alunos: 
                <?php
                    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'aluno'");
                    $row = $result->fetch_assoc();
                    echo $row['total'];
                ?>
                </div>
            </div>

            <div class="info-block">
                <i class="fas fa-chalkboard-teacher"></i>
                <div>Total Professores: 
                <?php
                    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'professor'");
                    $row = $result->fetch_assoc();
                    echo $row['total'];
                ?>
                </div>
            </div>
        </div>

        <!-- Aqui pode colocar mais conteúdo/administração -->

    </section>
</div>

<!-- FontAwesome CDN para os ícones -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>




</body>
</html>
