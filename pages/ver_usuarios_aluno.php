<?php
session_start();
include '../db.php';
include '../componentes/avatar_upload.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'aluno') {
    header('Location: ../login.php');
    exit();
}

$usuario_id = $_SESSION['id'];

if (!isset($_GET['turma_id']) || !is_numeric($_GET['turma_id'])) {
    die("Turma inválida.");
}

$turma_id = intval($_GET['turma_id']);

// Verifica se o aluno pertence a essa turma
$stmt = $conn->prepare("
    SELECT 1
    FROM usuario_turma
    WHERE turma_id = ? AND usuario_id = ? AND tipo = 'aluno'
");
$stmt->bind_param('ii', $turma_id, $usuario_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("Você não tem acesso a essa turma.");
}

// Busca usuários da turma (professores e alunos)
$stmt = $conn->prepare("
    SELECT u.id, u.nome, u.usuario, ut.tipo
    FROM usuarios u
    JOIN usuario_turma ut ON u.id = ut.usuario_id
    WHERE ut.turma_id = ?
    ORDER BY ut.tipo DESC, u.nome
");
$stmt->bind_param('i', $turma_id);
$stmt->execute();
$result = $stmt->get_result();
$usuarios = $result->fetch_all(MYSQLI_ASSOC);

// Separa os usuários por tipo
$professores = array_filter($usuarios, fn($u) => $u['tipo'] === 'professor');
$alunos = array_filter($usuarios, fn($u) => $u['tipo'] === 'aluno');

// Nome da turma
$stmt = $conn->prepare("SELECT nome FROM turmas WHERE id = ?");
$stmt->bind_param('i', $turma_id);
$stmt->execute();
$res = $stmt->get_result();
$turma = $res->fetch_assoc();
$nome_turma = $turma ? $turma['nome'] : 'Turma';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Usuários da Turma <?= htmlspecialchars($nome_turma) ?> - S.E.A 🌊</title>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins&display=swap');

  body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background-image: url('../wallpaper/foto.png');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: #e0e7ff;
    display: flex;
    min-height: 100vh;
  }

  /* Sidebar */
  .sidebar {
    background: rgba(0, 0, 50, 0.85);
    width: 240px;
    padding: 20px 15px;
    display: flex;
    flex-direction: column;
    box-shadow: 3px 0 12px rgba(0,0,0,0.5);
  }
  .sidebar .logo img {
    width: 150px;
    margin-bottom: 30px;
    filter: drop-shadow(1px 1px 2px #0009);
  }
  .sidebar a {
    text-decoration: none;
    color: #a9b9ff;
    font-weight: 600;
    margin-bottom: 18px;
    font-size: 1.05rem;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 8px;
    transition: background-color 0.3s ease;
  }
  .sidebar a:hover {
    background-color: #3765cc;
    color: #fff;
  }
  /* Avatar na sidebar */
  .sidebar-avatar {
    margin-top: auto;
    display: flex;
    justify-content: center;
  }
  .sidebar-avatar img {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 2px solid #00cfff;
    box-shadow: 0 0 8px #00cfffaa;
    object-fit: cover;
  }

  /* Conteúdo principal */
  .main-content {
    flex: 1;
    padding: 30px 40px;
    background: rgba(0,0,50,0.6);
    overflow-y: auto;
    position: relative;
  }

  header.aluno-header h1 {
    color: #00cfff;
    text-shadow: 0 0 6px #00cfffaa;
    margin-bottom: 10px;
  }

  /* Tabela */
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    color: #001f4d;
    background: #cde6ff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 15px rgba(0, 204, 255, 0.3);
  }
  thead tr {
    background-color: #007acc;
    color: white;
    font-weight: 700;
  }
  thead th, tbody td {
    padding: 12px 15px;
    text-align: left;
  }
  tbody tr:nth-child(even) {
    background-color: #e4f0ff;
  }
  tbody tr:hover {
    background-color: #b1d4ff;
  }

  /* Scrollbar para main-content */
  .main-content::-webkit-scrollbar {
    width: 10px;
  }
  .main-content::-webkit-scrollbar-thumb {
    background: #00cfffaa;
    border-radius: 10px;
  }
  .main-content::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.1);
  }
</style>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="../img/logo.png" alt="Logo S.E.A" />
    </div>
    <a href="aluno.php">🏠 Início</a>
    <a href="turma_aluno.php?id=<?= $turma_id ?>">👈 Voltar à turma</a>
    <a href="../logout.php">👋 Sair</a>
    <?php include '../componentes/avatar_sidebar.php'; ?>
</div>

<div class="main-content">
    <header class="aluno-header">
        <h1>Usuários da turma <?= htmlspecialchars($nome_turma) ?></h1>
    </header>

    <section class="user-list">
        <?php if (count($usuarios) === 0): ?>
            <p>Não há usuários cadastrados nesta turma.</p>
        <?php else: ?>
            <?php if (count($professores) > 0): ?>
                <h2>Professores</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Usuário ‎  ‎ ‎‎</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($professores as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['nome']) ?></td>
                                <td><?= htmlspecialchars($p['usuario']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (count($alunos) > 0): ?>
                <h2>Alunos</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Usuário</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alunos as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars($a['nome']) ?></td>
                                <td><?= htmlspecialchars($a['usuario']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>

</body>
</html>
