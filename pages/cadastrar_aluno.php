<?php
include 'login_user/conexao.php';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $curso = $_POST['curso'];

        $sql = "INSERT INTO alunos (nome, email, curso) VALUES (:nome, :email, :curso)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':curso' => $curso
        ]);

        $mensagem = "Aluno cadastrado com sucesso!";
        $tipo_mensagem = "success";
        $_POST = [];
        
    } catch (PDOException $e) {
        $mensagem = "Erro ao cadastrar aluno: " . $e->getMessage();
        $tipo_mensagem = "error";
    }
}
?>

<?php if ($mensagem): ?>
<div class="alert alert-<?php echo $tipo_mensagem; ?>">
    <?php echo htmlspecialchars($mensagem); ?>
</div>
<?php endif; ?>

<h2 style="color: #2c3e50; margin-bottom: 25px;">🎓 Cadastrar Aluno</h2>

<form method="POST" class="form-simples">
    <div class="form-group">
        <label for="nome">Nome Completo *</label>
        <input type="text" id="nome" name="nome" required placeholder="Nome do aluno">
    </div>

    <div class="form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" placeholder="email@exemplo.com">
    </div>

    <div class="form-group">
        <label for="curso">Curso *</label>
        <input type="text" id="curso" name="curso" required placeholder="Ex: Desenvolvimento de Sistemas">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-success">💾 Salvar</button>
        <a href="?page=novo-projeto" class="btn btn-secondary">Voltar</a>
    </div>
</form>

<style>
.form-simples {
    max-width: 600px;
}

.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}
</style>