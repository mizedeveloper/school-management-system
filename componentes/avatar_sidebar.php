<?php
$usuario_id = $_SESSION['id'];
$avatar_path = "../uploads/perfis/usuario_" . $usuario_id . ".jpg";
$tem_avatar = file_exists($avatar_path);
?>

<!-- Avatar na sidebar -->
<div style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); text-align: center;">
  <form method="POST" enctype="multipart/form-data" id="formAvatar" style="display: none;" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
        <input type="file" name="avatar" id="inputAvatar" accept="image/*" onchange="document.getElementById('formAvatar').submit()" />
    </form>

    <div onclick="document.getElementById('inputAvatar').click()" 
        style="cursor: pointer; border-radius: 50%; width: 60px; height: 60px; overflow: hidden; margin: 0 auto; border: 2px solid white; background: #fff;">
        <?php if ($tem_avatar): ?>
            <img src="<?= $avatar_path ?>?v=<?= time() ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;" />
        <?php else: ?>
            <div style="width: 100%; height: 100%; background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                👤
            </div>
        <?php endif; ?>
    </div>

</div>
