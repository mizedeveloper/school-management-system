<?php
session_start();
include '../db.php';
include '../componentes/avatar_upload.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'aluno') {
    header('Location: ../login.php');
    exit();
}

$usuario_id = $_SESSION['id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Turma inválida.");
}

$turma_id = intval($_GET['id']);

// Verifica se o aluno pertence a essa turma
$stmt = $conn->prepare("
    SELECT t.nome, t.codigo
    FROM turmas t
    JOIN usuario_turma ut ON t.id = ut.turma_id
    WHERE t.id = ? AND ut.usuario_id = ? AND ut.tipo = 'aluno'
");
$stmt->bind_param('ii', $turma_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$turma = $result->fetch_assoc();

if (!$turma) {
    die("Você não tem acesso a essa turma.");
}

// Puxa atividades da turma
$stmt = $conn->prepare("
    SELECT a.*, u.nome AS professor_nome
    FROM atividades a
    JOIN usuarios u ON a.professor_id = u.id
    WHERE a.turma_id = ?
    ORDER BY a.id DESC
");
$stmt->bind_param("i", $turma_id);
$stmt->execute();
$result = $stmt->get_result();
$atividades = $result->fetch_all(MYSQLI_ASSOC);

// Puxa usuários da turma (professores e alunos)
$stmt = $conn->prepare("
    SELECT u.id, u.nome, ut.tipo
    FROM usuarios u
    JOIN usuario_turma ut ON u.id = ut.usuario_id
    WHERE ut.turma_id = ?
    ORDER BY ut.tipo DESC, u.nome
");
$stmt->bind_param("i", $turma_id);
$stmt->execute();
$result = $stmt->get_result();
$usuarios = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Turma: <?= htmlspecialchars($turma['nome']) ?> - S.E.A 🌊</title>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

<style>
  /* Reset básico */
  * {
    box-sizing: border-box;
  }

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

  header h1 {
    color: #00cfff;
    text-shadow: 0 0 6px #00cfffaa;
    margin-bottom: 5px;
  }
  header p {
    font-style: italic;
    color: #a9cfff;
    margin-top: 0;
  }

  /* Seção atividades */
  .atividades-section {
    margin-top: 30px;
  }

  .atividades-section h2 {
    color: #99d9ff;
    font-weight: 700;
    margin-bottom: 18px;
    text-shadow: 0 0 5px #00cfffaa;
  }

  .sem-atividades {
    font-style: italic;
    color: #a0a7c4;
    margin-top: 20px;
  }

  /* Lista de atividades */
  .atividades-lista {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .atividade-card {
    background: rgba(255 255 255 / 0.9);
    border-radius: 12px;
    padding: 18px 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.3s ease;
  }
  .atividade-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
  }

  .atividade-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
  }

  .atividade-header > div {
    flex: 1 1 auto;
  }

  .atividade-link {
    color: #002d4c;
    font-weight: 700;
    font-size: 1.15rem;
    text-decoration: none;
    transition: color 0.3s ease;
  }
  .atividade-link:hover {
    color: #00aaff;
  }

  .data-limite {
    display: block;
    margin-top: 6px;
    color: #005577;
    font-size: 0.9rem;
  }

  .professor-nome {
    color: #004466;
    font-weight: 600;
    font-size: 0.95rem;
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
        <img src="../img/logo.png" alt="Logo" class="logo" />
    </div>
    <a href="aluno.php">🏠 Início</a>
    <a href="ver_ocorrencias.php?turma_id=<?= $turma_id ?>">💀 Ocorrências</a>
    <a href="ver_usuarios_aluno.php?turma_id=<?= $turma_id ?>">👥 Usuários</a>
    <a href="../logout.php">👋 Sair</a>
    <?php include '../componentes/avatar_sidebar.php'; ?>
</div>

<div class="main-content">
    <header>
        <h1>Bem-vindo a <?= htmlspecialchars($turma['nome']) ?>! 🧐</h1>
        <p><i>Aprender é crescer.</i></p>
    </header>

    <section class="atividades-section">
        <h2>Atividades Disponíveis</h2>

        <?php if (count($atividades) === 0): ?>
            <p class="sem-atividades">💤 Nenhuma atividade disponível ainda. 💤</p>
        <?php else: ?>
            <div class="atividades-lista">
                <?php foreach ($atividades as $atividade): ?>
                    <article class="atividade-card">
                        <header class="atividade-header">
                            <div class="titulo-data">
                                <h3>
                                    <a href="atividade_aluno.php?id=<?= $atividade['id'] ?>" class="atividade-link">
                                        <?= htmlspecialchars($atividade['titulo']) ?>
                                    </a>
                                </h3>
                                <?php if (!empty($atividade['data_limite'])): ?>
                                    <time datetime="<?= htmlspecialchars($atividade['data_limite']) ?>" class="data-limite">
                                        Entrega até: <?= date('d/m/Y', strtotime($atividade['data_limite'])) ?>
                                    </time>
                                <?php endif; ?>
                            </div>
                            <p class="professor-nome">Enviada por: <?= htmlspecialchars($atividade['professor_nome']) ?></p>
                        </header>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

</body>
</html>
