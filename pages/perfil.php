<?php
include 'login_user/conexao.php';

$mensagem = '';
$tipo_mensagem = '';

// Buscar dados do usuário logado
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = :id_usuario");
$stmt->execute([':id_usuario' => $_SESSION['id_usuario']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha_atual = $_POST['senha_atual'];
        $senha_nova = $_POST['senha_nova'];
        $senha_confirma = $_POST['senha_confirma'];

        // Atualizar nome e email
        $sql = "UPDATE usuarios SET nome = :nome, email = :email WHERE id_usuario = :id_usuario";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':id_usuario' => $_SESSION['id_usuario']
        ]);

        // Atualizar senha se fornecida
        if (!empty($senha_atual) && !empty($senha_nova)) {
            if ($senha_nova !== $senha_confirma) {
                throw new Exception("As senhas não coincidem.");
            }

            if (!password_verify($senha_atual, $usuario['senha'])) {
                throw new Exception("Senha atual incorreta.");
            }

            $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET senha = :senha WHERE id_usuario = :id_usuario";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':senha' => $senha_hash,
                ':id_usuario' => $_SESSION['id_usuario']
            ]);
        }

        $_SESSION['nome'] = $nome;
        $mensagem = "Perfil atualizado com sucesso!";
        $tipo_mensagem = "success";

        // Recarregar dados do usuário
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = :id_usuario");
        $stmt->execute([':id_usuario' => $_SESSION['id_usuario']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $mensagem = "Erro: " . $e->getMessage();
        $tipo_mensagem = "error";
    }
}
?>

<?php if ($mensagem): ?>
<div class="alert alert-<?php echo $tipo_mensagem; ?>">
    <?php echo htmlspecialchars($mensagem); ?>
</div>
<?php endif; ?>

<h2 style="color: #2c3e50; margin-bottom: 25px;">⚙️ Meu Perfil</h2>

<div class="perfil-container">
    <div class="perfil-info">
        <div class="perfil-avatar">
            <div class="avatar-circle">
                <?php echo strtoupper(substr($usuario['nome'], 0, 2)); ?>
            </div>
        </div>
        <div class="perfil-dados">
            <h3><?php echo htmlspecialchars($usuario['nome']); ?></h3>
            <p><?php echo htmlspecialchars($usuario['email']); ?></p>
            <span class="badge badge-info"><?php echo ucfirst($usuario['tipo']); ?></span>
            <p style="margin-top: 10px; color: #7f8c8d; font-size: 14px;">
                Membro desde <?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?>
            </p>
        </div>
    </div>

    <form method="POST" class="form-perfil">
        <h3 style="margin-bottom: 20px; color: #2c3e50;">Dados Pessoais</h3>
        
        <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" required 
                   value="<?php echo htmlspecialchars($usuario['nome']); ?>">
        </div>

        <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" required 
                   value="<?php echo htmlspecialchars($usuario['email']); ?>">
        </div>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e0e0e0;">

        <h3 style="margin-bottom: 20px; color: #2c3e50;">Alterar Senha</h3>
        <p style="color: #7f8c8d; margin-bottom: 20px; font-size: 14px;">
            Deixe em branco se não deseja alterar a senha.
        </p>

        <div class="form-group">
            <label for="senha_atual">Senha Atual</label>
            <input type="password" id="senha_atual" name="senha_atual" 
                   placeholder="Digite sua senha atual">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="senha_nova">Nova Senha</label>
                <input type="password" id="senha_nova" name="senha_nova" 
                       placeholder="Digite a nova senha">
            </div>

            <div class="form-group">
                <label for="senha_confirma">Confirmar Nova Senha</label>
                <input type="password" id="senha_confirma" name="senha_confirma" 
                       placeholder="Confirme a nova senha">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">💾 Salvar Alterações</button>
            <a href="?page=dashboard" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<style>
.perfil-container {
    max-width: 800px;
}

.perfil-info {
    display: flex;
    gap: 30px;
    align-items: center;
    background: #f8f9fa;
    padding: 30px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.perfil-avatar {
    flex-shrink: 0;
}

.avatar-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 36px;
    font-weight: bold;
}

.perfil-dados h3 {
    margin: 0 0 5px 0;
    color: #2c3e50;
}

.perfil-dados p {
    margin: 5px 0;
    color: #555;
}

.form-perfil {
    background: white;
    padding: 30px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
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
