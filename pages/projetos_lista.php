<?php
include 'login_user/conexao.php';

// Filtros
$filtro_area = isset($_GET['area']) ? $_GET['area'] : '';
$filtro_status = isset($_GET['status']) ? $_GET['status'] : '';
$filtro_busca = isset($_GET['busca']) ? $_GET['busca'] : '';

// Montar query com filtros
$sql = "SELECT p.*, o.nome as orientador_nome, a.nome_area, s.descricao as status_descricao
        FROM projetos p 
        LEFT JOIN orientadores o ON p.id_orientador = o.id_orientador
        LEFT JOIN areas a ON p.id_area = a.id_area
        LEFT JOIN status s ON p.id_status = s.id_status
        WHERE 1=1";

$params = [];

if (!empty($filtro_area)) {
    $sql .= " AND p.id_area = :id_area";
    $params[':id_area'] = $filtro_area;
}

if (!empty($filtro_status)) {
    $sql .= " AND p.id_status = :id_status";
    $params[':id_status'] = $filtro_status;
}

if (!empty($filtro_busca)) {
    $sql .= " AND (p.titulo LIKE :busca OR p.resumo LIKE :busca)";
    $params[':busca'] = "%$filtro_busca%";
}

$sql .= " ORDER BY p.data_criacao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar áreas e status para filtros
$areas = $pdo->query("SELECT id_area, nome_area FROM areas ORDER BY nome_area")->fetchAll(PDO::FETCH_ASSOC);
$status_list = $pdo->query("SELECT id_status, descricao FROM status ORDER BY id_status")->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h2 style="color: #2c3e50; margin: 0;">📋 Lista de Projetos</h2>
    <?php if ($tipo_usuario === 'admin' || $tipo_usuario === 'orientador'): ?>
        <a href="?page=novo-projeto" class="btn btn-success">➕ Novo Projeto</a>
    <?php endif; ?>
</div>

<!-- Filtros -->
<div class="filters-container">
    <form method="GET" action="" class="filters-form">
        <input type="hidden" name="page" value="projetos">
        
        <div class="filter-group">
            <input type="text" name="busca" placeholder="🔍 Buscar por título ou resumo..." 
                   value="<?php echo htmlspecialchars($filtro_busca); ?>" class="filter-input">
        </div>
        
        <div class="filter-group">
            <select name="area" class="filter-select">
                <option value="">Todas as Áreas</option>
                <?php foreach ($areas as $area): ?>
                    <option value="<?php echo $area['id_area']; ?>" 
                            <?php echo ($filtro_area == $area['id_area']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($area['nome_area']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <select name="status" class="filter-select">
                <option value="">Todos os Status</option>
                <?php foreach ($status_list as $status): ?>
                    <option value="<?php echo $status['id_status']; ?>" 
                            <?php echo ($filtro_status == $status['id_status']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($status['descricao']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="?page=projetos" class="btn btn-secondary">Limpar</a>
    </form>
</div>

<!-- Contador de resultados -->
<p style="color: #7f8c8d; margin: 20px 0;">
    <strong><?php echo count($projetos); ?></strong> projeto(s) encontrado(s)
</p>

<!-- Lista de Projetos -->
<?php if (count($projetos) > 0): ?>
    <div class="projetos-grid">
        <?php foreach ($projetos as $projeto): ?>
            <div class="projeto-card">
                <div class="projeto-header">
                    <h3 class="projeto-titulo"><?php echo htmlspecialchars($projeto['titulo']); ?></h3>
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
                </div>
                
                <p class="projeto-resumo">
                    <?php echo htmlspecialchars(substr($projeto['resumo'], 0, 150)) . '...'; ?>
                </p>
                
                <div class="projeto-info">
                    <div class="info-item">
                        <strong>👨‍🏫 Orientador:</strong> 
                        <?php echo htmlspecialchars($projeto['orientador_nome'] ?? 'Não definido'); ?>
                    </div>
                    <div class="info-item">
                        <strong>📚 Área:</strong> 
                        <?php echo htmlspecialchars($projeto['nome_area'] ?? 'Não definida'); ?>
                    </div>
                    <div class="info-item">
                        <strong>📅 Cadastro:</strong> 
                        <?php echo date('d/m/Y', strtotime($projeto['data_criacao'])); ?>
                    </div>
                </div>
                
                <div class="projeto-actions">
                    <a href="?page=ver-projeto&id=<?php echo $projeto['id_projeto']; ?>" 
                       class="btn btn-primary btn-sm">👁️ Visualizar</a>
                    
                    <?php if ($tipo_usuario === 'admin' || $tipo_usuario === 'orientador'): ?>
                        <a href="?page=editar-projeto&id=<?php echo $projeto['id_projeto']; ?>" 
                           class="btn btn-warning btn-sm">✏️ Editar</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <p style="font-size: 48px; margin-bottom: 20px;">📂</p>
        <h3>Nenhum projeto encontrado</h3>
        <p>Não há projetos cadastrados com os filtros selecionados.</p>
        <?php if ($tipo_usuario === 'admin' || $tipo_usuario === 'orientador'): ?>
            <a href="?page=novo-projeto" class="btn btn-success" style="margin-top: 20px;">
                ➕ Cadastrar Primeiro Projeto
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<style>
.filters-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.filters-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-input,
.filter-select {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.projetos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.projeto-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.projeto-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.projeto-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    gap: 10px;
}

.projeto-titulo {
    color: #2c3e50;
    font-size: 18px;
    margin: 0;
    flex: 1;
}

.projeto-resumo {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 15px;
}

.projeto-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
}

.info-item {
    font-size: 13px;
    color: #555;
    margin-bottom: 8px;
}

.info-item:last-child {
    margin-bottom: 0;
}

.projeto-actions {
    display: flex;
    gap: 10px;
}

.btn-sm {
    padding: 8px 15px;
    font-size: 13px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #7f8c8d;
}

.empty-state h3 {
    color: #2c3e50;
    margin-bottom: 10px;
}
</style>
