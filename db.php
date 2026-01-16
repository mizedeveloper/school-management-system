<?php
// db.php - conexão genérica para XAMPP local

$servername = "localhost";
$username = "root";        // usuário padrão do XAMPP
$password = "";            // senha vazia padrão do XAMPP
$dbname = "sistema_escolar_final"; // seu banco já criado

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Chave de criptografia segura e vetores de inicialização
define('ENCRYPT_KEY', 'MinhaChaveUltraSecreta123'); // pode mudar isso depois
define('ENCRYPT_METHOD', 'AES-256-CBC');
define('ENCRYPT_IV', substr(hash('sha256', 'iv_padrao_unico'), 0, 16));


?>

