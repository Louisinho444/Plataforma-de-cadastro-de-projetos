<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de cadastro</title>
</head>

<body>
    <form method="post" action="cadastro_usuario.php">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <select name="tipo">
            <option value="aluno">Aluno</option>
            <option value="orientador">Orientador</option>
            <option value="admin">Admin</option>
        </select>
        <button type="submit">Cadastrar</button>
    </form>

    <p>Já tem cadastro? <a href="login.php">Clique aqui para entrar</a></p>

</body>

</html>