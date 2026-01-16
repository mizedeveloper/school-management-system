<?php
session_start();
include '../db.php';
include '../componentes/avatar_upload.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'aluno') {
    header('Location: ../login.php');
    exit();
}

$aluno_id = $_SESSION['id'];
$turma_id = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;

if (!$turma_id) {
    die("Turma inválida.");
}

// Verifica se o aluno realmente pertence à turma
$stmt = $conn->prepare("SELECT nome FROM turmas t JOIN usuario_turma ut ON t.id = ut.turma_id WHERE t.id = ? AND ut.usuario_id = ? AND ut.tipo = 'aluno'");
$stmt->bind_param("ii", $turma_id, $aluno_id);
$stmt->execute();
$result = $stmt->get_result();
$turma = $result->fetch_assoc();

if (!$turma) {
    die("Você não tem acesso a essa turma.");
}

$nome_turma = $turma['nome'];

// Pega ocorrências do aluno nesta turma
$stmt = $conn->prepare("
    SELECT o.descricao, o.data, u.nome AS professor_nome
    FROM ocorrencias o
    JOIN usuarios u ON o.professor_id = u.id
    JOIN usuario_turma ut ON ut.usuario_id = o.aluno_id AND ut.turma_id = ?
    WHERE o.aluno_id = ? AND ut.usuario_id = ?
    ORDER BY o.data DESC
");
$stmt->bind_param("iii", $turma_id, $aluno_id, $aluno_id);
$stmt->execute();
$result = $stmt->get_result();
$ocorrencias = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Ocorrências - <?= htmlspecialchars($nome_turma) ?> - S.E.A 🌊</title>

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

  header h1 {
    color: #00cfff;
    text-shadow: 0 0 6px #00cfffaa;
    margin-bottom: 5px;
  }

  header p {
    color: #a9b9ff;
    margin-top: 0;
    font-weight: 500;
  }

  .ocorrencias-section {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
  }

  .ocorrencia-card {
    background: #cde6ff;
    border-radius: 12px;
    box-shadow: 0 6px 14px rgba(0,0,0,0.1);
    padding: 1.5rem 2rem;
    border-left: 6px solid #d9534f;
    transition: box-shadow 0.3s ease;
    color: #001f4d;
  }

  .ocorrencia-card:hover {
    box-shadow: 0 10px 24px rgba(0,0,0,0.15);
  }

  .ocorrencia-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.7rem;
    font-weight: 700;
    font-size: 1.1rem;
  }

  .ocorrencia-header .professor {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #d9534f;
  }

  .ocorrencia-header .data {
    font-size: 0.9rem;
    color: #555;
    font-style: italic;
  }

  .ocorrencia-descricao {
    white-space: pre-wrap;
    line-height: 1.5;
    font-size: 1rem;
    color: #003366;
  }

  .ocorrencias-vazia {
    font-size: 1.3rem;
    color: #5cb85c;
    text-align: center;
    padding: 4rem 1rem;
    font-weight: 700;
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
  <header>
      <h1>Ocorrências</h1>
      <p>Turma: <?= htmlspecialchars($nome_turma) ?></p>
  </header>

  <section class="ocorrencias-section">
      <?php if (count($ocorrencias) === 0): ?>
          <p class="ocorrencias-vazia">🎉 Nenhuma ocorrência registrada contra você nesta turma.</p>
      <?php else: ?>
          <?php foreach ($ocorrencias as $oc): ?>
              <article class="ocorrencia-card">
                  <div class="ocorrencia-header">
                      <div class="professor">👨‍🏫 <?= htmlspecialchars($oc['professor_nome']) ?></div>
                      <div class="data">📅 <?= date('d/m/Y H:i', strtotime($oc['data'])) ?></div>
                  </div>
                  <div class="ocorrencia-descricao"><?= nl2br(htmlspecialchars($oc['descricao'])) ?></div>
              </article>
          <?php endforeach; ?>
      <?php endif; ?>
  </section>
</div>

</body>
</html>
