<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Plataforma de Pesquisa</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-page">
    <!-- Added wrapper for better centering -->
    <div class="auth-container">
        <div class="auth-box">
            <h2>🔐 Login</h2>
            
            <?php if(isset($erro)) { ?>
                <div class="message error"><?php echo $erro; ?></div>
            <?php } ?>
            
            <form method="post" action="login.php">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit">Entrar</button>
            </form>

            <p class="auth-link">Não tem cadastro? <a href="cadastro_usuario.php">Clique aqui para se cadastrar</a></p>
        </div>
    </div>

    <?php
    session_start();
    include 'conexao.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['tipo'] = $usuario['tipo'];
            header("Location: ../index.php");
            exit;
        } else {
            $erro = "Email ou senha incorretos.";
        }
    }
    ?>
</body>
</html>
