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
    die("Atividade inválida.");
}

$atividade_id = intval($_GET['id']);

// Verifica se o aluno pertence à turma dessa atividade
$stmt = $conn->prepare("
    SELECT a.*, t.nome AS turma_nome, t.codigo AS turma_codigo
    FROM atividades a
    JOIN turmas t ON a.turma_id = t.id
    JOIN usuario_turma ut ON t.id = ut.turma_id
    WHERE a.id = ? AND ut.usuario_id = ? AND ut.tipo = 'aluno'
");
$stmt->bind_param('ii', $atividade_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$atividade = $result->fetch_assoc();

if (!$atividade) {
    die("Você não tem acesso a essa atividade.");
}

// Pasta para salvar uploads do aluno: ../uploads/turma_<id>/atividade_<id>/usuario_<id>/
$upload_dir = __DIR__ . "/../uploads/turma_" . $atividade['turma_id'] . "/atividade_" . $atividade_id . "/usuario_" . $usuario_id . "/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$arquivo_enviado = null;

// Verifica se já existe arquivo enviado pelo aluno (pega o nome do arquivo salvo)
$arquivos = glob($upload_dir . "*");
if (count($arquivos) > 0) {
    $arquivo_enviado = basename($arquivos[0]);
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo_tarefa'])) {
    if ($_FILES['arquivo_tarefa']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['arquivo_tarefa']['tmp_name'];
        $name = basename($_FILES['arquivo_tarefa']['name']);
        $target_file = $upload_dir . $name;

        // Remove arquivos antigos da pasta (substituição)
        foreach ($arquivos as $old_file) {
            unlink($old_file);
        }

        if (move_uploaded_file($tmp_name, $target_file)) {
            $mensagem = "Arquivo enviado com sucesso!";
            $arquivo_enviado = $name;
        } else {
            $mensagem = "Erro ao salvar o arquivo.";
        }
    } else {
        $mensagem = "Erro no upload do arquivo.";
    }
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

  /* Seção descrição */
  .descricao-atividade {
    margin-top: 25px;
    background: #cde6ff;
    color: #001f4d;
    padding: 20px 25px;
    border-radius: 10px;
    box-shadow: 0 0 20px #00cfff55;
  }
  .descricao-atividade h2 {
    margin-top: 0;
    color: #0059b3;
  }
  .descricao-atividade p {
    white-space: pre-wrap;
    font-size: 1.1rem;
    line-height: 1.5;
  }
  .descricao-atividade a {
    color: #003366;
    font-weight: 600;
    text-decoration: none;
  }
  .descricao-atividade a:hover {
    text-decoration: underline;
  }

  /* Seção upload */
  .upload-tarefa {
    margin-top: 35px;
    background: #cde6ff;
    color: #001f4d;
    padding: 20px 25px;
    border-radius: 10px;
    box-shadow: 0 0 20px #00cfff55;
  }
  .upload-tarefa h2 {
    margin-top: 0;
    color: #0059b3;
  }
  .upload-tarefa form {
    margin-top: 15px;
    display: flex;
    gap: 15px;
    align-items: center;
  }
  .upload-tarefa input[type="file"] {
    flex: 1;
    padding: 6px 8px;
    border-radius: 6px;
    border: 1px solid #007acc;
    font-size: 1rem;
    cursor: pointer;
    background: #e8f0fe;
  }
  .upload-tarefa button {
    padding: 10px 20px;
    background-color: #0059b3;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  .upload-tarefa button:hover {
    background-color: #003d7a;
  }
  .upload-tarefa p {
    margin-top: 15px;
    font-weight: 600;
  }
  .upload-tarefa p a {
    color: #003366;
    font-weight: 700;
    text-decoration: none;
  }
  .upload-tarefa p a:hover {
    text-decoration: underline;
  }

  /* Mensagem */
  .mensagem-sucesso {
    color: #28a745;
    font-weight: 700;
  }
  .mensagem-erro {
    color: #dc3545;
    font-weight: 700;
  }
</style>

</head>
<body>

<div class="sidebar">
  <div class="logo">
    <img src="../img/logo.png" alt="Logo S.E.A" />
  </div>
  <a href="aluno.php">🏠 Início</a>
  <a href="turma_aluno.php?id=<?= $atividade['turma_id'] ?>">👈 Voltar à turma</a>
  <a href="../logout.php">👋 Sair</a>
  <?php include '../componentes/avatar_sidebar.php'; ?>
</div>

<div class="main-content">
  <header>
    <h1><?= htmlspecialchars($atividade['titulo']) ?></h1>
    <p><strong>Turma:</strong> <?= htmlspecialchars($atividade['turma_nome']) ?></p>
    <?php if (!empty($atividade['data_limite'])): ?>
      <p><strong>Entrega até:</strong> <?= date('d/m/Y', strtotime($atividade['data_limite'])) ?></p>
    <?php endif; ?>
  </header>

  <section class="descricao-atividade">
    <h2>Descrição</h2>
      
    <?php
    $nomeArquivo = ''; 
    if (!empty($atividade['arquivo'])):
      $nomeArquivo = basename($atividade['arquivo']);
    ?>
     
    <?php endif; ?>

    <p><?= nl2br(htmlspecialchars($atividade['descricao'] ?? 'Sem descrição.')) ?></p>
      <?php if (!empty($atividade['arquivo'])): ?>
     <p>
        Arquivo da atividade:
        <a href="<?= htmlspecialchars($atividade['arquivo']) ?>" download>
          📂 <?= htmlspecialchars($nomeArquivo) ?>
        </a>
      </p>
      <?php endif; ?>
  </section>

  <section class="upload-tarefa">
    <h2>Enviar tarefa</h2>

    <?php if ($mensagem): ?>
      <p class="<?= strpos($mensagem, 'sucesso') !== false ? 'mensagem-sucesso' : 'mensagem-erro' ?>">
        <?= htmlspecialchars($mensagem) ?>
      </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" autocomplete="off">
      <input type="file" name="arquivo_tarefa" required />
      <button type="submit">Enviar</button>
    </form>

    <?php if ($arquivo_enviado): ?>
      <p>Arquivo enviado: 
        <a href="<?= "../uploads/turma_" . $atividade['turma_id'] . "/atividade_" . $atividade_id . "/usuario_" . $usuario_id . "/" . rawurlencode($arquivo_enviado) ?>" download>
          <?= htmlspecialchars($arquivo_enviado) ?>
        </a>
      </p>
    <?php else: ?>
      <p>Nenhum arquivo enviado ainda.</p>
    <?php endif; ?>
  </section>
</div>

</body>
</html>
