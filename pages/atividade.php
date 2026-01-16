<?php
session_start();
include '../db.php';
include '../componentes/avatar_upload.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'professor') {
    header('Location: ../login.php');
    exit();
}

$usuario_id = $_SESSION['id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Atividade inválida.");
}

$atividade_id = intval($_GET['id']);

// Puxa os dados da atividade e turma, garantindo permissão do professor
$stmt = $conn->prepare("
    SELECT a.*, t.id AS turma_id, t.nome AS turma_nome, u.nome AS professor_nome
    FROM atividades a
    JOIN turmas t ON a.turma_id = t.id
    JOIN usuario_turma ut ON t.id = ut.turma_id
    JOIN usuarios u ON a.professor_id = u.id
    WHERE a.id = ? AND ut.usuario_id = ? AND ut.tipo = 'professor'
");

$stmt->bind_param('ii', $atividade_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$atividade = $result->fetch_assoc();

if (!$atividade) {
    die("Atividade não encontrada ou acesso negado.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Atividade: <?= htmlspecialchars($atividade['titulo']) ?> - S.E.A 🌊</title>

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
    margin: 4px 0;
    font-weight: 500;
  }

  /* Botão editar */
  .btn {
    display: inline-block;
    margin-top: 12px;
    padding: 8px 14px;
    background-color: #2e86de;
    color: white;
    border-radius: 6px;
    font-weight: 700;
    text-decoration: none;
    transition: background-color 0.3s ease;
  }
  .btn:hover {
    background-color: #1b4f72;
  }

  /* Seção detalhes da atividade */
  .atividade-detalhes {
    margin-top: 25px;
    background: #cde6ff;
    color: #001f4d;
    padding: 20px 25px;
    border-radius: 10px;
    box-shadow: 0 0 20px #00cfff55;
  }
  .atividade-detalhes p {
    white-space: pre-wrap;
    font-size: 1.1rem;
    line-height: 1.5;
  }
  .btn-arquivo {
    display: inline-block;
    margin-top: 15px;
    padding: 10px 15px;
    background-color: #0059b3;
    color: white;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    transition: background-color 0.3s ease;
  }
  .btn-arquivo:hover {
    background-color: #003d7a;
  }
  .btn-arquivo i {
    margin-right: 8px;
  }

  .data-envio {
    margin-top: 15px;
    font-size: 0.9rem;
    color: #004080;
  }

  /* Seção entregas */
  .entregas-alunos {
    margin-top: 40px;
  }
  .entregas-alunos h2 {
    color: #00cfff;
    text-shadow: 0 0 5px #00cfffaa;
    margin-bottom: 15px;
  }
  .entregas-alunos > div {
    background: #d7e9ff;
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 1rem;
    color: #00224d;
    box-shadow: 0 0 12px #00cfff33;
  }
  .entregas-alunos a {
    display: inline-block;
    margin-top: 4px;
    color: #004080;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
  }
  .entregas-alunos a:hover {
    color: #00254d;
  }
</style>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="../img/logo.png" alt="Logo S.E.A" />
    </div>
    <a href="professor.php">🏠 Início</a>
    <a href="turma.php?id=<?= $atividade['turma_id'] ?>">👈 Voltar à Turma</a>
    <a href="../logout.php">👋 Sair</a>
    <?php include '../componentes/avatar_sidebar.php'; ?>
</div>

<div class="main-content">
<header>
    <h1><?= htmlspecialchars($atividade['titulo']) ?></h1>
    <p><strong>Turma:</strong> <?= htmlspecialchars($atividade['turma_nome']) ?></p>
    <p><strong>Enviada por:</strong> <?= htmlspecialchars($atividade['professor_nome']) ?></p>
    <?php if (!empty($atividade['data_limite'])): ?>
        <p><strong>Data limite de entrega:</strong> <?= date('d/m/Y', strtotime($atividade['data_limite'])) ?></p>
    <?php endif; ?>

    <?php if ($atividade['professor_id'] == $usuario_id): ?>
        <a href="editar_atividade.php?id=<?= $atividade_id ?>" class="btn">✏️ Editar Atividade</a>
    <?php endif; ?>
</header>

<section class="atividade-detalhes">
    <p><?= nl2br(htmlspecialchars($atividade['descricao'])) ?></p>

    <?php if (!empty($atividade['arquivo'])):
        $nomeArquivo = basename($atividade['arquivo']);
    ?>
        <a href="<?= htmlspecialchars($atividade['arquivo']) ?>" target="_blank" class="btn-arquivo">
            <i class="fas fa-file-download"></i> Arquivo: <?= htmlspecialchars($nomeArquivo) ?>
        </a>
    <?php endif; ?>

    <p class="data-envio"><small>Enviada em <?= date('d/m/Y H:i', strtotime($atividade['data_envio'])) ?></small></p>
</section>

<section class="entregas-alunos">
    <h2>Entregas dos alunos</h2>

    <?php
    $path_base = __DIR__ . "/../uploads/turma_" . $atividade['turma_id'] . "/atividade_" . $atividade_id . "/";
    $url_base = "../uploads/turma_" . $atividade['turma_id'] . "/atividade_" . $atividade_id . "/";

    $stmt = $conn->prepare("
        SELECT u.id, u.nome 
        FROM usuarios u
        JOIN usuario_turma ut ON u.id = ut.usuario_id
        WHERE ut.turma_id = ? AND ut.tipo = 'aluno'
    ");
    $stmt->bind_param('i', $atividade['turma_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $alunos = $result->fetch_all(MYSQLI_ASSOC);

    if (count($alunos) === 0) {
        echo "<p>Nenhum aluno nesta turma.</p>";
    } else {
        foreach ($alunos as $aluno) {
            $aluno_dir = $path_base . "usuario_" . $aluno['id'] . "/";
            $arquivos = is_dir($aluno_dir) ? glob($aluno_dir . "*") : [];

            echo "<div>";
            echo "<strong>" . htmlspecialchars($aluno['nome']) . ":</strong><br>";

            if (count($arquivos) > 0) {
                foreach ($arquivos as $arquivo) {
                    $arquivo_nome = basename($arquivo);
                    echo "<a href='" . $url_base . "usuario_" . $aluno['id'] . "/" . rawurlencode($arquivo_nome) . "' download>📎 $arquivo_nome</a><br>";
                }
            } else {
                echo "<em>Nenhum arquivo enviado</em>";
            }

            echo "</div>";
        }
    }
    ?>
</section>

</div>

</body>
</html>
