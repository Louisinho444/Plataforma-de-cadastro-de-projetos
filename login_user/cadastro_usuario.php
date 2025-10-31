<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Plataforma de Pesquisa</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-page">

    <div class="auth-container">
        <div class="auth-box">
            <h2>Cadastro</h2>
            
            <?php
            include 'conexao.php';
            $mensagem = '';
            $tipo_mensagem = '';

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $nome = $_POST['nome'];
                $email = $_POST['email'];
                $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $tipo = $_POST['tipo'];

                try {
                    $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':nome', $nome);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':senha', $senha);
                    $stmt->bindParam(':tipo', $tipo);

                    if ($stmt->execute()) {
                        $mensagem = "Usuário cadastrado com sucesso! Você já pode fazer login.";
                        $tipo_mensagem = "success";
                    }
                } catch (PDOException $e) {
                    $mensagem = "Erro ao cadastrar usuário. Este email pode já estar em uso.";
                    $tipo_mensagem = "error";
                }
            }
            ?>
            
            <?php if($mensagem) { ?>
                <div class="message <?php echo $tipo_mensagem; ?>"><?php echo $mensagem; ?></div>
            <?php } ?>
            
            <form method="post" action="cadastro_usuario.php">
                <input type="text" name="nome" placeholder="Nome completo" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="senha" placeholder="Senha" required minlength="6">
                <select name="tipo" required>
                    <option value="">Selecione o tipo de usuário</option>
                    <option value="aluno">Aluno</option>
                    <option value="orientador">Orientador</option>
                </select>
                <button type="submit">Cadastrar</button>
            </form>

            <p class="auth-link">Já tem cadastro? <a href="login.php">Clique aqui para entrar</a></p>
        </div>
    </div>
</body>
</html>
