<?php
include 'login_user/conexao.php';

// Estatísticas gerais
$total_projetos = $pdo->query("SELECT COUNT(*) FROM projetos")->fetchColumn();
$total_alunos = $pdo->query("SELECT COUNT(*) FROM alunos")->fetchColumn();
$total_orientadores = $pdo->query("SELECT COUNT(*) FROM orientadores")->fetchColumn();

// Projetos por status
$stmt_status = $pdo->query("SELECT s.descricao, COUNT(p.id_projeto) as total 
                            FROM status s 
                            LEFT JOIN projetos p ON s.id_status = p.id_status 
                            GROUP BY s.id_status, s.descricao
                            ORDER BY s.id_status");
$projetos_por_status = $stmt_status->fetchAll(PDO::FETCH_ASSOC);

// Projetos por área
$stmt_area = $pdo->query("SELECT a.nome_area, COUNT(p.id_projeto) as total 
                          FROM areas a 
                          LEFT JOIN projetos p ON a.id_area = p.id_area 
                          GROUP BY a.id_area, a.nome_area
                          HAVING total > 0
                          ORDER BY total DESC");
$projetos_por_area = $stmt_area->fetchAll(PDO::FETCH_ASSOC);

// Orientadores mais ativos
$stmt_orientadores = $pdo->query("SELECT o.nome, COUNT(p.id_projeto) as total_projetos
                                  FROM orientadores o
                                  LEFT JOIN projetos p ON o.id_orientador = p.id_orientador
                                  GROUP BY o.id_orientador, o.nome
                                  HAVING total_projetos > 0
                                  ORDER BY total_projetos DESC
                                  LIMIT 5");
$orientadores_ativos = $stmt_orientadores->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 style="color: #2c3e50; margin-bottom: 25px;">📊 Relatórios e Estatísticas</h2>

<!-- Resumo Geral -->
<div class="dashboard-cards" style="margin-bottom: 30px;">
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
                <h3 class="card-title">Total de Alunos</h3>
                <div class="card-value"><?php echo $total_alunos; ?></div>
            </div>
            <div class="card-icon">🎓</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Total de Orientadores</h3>
                <div class="card-value"><?php echo $total_orientadores; ?></div>
            </div>
            <div class="card-icon">👨‍🏫</div>
        </div>
    </div>
</div>

<!-- Projetos por Status -->
<div class="relatorio-section">
    <h3>Projetos por Status</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Quantidade</th>
                    <th>Percentual</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projetos_por_status as $status): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($status['descricao']); ?></strong></td>
                    <td><?php echo $status['total']; ?></td>
                    <td>
                        <?php 
                        $percentual = $total_projetos > 0 ? ($status['total'] / $total_projetos) * 100 : 0;
                        echo number_format($percentual, 1) . '%'; 
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Projetos por Área -->
<div class="relatorio-section">
    <h3>Projetos por Área de Pesquisa</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Área</th>
                    <th>Quantidade de Projetos</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($projetos_por_area) > 0): ?>
                    <?php foreach ($projetos_por_area as $area): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($area['nome_area']); ?></strong></td>
                        <td><?php echo $area['total']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="text-align: center; padding: 20px; color: #7f8c8d;">
                            Nenhum projeto cadastrado ainda.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Orientadores Mais Ativos -->
<div class="relatorio-section">
    <h3>Top 5 Orientadores Mais Ativos</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Orientador</th>
                    <th>Projetos Orientados</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orientadores_ativos) > 0): ?>
                    <?php foreach ($orientadores_ativos as $orientador): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($orientador['nome']); ?></strong></td>
                        <td><?php echo $orientador['total_projetos']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="text-align: center; padding: 20px; color: #7f8c8d;">
                            Nenhum orientador com projetos cadastrados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.relatorio-section {
    margin-bottom: 40px;
}

.relatorio-section h3 {
    color: #2c3e50;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}
</style>
