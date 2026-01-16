<?php
include 'db.php';

// Gera hash da senha
$senha_plain = 'admin123';
$senha_hash = password_hash($senha_plain, PASSWORD_DEFAULT);

// Dados do admin
$nome = 'Administrador';
$usuario = 'admin';
$tipo = 'admin';

// Insere no banco
$stmt = $conn->prepare("INSERT INTO usuarios (nome, usuario, senha, tipo) VALUES (?, ?, ?, ?)");

if (!$stmt) {
    die("Erro no prepare: " . $conn->error);
}

$stmt->bind_param("ssss", $nome, $usuario, $senha_hash, $tipo);

if ($stmt->execute()) {
    echo "Admin criado com sucesso! Usuario: admin | Senha: admin123";
} else {
    echo "Erro ao criar admin: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
