<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $usuario_id = $_SESSION['id'];
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $nome_final = "../uploads/perfis/usuario_" . $usuario_id . ".jpg";

    if ($_FILES['avatar']['error'] === 0 && in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
        move_uploaded_file($_FILES['avatar']['tmp_name'], $nome_final);
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}
