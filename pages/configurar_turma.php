<?php
session_start();
include '../db.php';
include '../componentes/avatar_upload.php';
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'professor') {
    header('Location: ../login.php');
    exit();
}

$professor_id = $_SESSION['id'];
$turma_id = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;

if (!$turma_id) {
    die('Turma inválida.');
}

$stmt = $conn->prepare("SELECT t.nome, t.codigo FROM turmas t JOIN usuario_turma ut ON ut.turma_id = t.id WHERE t.id = ? AND ut.usuario_id = ? AND ut.tipo = 'professor'");
$stmt->bind_param('ii', $turma_id, $professor_id);
$stmt->execute();
$result = $stmt->get_result();
$turma = $result->fetch_assoc();

if (!$turma) {
    die('Você não tem permissão para essa turma.');
}

$mensagem = '';
$novo_nome = $turma['nome'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_nome'])) {
    $novo_nome = trim($_POST['novo_nome']);
    if (strlen($novo_nome) > 2) {
        $stmt = $conn->prepare("UPDATE turmas SET nome = ? WHERE id = ?");
        $stmt->bind_param('si', $novo_nome, $turma_id);
        if ($stmt->execute()) {
            $mensagem = "Nome da turma atualizado com sucesso.";
            $turma['nome'] = $novo_nome;
        } else {
            $mensagem = "Erro ao atualizar o nome.";
        }
    } else {
        $mensagem = "O nome deve ter pelo menos 3 caracteres.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_turma'])) {
    $conn->query("DELETE FROM usuario_turma WHERE turma_id = $turma_id");
    $conn->query("DELETE FROM atividades WHERE turma_id = $turma_id");
    $conn->query("DELETE FROM turmas WHERE id = $turma_id");
    header("Location: professor.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Configurar Turma - S.E.A 🌊</title>

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

/* Main */
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

/* Formulários */
form {
  margin-top: 24px;
  max-width: 600px;
}
form label {
  display: block;
  margin-top: 15px;
  font-weight: 600;
}
form input[type="text"] {
  width: 100%;
  padding: 10px 14px;
  border-radius: 6px;
  border: 1px solid #004466;
  font-size: 1rem;
  background: #e9f2ff;
  color: #003355;
}
form input[type="text"]:focus {
  background-color: #cde6ff;
  outline: none;
  border-color: #00cfff;
}

/* Botões */
button {
  margin-top: 20px;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 700;
  font-size: 1.1rem;
  transition: 0.3s ease;
  border: none;
}
button.salvar-btn {
  background-color: #00cfff;
  color: #002d4c;
  box-shadow: 0 6px 14px rgba(0, 204, 255, 0.6);
  width: 105%; /* Faz o botão ter a mesma largura do input */
  padding: 12px 0; /* Para alinhar visualmente com o input */
}

button.salvar-btn:hover {
  background-color: #0099cc;
  color: #e0f7ff;
  box-shadow: 0 8px 20px rgba(0, 153, 204, 0.8);
}
button.excluir-btn {
  background-color: #dc3545;
  color: #fff;
  box-shadow: 0 6px 14px rgba(220, 53, 69, 0.6);
  width: 105%;
}
button.excluir-btn:hover {
  background-color: #a71d2a;
  box-shadow: 0 8px 20px rgba(167, 29, 42, 0.8);
}

/* Código turma */
.codigo-turma {
  font-size: 1.2rem;
  background: #f0f5ff;
  color: #004080;
  padding: 12px 16px;
  border-radius: 10px;
  display: inline-block;
  margin-top: 15px;
  user-select: all;
  box-shadow: inset 0 0 15px #00cfff55;
  cursor: pointer;
  transition: background-color 0.3s ease;
}
.codigo-turma:hover {
  background-color: #d0e7ff;
}
</style>

</head>
<body>

<div class="sidebar">
  <div class="logo"><img src="../img/logo.png" alt="Logo S.E.A" /></div>
  <a href="professor.php">🏠 Início</a>
  <a href="turma.php?id=<?= $turma_id ?>">👈 Voltar à turma</a>
  <a href="../logout.php">👋 Sair</a>
  <?php include '../componentes/avatar_sidebar.php'; ?>
</div>

<div class="main-content">
  <header class="professor-header">
    <h1>🛠️ Configurações da Turma</h1>
    <p>Gerencie o nome e código da turma</p>
    <br>
  </header>

  <?php if ($mensagem): ?>
    <p class="mensagem <?= strpos($mensagem, 'sucesso') !== false ? 'sucesso' : 'erro' ?>">
      <?= htmlspecialchars($mensagem) ?>
    </p>
  <?php endif; ?>

  <form method="POST">
    <label for="novo_nome">Nome da turma:</label>
    <br> <p>
    <input type="text" name="novo_nome" id="novo_nome" value="<?= htmlspecialchars($turma['nome']) ?>" required />
    <p></p>  <button type="submit" class="salvar-btn">Salvar Alterações</button>
  </form>
 <br> <p>
  <p><strong>Código da turma:</strong></p>
  <div class="codigo-turma" title="Clique para copiar" tabindex="0" role="button">
    <?= htmlspecialchars($turma['codigo']) ?>
  </div>

  <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta turma? Esta ação não pode ser desfeita.');">
    <button type="submit" name="excluir_turma" class="excluir-btn">🗑️ Excluir Turma</button>
  </form>
</div>

<script>
const codigoEl = document.querySelector('.codigo-turma');
codigoEl.addEventListener('click', () => {
  navigator.clipboard.writeText(codigoEl.textContent.trim());
  alert('Código copiado!');
});
codigoEl.addEventListener('keydown', (e) => {
  if (e.key === 'Enter' || e.key === ' ') {
    e.preventDefault();
    codigoEl.click();
  }
});
</script>

</body>
</html>
