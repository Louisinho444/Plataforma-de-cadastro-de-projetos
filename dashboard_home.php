<?php
include 'login_user/conexao.php';

// Buscar estatísticas
$total_projetos = $pdo->query("SELECT COUNT(*) FROM projetos")->fetchColumn();

// Buscar status para contagem
$stmt_status = $pdo->query("SELECT s.descricao, COUNT(p.id_projeto) as total 
                            FROM status s 
                            LEFT JOIN projetos p ON s.id_status = p.id_status 
                            GROUP BY s.id_status, s.descricao");
$status_count = $stmt_status->fetchAll(PDO::FETCH_KEY_PAIR);

$total_alunos = $pdo->query("SELECT COUNT(*) FROM alunos")->fetchColumn();
$total_orientadores = $pdo->query("SELECT COUNT(*) FROM orientadores")->fetchColumn();
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

// Buscar projetos recentes
$stmt = $pdo->query("SELECT p.*, o.nome as orientador_nome, a.nome_area,
                     s.descricao as status_descricao
                     FROM projetos p 
                     LEFT JOIN orientadores o ON p.id_orientador = o.id_orientador
                     LEFT JOIN areas a ON p.id_area = a.id_area
                     LEFT JOIN status s ON p.id_status = s.id_status
                     ORDER BY p.data_criacao DESC 
                     LIMIT 5");
$projetos_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-cards">
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Total de Projetos</h3>
                <div class="card-value"><?php echo $total_projetos; ?></div>
            </div>
            <div class="card-icon">📚</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Alunos</h3>
                <div class="card-value"><?php echo $total_alunos; ?></div>
            </div>
            <div class="card-icon">🎓</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Orientadores</h3>
                <div class="card-value"><?php echo $total_orientadores; ?></div>
            </div>
            <div class="card-icon">👨‍🏫</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Usuários</h3>
                <div class="card-value"><?php echo $total_usuarios; ?></div>
            </div>
            <div class="card-icon">👥</div>
        </div>
    </div>
</div>

<h2 style="margin-top: 30px; margin-bottom: 20px; color: #2c3e50;">Projetos Recentes</h2>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Orientador</th>
                <th>Área</th>
                <th>Status</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($projetos_recentes) > 0): ?>
                <?php foreach ($projetos_recentes as $projeto): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($projeto['titulo']); ?></strong></td>
                    <td><?php echo htmlspecialchars($projeto['orientador_nome'] ?? 'Não definido'); ?></td>
                    <td><?php echo htmlspecialchars($projeto['nome_area'] ?? 'Não definida'); ?></td>
                    <td>
                        <?php
                        $status = $projeto['status_descricao'] ?? 'Indefinido';
                        $badge_class = '';
                        switch (strtolower($status)) {
                            case 'planejamento':
                                $badge_class = 'badge-info';
                                break;
                            case 'em andamento':
                                $badge_class = 'badge-warning';
                                break;
                            case 'concluído':
                            case 'concluido':
                                $badge_class = 'badge-success';
                                break;
                            case 'cancelado':
                                $badge_class = 'badge-danger';
                                break;
                            default:
                                $badge_class = 'badge-info';
                        }
                        ?>
                        <span class="badge <?php echo $badge_class; ?>">
                            <?php echo htmlspecialchars($status); ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($projeto['data_criacao'])); ?></td>
                    <td>
                        <a href="?page=ver-projeto&id=<?php echo $projeto['id_projeto']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">Ver</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #7f8c8d;">
                        Nenhum projeto cadastrado ainda. 
                        <?php if ($tipo_usuario === 'admin' || $tipo_usuario === 'orientador'): ?>
                            <a href="?page=novo-projeto">Clique aqui para criar o primeiro!</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
