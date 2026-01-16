<?php
session_start();

$usuario_id = $_SESSION['id'];
$nome_professor = $_SESSION['nome'];
$mensagem = '';

// Caminho do avatar
$avatar_path = "../uploads/perfis/usuario_" . $usuario_id . ".jpg";
$tem_avatar = file_exists($avatar_path);

// Upload do avatar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $nome_final = "../uploads/perfis/usuario_" . $usuario_id . ".jpg";

    if ($_FILES['avatar']['error'] === 0 && in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
        move_uploaded_file($_FILES['avatar']['tmp_name'], $nome_final);
        header("Location: professor.php");
        exit;
    } else {
        $mensagem = "Erro ao enviar imagem. Tente JPG ou PNG.";
    }
}

include '../db.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'professor') {
    header('Location: ../login.php');
    exit();
}

$usuario_id = $_SESSION['id'];
$nome_professor = $_SESSION['nome'];
$mensagem = '';

function gerarCodigoUnico($conn, $tamanho = 8) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    do {
        $codigo = '';
        for ($i = 0; $i < $tamanho; $i++) {
            $codigo .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $stmt = $conn->prepare("SELECT id FROM turmas WHERE codigo = ?");
        $stmt->bind_param('s', $codigo);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);

    return $codigo;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_turma'])) {
    $nome = trim($_POST['nome_turma']);

    if (strlen($nome) >= 3 && strlen($nome) <= 100) {
        $codigo = gerarCodigoUnico($conn);

        $stmt = $conn->prepare("INSERT INTO turmas (nome, codigo) VALUES (?, ?)");
        $stmt->bind_param('ss', $nome, $codigo);
        if ($stmt->execute()) {
            $nova_turma_id = $stmt->insert_id;

            $stmt = $conn->prepare("INSERT INTO usuario_turma (usuario_id, turma_id, tipo) VALUES (?, ?, 'professor')");
            $stmt->bind_param('ii', $usuario_id, $nova_turma_id);
            $stmt->execute();

            $mensagem = "Turma criada com sucesso!";
        } else {
            $mensagem = "Erro ao criar turma. Tente novamente.";
        }
    } else {
        $mensagem = "O nome da turma deve ter entre 3 e 100 caracteres.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo_turma'])) {
    $codigo_turma = strtoupper(trim($_POST['codigo_turma']));

    $stmt = $conn->prepare("SELECT id FROM turmas WHERE codigo = ?");
    $stmt->bind_param('s', $codigo_turma);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($turma = $result->fetch_assoc()) {
        $turma_id = $turma['id'];

        $stmt = $conn->prepare("SELECT id FROM usuario_turma WHERE usuario_id = ? AND turma_id = ?");
        $stmt->bind_param('ii', $usuario_id, $turma_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO usuario_turma (usuario_id, turma_id, tipo) VALUES (?, ?, 'professor')");
            $stmt->bind_param('ii', $usuario_id, $turma_id);
            if ($stmt->execute()) {
                $mensagem = "Você entrou na turma com sucesso!";
            } else {
                $mensagem = "Erro ao entrar na turma.";
            }
        } else {
            $mensagem = "Você já está nesta turma.";
        }
    } else {
        $mensagem = "Código da turma inválido.";
    }
}

$stmt = $conn->prepare("
    SELECT t.id, t.nome, t.codigo
    FROM turmas t
    JOIN usuario_turma ut ON t.id = ut.turma_id
    WHERE ut.usuario_id = ? AND ut.tipo = 'professor'
    ORDER BY t.nome
");
$stmt->bind_param('i', $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$turmas = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Painel do Professor - S.E.A 🌊</title>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<style>
  /* Fundo oceânico */
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
  .sidebar a:hover,
  .sidebar a.active {
    background-color: #3765cc;
    color: #fff;
  }
  .sidebar a i {
    font-size: 1.25rem;
  }

  /* Avatar sidebar (assumindo que avatar_sidebar.php coloca img ou ícone) */
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
  }
  .professor-header h1 {
    margin-bottom: 5px;
    color: #00cfff;
    text-shadow: 0 0 5px #00cfffaa;
  }
  .professor-header p {
    margin-top: 0;
    font-size: 1.15rem;
    color: #c0d8ff;
  }

  /* Turmas cards */
  .cards-turmas {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 20px;
  }
  .cards-turmas a {
    background: rgba(255 255 255 / 0.9);
    color: #000;
    text-decoration: none;
    border-radius: 10px;
    padding: 18px 22px;
    width: 220px;
    box-shadow: 0 0 12px rgba(0,0,0,0.2);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    font-weight: 600;
  }
  .cards-turmas a:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.3);
  }
  .cards-turmas a h3 {
    margin: 0;
  }

  /* Mensagens */
  .mensagem {
    margin-top: 20px;
    padding: 12px 15px;
    background: #00cfff88;
    color: #002d4c;
    font-weight: 600;
    border-radius: 8px;
    max-width: 480px;
  }

  /* Botão flutuante */
  #btnAbrirPopup {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background-color: #00cfff;
    color: #002d4c;
    border: none;
    border-radius: 50%;
    width: 56px;
    height: 56px;
    font-size: 30px;
    cursor: pointer;
    box-shadow: 0 6px 15px rgba(0, 204, 255, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.3s ease;
    z-index: 9999;
  }
  #btnAbrirPopup:hover {
    background-color: #0099cc;
  }

  /* Popup modal */
  #popupModal {
    display: none;
    position: fixed;
    z-index: 9998;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.55);
  }

  #popupModal .popup-content {
    background-color: #e6f7ff;
    margin: 10% auto;
    padding: 25px 35px;
    border-radius: 14px;
    max-width: 420px;
    box-shadow: 0 12px 28px rgba(0, 140, 200, 0.3);
    position: relative;
    color: #002d4c;
  }

  #popupModal .popup-content h2 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 1.6rem;
    text-align: center;
  }

  #popupModal .popup-content label {
    font-weight: 600;
    display: block;
    margin-top: 10px;
    margin-bottom: 6px;
    color: #002d4c;
  }

  #popupModal .popup-content input[type="text"] {
    width: 100%;
    padding: 10px 14px;
    border-radius: 8px;
    border: 1.5px solid #99d1f7;
    font-size: 1rem;
    box-sizing: border-box;
  }

  #popupModal .popup-content button {
    margin-top: 20px;
    background-color: #00cfff;
    color: #002d4c;
    border: none;
    padding: 12px 0;
    font-size: 1.1rem;
    border-radius: 10px;
    cursor: pointer;
    width: 100%;
    font-weight: 700;
    transition: background-color 0.3s ease;
  }

  #popupModal .popup-content button:hover {
    background-color: #0099cc;
  }

  /* Botão fechar */
  #popupModal .btn-fechar {
    position: absolute;
    top: 14px;
    right: 20px;
    font-size: 26px;
    color: #336699;
    cursor: pointer;
    transition: color 0.3s ease;
  }
  #popupModal .btn-fechar:hover {
    color: #003355;
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
    <a href="professor.php" class="active"><i class="fas fa-home"></i>🏠 Início</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>👋 Sair</a>

    <?php include '../componentes/avatar_sidebar.php'; ?>
</div>

<div class="main-content">
    <header class="professor-header">
        <h1>Painel do Professor 🌊</h1>
        <p>Bem-vindo, <?= htmlspecialchars($nome_professor) ?> 😀</p>
    </header>

    <section>
        <?php if (count($turmas) > 0): ?>
            <h2>Suas turmas</h2>
            <div class="cards-turmas">
                <?php foreach ($turmas as $turma): ?>
                   <a href="turma.php?id=<?= $turma['id'] ?>">
                        <h3><?= htmlspecialchars($turma['nome']) ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Você não está cadastrado em nenhuma turma. 😭</p>
        <?php endif; ?>

        <?php if ($mensagem): ?>
            <div class="mensagem"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>
    </section>
</div>

<button id="btnAbrirPopup" title="Criar ou entrar em turma">👩🏻‍🏫</button>

<div id="popupModal">
    <div class="popup-content">
        <span class="btn-fechar" id="btnFecharPopup">&times;</span>
        
        <h2>Criar nova turma</h2>
        <form method="POST" autocomplete="off" id="formCriarTurma">
            <label for="nome_turma">Nome da turma:</label>
            <input type="text" name="nome_turma" id="nome_turma" required minlength="3" maxlength="100" />
            <button type="submit">Criar turma</button>
        </form>

        <hr style="margin: 25px 0;" />

        <h2>Entrar em uma turma existente</h2>
        <form method="POST" autocomplete="off" id="formEntrarTurma">
            <label for="codigo_turma">Código da turma:</label>
            <input type="text" name="codigo_turma" id="codigo_turma" required maxlength="8" style="text-transform: uppercase;" />
            <button type="submit">Entrar</button>
        </form>
    </div>
</div>

<script>
const btnAbrir = document.getElementById('btnAbrirPopup');
const popup = document.getElementById('popupModal');
const btnFechar = document.getElementById('btnFecharPopup');

btnAbrir.addEventListener('click', () => {
    popup.style.display = 'block';
});

btnFechar.addEventListener('click', () => {
    popup.style.display = 'none';
});

window.addEventListener('click', (e) => {
    if (e.target === popup) {
        popup.style.display = 'none';
    }
});
</script>

</body>
</html>
