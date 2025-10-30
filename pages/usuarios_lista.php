<?php
include 'login_user/conexao.php';

// Buscar todos os usuários
$stmt = $pdo->query("SELECT id_usuario, nome, email, tipo, data_cadastro 
                     FROM usuarios 
                     ORDER BY data_cadastro DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 style="color: #2c3e50; margin-bottom: 25px;">👥 Gerenciamento de Usuários</h2>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Tipo</th>
                <th>Data de Cadastro</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($usuarios) > 0): ?>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo $usuario['id_usuario']; ?></td>
                    <td><strong><?php echo htmlspecialchars($usuario['nome']); ?></strong></td>
                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                    <td>
                        <?php
                        $tipo = ucfirst($usuario['tipo']);
                        $badge_class = '';
                        switch ($usuario['tipo']) {
                            case 'admin':
                                $badge_class = 'badge-danger';
                                break;
                            case 'orientador':
                                $badge_class = 'badge-warning';
                                break;
                            case 'aluno':
                                $badge_class = 'badge-info';
                                break;
                        }
                        ?>
                        <span class="badge <?php echo $badge_class; ?>">
                            <?php echo $tipo; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($usuario['data_cadastro'])); ?></td>
                    <td>
                        <button class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">Editar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #7f8c8d;">
                        Nenhum usuário cadastrado.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div style="margin-top: 20px; padding: 15px; background: #e8f4f8; border-left: 4px solid #3498db; border-radius: 5px;">
    <p style="margin: 0; color: #2c3e50;">
        <strong>ℹ️ Informação:</strong> Total de <?php echo count($usuarios); ?> usuário(s) cadastrado(s) no sistema.
    </p>
</div>
