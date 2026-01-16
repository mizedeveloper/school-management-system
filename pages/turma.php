<?php
session_start();

include '../db.php';
include '../componentes/avatar_upload.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'professor') {
    header('Location: ../login.php');
    exit();
}

$usuario_id = $_SESSION['id'];
$turma_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$turma_id) die("Turma inválida.");

// Excluir atividade se solicitado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_atividade_id'])) {
    $atividade_id = (int) $_POST['excluir_atividade_id'];

    // Confirma se o professor é dono da atividade
    $stmt = $conn->prepare("SELECT id FROM atividades WHERE id = ? AND professor_id = ?");
    $stmt->bind_param("ii", $atividade_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $del_stmt = $conn->prepare("DELETE FROM atividades WHERE id = ?");
        $del_stmt->bind_param("i", $atividade_id);
        $del_stmt->execute();
        $del_stmt->close();
    }
    $stmt->close();
    header("Location: turma.php?id=$turma_id");
    exit();
}

// Verifica se o professor pertence à turma
$stmt = $conn->prepare("
    SELECT t.nome, t.codigo
    FROM turmas t
    JOIN usuario_turma ut ON t.id = ut.turma_id
    WHERE t.id = ? AND ut.usuario_id = ? AND ut.tipo = 'professor'
");
$stmt->bind_param('ii', $turma_id, $usuario_id);
$stmt->execute();
$turma = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$turma) die("Você não tem acesso a essa turma.");

$nome_turma = $turma['nome'];
$codigo_turma = $turma['codigo'];

// Puxa atividades
$stmt = $conn->prepare("
    SELECT a.*, u.nome AS professor_nome
    FROM atividades a
    JOIN usuarios u ON a.professor_id = u.id
    WHERE a.turma_id = ?
    ORDER BY a.id DESC
");
$stmt->bind_param("i", $turma_id);
$stmt->execute();
$atividades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Turma: <?= htmlspecialchars($nome_turma) ?> - S.E.A 🌊</title>

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

  /* Botão configurações turma */
  .config-btn {
    position: absolute;
    top: 20px;
    right: 30px;
    background-color: #00cfff;
    border: none;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    color: #002d4c;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0, 204, 255, 0.6);
    transition: transform 0.3s ease, background-color 0.3s ease;
    text-decoration: none;
  }
  .config-btn:hover {
    background-color: #0099cc;
    transform: rotate(10deg);
    color: #e0f7ff;
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

  /* Botão excluir atividade */
  .excluir-atividade-btn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.5rem;
    color: #cc0000;
    padding: 0;
    line-height: 1;
    transition: transform 0.2s ease, color 0.3s ease;
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .excluir-atividade-btn:hover {
    transform: scale(1.3);
    color: #ff4444;
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

<script>
function confirmarExclusao(atividadeId) {
    if (confirm("Tem certeza que deseja excluir esta atividade?")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'excluir_atividade_id';
        input.value = atividadeId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="../img/logo.png" alt="Logo S.E.A" />
    </div>
    <a href="professor.php">🏠 Início</a>
    <a href="marcar_atividade.php?turma_id=<?= $turma_id ?>">📝 Atividade</a>
    <a href="marcar_ocorrencia.php?turma_id=<?= $turma_id ?>">💀 Ocorrência</a>
    <a href="ver_usuarios.php?turma_id=<?= $turma_id ?>">👥 Usuários</a>
    <a href="../logout.php">👋 Sair</a>
    <?php include '../componentes/avatar_sidebar.php'; ?>
</div>

<div class="main-content">
    <a class="config-btn" title="Configurar Turma" href="configurar_turma.php?turma_id=<?= $turma_id ?>">⚙</a>

    <header>
        <h1>Bem-vindo à turma <?= htmlspecialchars($nome_turma) ?>! 🧐</h1>
        <p><i>Ensinar é crescer.</i></p>
    </header>

    <section class="atividades-section">
        <h2>Atividades Enviadas</h2>

        <?php if (count($atividades) === 0): ?>
            <p class="sem-atividades">💤 Nenhuma atividade enviada ainda. 💤</p>
        <?php else: ?>
            <div class="atividades-lista">
                <?php foreach ($atividades as $atividade): ?>
                    <article class="atividade-card">
                        <header class="atividade-header">
                            <div>
                                <h3>
                                    <a href="atividade.php?id=<?= $atividade['id'] ?>" class="atividade-link">
                                        <?= htmlspecialchars($atividade['titulo']) ?>
                                    </a>
                                </h3>
                                <?php if (!empty($atividade['data_limite'])): ?>
                                    <time datetime="<?= $atividade['data_limite'] ?>" class="data-limite">
                                        Entrega até: <?= date('d/m/Y', strtotime($atividade['data_limite'])) ?>
                                    </time>
                                <?php endif; ?>
                            </div>

                            <?php if ($atividade['professor_id'] == $usuario_id): ?>
                                <button class="excluir-atividade-btn" title="Excluir atividade" onclick="confirmarExclusao(<?= $atividade['id'] ?>)">❌</button>
                            <?php else: ?>
                                <p class="professor-nome">Enviada por: <?= htmlspecialchars($atividade['professor_nome']) ?></p>
                            <?php endif; ?>
                        </header>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

</body>
</html>
