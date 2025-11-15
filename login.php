<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background-color: white; padding: 2rem; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 300px; }
        .login-container h2 { text-align: center; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: .5rem; }
        .form-group input { width: 100%; padding: .5rem; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        .btn { width: 100%; padding: .7rem; background-color: #333; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 1rem; }
        .btn:hover { background-color: #555; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; text-align: center; }
        .back-link { display: block; text-align: center; margin-top: 1rem; color: #333; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php
        if (isset($_GET['error'])) {
            echo '<p class="error">' . htmlspecialchars($_GET['error']) . '</p>';
        }
        ?>
        <form action="auth.php" method="POST">
            <div class="form-group">
                <label for="login">Usuário</label>
                <input type="text" id="login" name="login" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>
        <a href="index.php" class="back-link">Voltar ao Painel</a>
    </div>
</body>
</html>
