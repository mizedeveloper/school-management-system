<?php
session_start();
include '../db.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'professor') {
    header('Location: ../login.php');
    exit();
}

$usuario_id = $_SESSION['id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido.");
}

$atividade_id = intval($_GET['id']);

// Busca atividade (somente se o professor for o autor)
$stmt = $conn->prepare("SELECT * FROM atividades WHERE id = ? AND professor_id = ?");
$stmt->bind_param("ii", $atividade_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$atividade = $result->fetch_assoc();

if (!$atividade) {
    die("Atividade não encontrada ou acesso negado.");
}

// Processa edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $data_limite = !empty($_POST['data_limite']) ? $_POST['data_limite'] : null;

    $stmt = $conn->prepare("UPDATE atividades SET titulo = ?, descricao = ?, data_limite = ? WHERE id = ?");
    $stmt->bind_param("sssi", $titulo, $descricao, $data_limite, $atividade_id);
    $stmt->execute();

    header("Location: atividade.php?id=" . $atividade_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Editar Atividade - S.E.A 🌊</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
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

  /* Conteúdo principal */
  .main-content {
    flex: 1;
    padding: 40px;
    background: rgba(0,0,50,0.6);
    overflow-y: auto;
  }

  h1 {
    color: #00cfff;
    text-shadow: 0 0 6px #00cfffaa;
    margin-bottom: 25px;
  }

  form {
    background: #cde6ff;
    color: #001f4d;
    padding: 25px 30px;
    border-radius: 10px;
    box-shadow: 0 0 20px #00cfff55;
    max-width: 700px;
  }

  label {
    display: block;
    margin-top: 20px;
    font-weight: 600;
    margin-bottom: 6px;
    font-size: 1.05rem;
  }

  input[type="text"],
  textarea,
  input[type="datetime-local"] {
    width: 100%;
    padding: 10px 12px;
    border-radius: 6px;
    border: 1px solid #007acc;
    font-size: 1rem;
    background-color: #f4f9ff;
    color: #00224d;
    box-sizing: border-box;
  }

  textarea {
    resize: vertical;
    min-height: 150px;
  }

  button[type="submit"] {
    margin-top: 25px;
    padding: 12px 24px;
    background-color: #0059b3;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  button[type="submit"]:hover {
    background-color: #003d7a;
  }
</style>
</head>
<body>

<div class="sidebar">
  <div class="logo">
    <img src="../img/logo.png" alt="Logo" />
  </div>
  <a href="turma.php?id=<?= $atividade['turma_id'] ?>">👈 Voltar à turma</a>
  <a href="../logout.php">👋 Sair</a>
</div>

<div class="main-content">
  <h1>✏️ Editar Atividade</h1>

  <form method="POST">
    <label for="titulo">Título:</label>
    <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($atividade['titulo']) ?>" required>

    <label for="descricao">Descrição:</label>
    <textarea id="descricao" name="descricao" rows="6" required><?= htmlspecialchars($atividade['descricao']) ?></textarea>

    <label for="data_limite">Data Limite:</label>
    <input type="datetime-local" id="data_limite" name="data_limite"
           value="<?= $atividade['data_limite'] ? date('Y-m-d\TH:i', strtotime($atividade['data_limite'])) : '' ?>">

    <button type="submit">💾 Salvar Alterações</button>
  </form>
</div>

</body>
</html>
