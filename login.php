<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $erro = "Ação inválida.";
    } else {
        $usuario = trim($_POST['usuario']);
        $senha = $_POST['senha'];

        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['tipo'] = $user['tipo'];
            $_SESSION['tema'] = $user['tema'] ?? 'claro';
            $_SESSION['turma_id'] = $user['turma_id'];

            if ($user['tipo'] === 'admin') {
                header("Location: pages/admin.php");
            } elseif ($user['tipo'] === 'aluno') {
                header("Location: pages/aluno.php");
            } elseif ($user['tipo'] === 'professor') {
                header("Location: pages/professor.php");
            }
            exit();
        } else {
            $erro = "Usuário ou senha inválidos!";
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - S.E.A 🌊</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('wallpaper/foto.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
            text-shadow: 1px 1px 2px #000;
        }
        .login-container {
            background: rgba(0, 0, 50, 0.8);
            padding: 35px;
            border-radius: 12px;
            width: 360px;
            text-align: center;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.5);
            animation: slideIn 0.8s ease;
        }
        .logo {
            width: 120px;
            margin-bottom: 10px;
            animation: fadeIn 1s ease;
        }
        .login-container h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            color: #00cfff;
        }
        .login-container h3 {
            font-weight: 400;
            font-size: 1rem;
            margin-bottom: 20px;
        }
        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
        }
        .login-container input::placeholder {
            color: #888;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            background: #f4f4f4;
            color: #000;
        }
        .login-container button {
            padding: 12px;
            background: #00cfff;
            color: #000;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }
        .login-container button:hover {
            background: #00a9cc;
        }
        .erro {
            color: #ff6868;
            font-size: 0.95rem;
            margin-top: 12px;
        }
        .spinner {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .spinner div {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #00cfff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes slideIn {
            from { transform: translateY(-40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <img src="img/logo.png" alt="Logo do SEA" class="logo" />
        <h1>🌊 S.E.A</h1>
        <h3>Faça login e comece a surfar 🏄</h3>

        <?php if (isset($erro)): ?>
            <p class="erro">⚠️ <?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form method="POST" id="login-form" autocomplete="off">
            <input type="text" name="usuario" placeholder="Usuário" required />
            <input type="password" name="senha" placeholder="Senha" required />
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" />
            <button type="submit">Entrar</button>
        </form>
    </div>

    <div class="spinner" id="spinner"><div></div></div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const spinner = document.getElementById('spinner');
            spinner.style.display = 'flex';

            setTimeout(() => {
                spinner.style.display = 'none';
                e.target.submit();
            }, 1000);
        });
    </script>

</body>
</html>
