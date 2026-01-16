<?php
session_start();
include '../db.php';
include '../componentes/avatar_upload.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'aluno') {
    header('Location: ../login.php');
    exit();
}

$usuario_id = $_SESSION['id'];
$mensagem = '';

// Sair da turma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sair_turma_id'])) {
    $sair_turma_id = (int)$_POST['sair_turma_id'];
    $stmt = $conn->prepare("DELETE FROM usuario_turma WHERE usuario_id = ? AND turma_id = ? AND tipo = 'aluno'");
    $stmt->bind_param('ii', $usuario_id, $sair_turma_id);
    $stmt->execute();
    $mensagem = "Você saiu da turma com sucesso.";
}

// Entrar em turma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo_turma'])) {
    $codigo = strtoupper(trim($_POST['codigo_turma']));

    if (preg_match('/^[A-Z0-9]{8}$/', $codigo)) {
        $stmt = $conn->prepare("SELECT id FROM turmas WHERE codigo = ?");
        $stmt->bind_param('s', $codigo);
        $stmt->execute();
        $result = $stmt->get_result();
        $turma = $result->fetch_assoc();

        if ($turma) {
            $turma_id = $turma['id'];
            $stmt = $conn->prepare("SELECT id FROM usuario_turma WHERE usuario_id = ? AND turma_id = ?");
            $stmt->bind_param('ii', $usuario_id, $turma_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                $stmt = $conn->prepare("INSERT INTO usuario_turma (usuario_id, turma_id, tipo) VALUES (?, ?, 'aluno')");
                $stmt->bind_param('ii', $usuario_id, $turma_id);
                $stmt->execute();
                $mensagem = "Você entrou na turma com sucesso!";
            } else {
                $mensagem = "Você já está nesta turma.";
            }
        } else {
            $mensagem = "Código de turma inválido.";
        }
    } else {
        $mensagem = "Código deve ter 8 caracteres alfanuméricos.";
    }
}

// Buscar turmas
$stmt = $conn->prepare("
    SELECT t.id, t.nome, t.codigo
    FROM turmas t
    JOIN usuario_turma ut ON t.id = ut.turma_id
    WHERE ut.usuario_id = ? AND ut.tipo = 'aluno'
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
    <title>Área do Aluno - S.E.A 🌊</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
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

        .main-content {
            flex: 1;
            padding: 30px 40px;
            background: rgba(0,0,50,0.6);
            overflow-y: auto;
        }

        h1 {
            color: #00cfff;
            text-shadow: 0 0 5px #00cfffaa;
        }

        h2 {
            margin-top: 30px;
            color: #cceaff;
        }

        .mensagem {
            margin-top: 20px;
            padding: 12px 15px;
            background: #00cfff88;
            color: #002d4c;
            font-weight: 600;
            border-radius: 8px;
            max-width: 480px;
        }

        .cards-turmas {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .card-turma {
            position: relative;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 20px;
            width: 220px;
            box-shadow: 0 0 12px rgba(0,0,0,0.2);
            color: #000;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-turma:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.3);
        }

        .card-turma h3 {
            margin: 0;
            font-size: 1.2rem;
        }

        .card-turma a {
            text-decoration: none;
            color: inherit;
        }

        .menu-opcoes {
            position: absolute;
            top: 8px;
            right: 10px;
            cursor: pointer;
            font-size: 18px;
        }

        .popup-sair {
            position: absolute;
            top: 32px;
            right: 10px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 6px 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            display: none;
        }

        .popup-sair button {
            background: none;
            border: none;
            color: #c00;
            cursor: pointer;
            font-size: 14px;
        }

        form {
            margin-top: 20px;
            max-width: 400px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
            box-sizing: border-box;
            margin-bottom: 10px;
        }

        button[type="submit"] {
            background-color: #00cfff;
            color: #002d4c;
            border: none;
            padding: 12px;
            font-size: 1.1rem;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            
            background-color: #0099cc;
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
        function togglePopup(id) {
            const popup = document.getElementById('popup-' + id);
            const popups = document.querySelectorAll('.popup-sair');
            popups.forEach(p => {
                if (p !== popup) p.style.display = 'none';
            });
            popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
        }

        function confirmarSaida(nome, formId) {
            if (confirm("Tem certeza que deseja sair da turma '" + nome + "'?")) {
                document.getElementById(formId).submit();
            }
        }

        document.addEventListener('click', function(event) {
            if (!event.target.classList.contains('menu-opcoes')) {
                document.querySelectorAll('.popup-sair').forEach(p => p.style.display = 'none');
            }
        });
    </script>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <img src="../img/logo.png" alt="Logo S.E.A" />
    </div>
    <a href="aluno.php" class="active">🏠 Início</a>
    <a href="../logout.php">👋 Sair</a>
    <?php include '../componentes/avatar_sidebar.php'; ?>
</div>

<div class="main-content">
    <header>
        <h1>Área do Aluno 🌊</h1>
        <p>Bem-vindo, <?= htmlspecialchars($_SESSION['nome']) ?> 😀</p>
    </header>

    <section>
        <?php if (count($turmas) > 0): ?>
            <h2>Suas turmas</h2>
            <div class="cards-turmas">
                <?php foreach ($turmas as $turma): ?>
                    <div class="card-turma">
                        <a href="turma_aluno.php?id=<?= $turma['id'] ?>">
                            <h3><?= htmlspecialchars($turma['nome']) ?></h3>
                        </a>
                        <div class="menu-opcoes" onclick="togglePopup(<?= $turma['id'] ?>)">⋮</div>
                        <div class="popup-sair" id="popup-<?= $turma['id'] ?>">
                            <form id="form-sair-<?= $turma['id'] ?>" method="POST" style="margin: 0;">
                                <input type="hidden" name="sair_turma_id" value="<?= $turma['id'] ?>">
                                <button type="button" onclick="confirmarSaida('<?= htmlspecialchars($turma['nome'], ENT_QUOTES) ?>', 'form-sair-<?= $turma['id'] ?>')">Sair da turma</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Você não está cadastrado em nenhuma turma. 😔</p>
        <?php endif; ?>

        <?php if ($mensagem): ?>
            <div class="mensagem"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>
    </section>

    <h2>Entrar em uma turma</h2>
    <form method="POST" autocomplete="off">
        <label for="codigo_turma">Código da turma:</label>
        <input type="text" name="codigo_turma" id="codigo_turma" maxlength="8" required pattern="[A-Za-z0-9]{8}" style="text-transform: uppercase;" />
        <button type="submit">Entrar</button>
    </form>
</div>

</body>
</html>
