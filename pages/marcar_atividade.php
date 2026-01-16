<?php
session_start();
include '../db.php';
include '../componentes/avatar_upload.php';

// Verifica se está logado e é professor
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'professor') {
    header("Location: ../login.php");
    exit();
}

$professor_id = $_SESSION['id'];
$nome_professor = $_SESSION['nome'];
$mensagem = '';

// Pega turma via GET (id ou turma_id)
$turma_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : null);
if (!$turma_id) {
    echo "<p>Turma não especificada.</p>";
    exit();
}

// Confirma que o professor tem acesso a essa turma
$stmt = $conn->prepare("SELECT nome FROM turmas t JOIN usuario_turma ut ON t.id = ut.turma_id WHERE t.id = ? AND ut.usuario_id = ? AND ut.tipo = 'professor'");
$stmt->bind_param('ii', $turma_id, $professor_id);
$stmt->execute();
$result = $stmt->get_result();
$turma = $result->fetch_assoc();

if (!$turma) {
    echo "<p>Turma inválida ou sem permissão.</p>";
    exit();
}

$nome_turma = $turma['nome'];

// Processa envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'], $_POST['descricao'])) {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $data_limite = !empty($_POST['data_limite']) ? $_POST['data_limite'] : null;

    // Validação da data limite
    if ($data_limite !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_limite)) {
        $mensagem = "Data limite inválida. Use o formato YYYY-MM-DD.";
    } elseif (empty($titulo)) {
        $mensagem = "O título é obrigatório.";
    } elseif (empty($descricao)) {
        $mensagem = "A descrição é obrigatória.";
    } else {
        $destino = null;

        // Verifica se arquivo foi enviado e sucesso no upload
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
            $nome_arquivo = basename($_FILES['arquivo']['name']);
            $destino = '../uploads/' . time() . '_' . $nome_arquivo;

            if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
                $mensagem = "Erro no upload do arquivo.";
            }
        }

        if (!$mensagem) {
            if ($destino) {
                $stmt = $conn->prepare("INSERT INTO atividades (titulo, descricao, arquivo, professor_id, turma_id, data_envio, data_limite) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
                $stmt->bind_param("sssiss", $titulo, $descricao, $destino, $professor_id, $turma_id, $data_limite);
            } else {
                $stmt = $conn->prepare("INSERT INTO atividades (titulo, descricao, professor_id, turma_id, data_envio, data_limite) VALUES (?, ?, ?, ?, NOW(), ?)");
                $stmt->bind_param("ssiis", $titulo, $descricao, $professor_id, $turma_id, $data_limite);
            }

            if ($stmt->execute()) {
                $mensagem = "Atividade enviada com sucesso.";
            } else {
                $mensagem = "Erro ao salvar atividade.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Marcar Atividade - S.E.A 🌊</title>

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

  header.professor-header h1 {
    color: #00cfff;
    text-shadow: 0 0 6px #00cfffaa;
    margin-bottom: 4px;
  }
  header.professor-header p {
    font-style: italic;
    color: #a9cfff;
    margin-top: 0;
  }

  /* Mensagem */
  p.mensagem {
    font-weight: 700;
    margin-top: 12px;
  }
  p.mensagem.sucesso {
    color: #00cc66;
  }
  p.mensagem.erro {
    color: #cc4444;
  }

  /* Formulário */
  form {
    margin-top: 24px;
    max-width: 600px;
  }

  form label {
    display: block;
    margin-top: 15px;
    font-weight: 600;
  }
  form input[type="text"],
  form textarea,
  form input[type="date"],
  form input[type="file"] {
    width: 100%;
    padding: 10px 14px;
    box-sizing: border-box;
    border-radius: 6px;
    border: 1px solid #004466;
    font-size: 1rem;
    background: #e9f2ff;
    color: #003355;
    transition: background-color 0.3s ease;
    font-family: 'Poppins', sans-serif;
  }
  form input[type="text"]:focus,
  form textarea:focus,
  form input[type="date"]:focus,
  form input[type="file"]:focus {
    background-color: #cde6ff;
    outline: none;
    border-color: #00cfff;
  }

  form textarea {
    resize: vertical;
    min-height: 100px;
  }

  /* Botão */
  button[type="submit"] {
    margin-top: 20px;
    padding: 12px 24px;
    background-color: #00cfff;
    color: #002d4c;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 700;
    font-size: 1.1rem;
    box-shadow: 0 6px 14px rgba(0, 204, 255, 0.6);
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }
  button[type="submit"]:hover {
    background-color: #0099cc;
    box-shadow: 0 8px 20px rgba(0, 153, 204, 0.8);
    color: #e0f7ff;
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
    <a href="professor.php">🏠 Início</a>
    <a href="turma.php?id=<?= $turma_id ?>">👈 Voltar à turma</a>
    <a href="../logout.php">👋 Sair</a>
    <?php include '../componentes/avatar_sidebar.php'; ?>
</div>

<div class="main-content">
    <header class="professor-header">
        <h1>Atividades - <?= htmlspecialchars($nome_turma) ?></h1>
        <p>Professor: <?= htmlspecialchars($nome_professor) ?></p>
    </header>

    <?php if ($mensagem): ?>
        <p class="mensagem <?= strpos($mensagem, 'sucesso') !== false ? 'sucesso' : 'erro' ?>">
            <?= htmlspecialchars($mensagem) ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" autocomplete="off" novalidate>
        <label for="titulo">Título da Atividade:</label>
        <input type="text" name="titulo" id="titulo" required maxlength="100" />

        <label for="descricao">Descrição da Atividade:</label>
        <textarea name="descricao" id="descricao" rows="4" required></textarea>

        <label for="data_limite">Data Limite de Entrega (opcional):</label>
        <input type="date" name="data_limite" id="data_limite" />

        <label for="arquivo">Arquivo da Atividade (PDF, Imagem etc) (opcional):</label>
        <input type="file" name="arquivo" id="arquivo" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" />

        <button type="submit">Enviar Atividade</button>
    </form>
</div>

</body>
</html>
