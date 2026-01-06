<?php
// Script para limpar a sessão
session_start();
session_destroy();

// Apagar cookies
setcookie('VeiGestSession', '', time() - 3600, '/');
setcookie('_identity', '', time() - 3600, '/');
setcookie('_csrf-frontend', '', time() - 3600, '/');
setcookie('_csrf-backend', '', time() - 3600, '/');

echo "✓ Sessão limpa com sucesso!<br>";
echo "Redirecionando para login em 2 segundos...<br>";
echo "<meta http-equiv='refresh' content='2;url=http://localhost/website-VeiGest/veigest/frontend/web/index.php?r=site/login'>";
?>
