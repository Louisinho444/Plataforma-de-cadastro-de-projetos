<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login_user/login.php");
    exit;
}

include 'login_user/conexao.php';

$nome_usuario = $_SESSION['nome'];
$tipo_usuario = $_SESSION['tipo'];

$mensagem = '';
$tipo_mensagem = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $id_projeto = $_GET['delete'];
        
        // Delete related records first
        $pdo->prepare("DELETE FROM projeto_aluno WHERE id_projeto = ?")->execute([$id_projeto]);
        $pdo->prepare("DELETE FROM projetos WHERE id_projeto = ?")->execute([$id_projeto]);
        
        header("Location: index.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        $mensagem = "Erro ao excluir projeto: " . $e->getMessage();
        $tipo_mensagem = "error";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $titulo = $_POST['titulo'];
        $resumo = $_POST['resumo'];
        $orientador_nome = $_POST['orientador_nome'];
        $area_nome = $_POST['area_nome'];
        $id_status = $_POST['id_status'];
        $alunos_texto = $_POST['alunos_texto'];

        if ($_POST['action'] === 'edit_project') {
            // Update existing project
            $id_projeto = $_POST['id_projeto'];
            $sql = "UPDATE projetos SET titulo = :titulo, resumo = :resumo, 
                    orientador_nome = :orientador_nome, area_nome = :area_nome, 
                    id_status = :id_status, alunos_texto = :alunos_texto 
                    WHERE id_projeto = :id_projeto";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titulo' => $titulo,
                ':resumo' => $resumo,
                ':orientador_nome' => $orientador_nome,
                ':area_nome' => $area_nome,
                ':id_status' => $id_status,
                ':alunos_texto' => $alunos_texto,
                ':id_projeto' => $id_projeto
            ]);

            header("Location: index.php?updated=1");
            exit;
            
        } else {
            // Insert new project
            $sql = "INSERT INTO projetos (titulo, resumo, orientador_nome, area_nome, id_status, alunos_texto) 
                    VALUES (:titulo, :resumo, :orientador_nome, :area_nome, :id_status, :alunos_texto)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':titulo' => $titulo,
                ':resumo' => $resumo,
                ':orientador_nome' => $orientador_nome,
                ':area_nome' => $area_nome,
                ':id_status' => $id_status,
                ':alunos_texto' => $alunos_texto
            ]);

            header("Location: index.php?success=1");
            exit;
        }
        
    } catch (PDOException $e) {
        $mensagem = "Erro ao salvar projeto: " . $e->getMessage();
        $tipo_mensagem = "error";
    }
}

$filtro_status = isset($_GET['status']) ? $_GET['status'] : '';
$filtro_area = isset($_GET['area']) ? $_GET['area'] : '';
$filtro_orientador = isset($_GET['orientador']) ? $_GET['orientador'] : '';

$sql = "SELECT p.*, s.descricao as status_descricao
        FROM projetos p 
        LEFT JOIN status s ON p.id_status = s.id_status
        WHERE 1=1";

$params = [];

if ($filtro_status) {
    $sql .= " AND p.id_status = ?";
    $params[] = $filtro_status;
}

if ($filtro_area) {
    $sql .= " AND p.area_nome LIKE ?";
    $params[] = "%$filtro_area%";
}

if ($filtro_orientador) {
    $sql .= " AND p.orientador_nome LIKE ?";
    $params[] = "%$filtro_orientador%";
}

$sql .= " ORDER BY p.data_criacao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$orientadores_unicos = $pdo->query("SELECT DISTINCT orientador_nome FROM projetos WHERE orientador_nome IS NOT NULL AND orientador_nome != '' ORDER BY orientador_nome")->fetchAll(PDO::FETCH_COLUMN);
$areas_unicas = $pdo->query("SELECT DISTINCT area_nome FROM projetos WHERE area_nome IS NOT NULL AND area_nome != '' ORDER BY area_nome")->fetchAll(PDO::FETCH_COLUMN);
$status_list = $pdo->query("SELECT id_status, descricao FROM status ORDER BY id_status")->fetchAll(PDO::FETCH_ASSOC);

$mostrar_form = isset($_GET['add']) && $_GET['add'] === '1';
$editando = isset($_GET['edit']) && is_numeric($_GET['edit']);
$projeto_edit = null;

if ($editando) {
    $id_edit = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM projetos WHERE id_projeto = ?");
    $stmt->execute([$id_edit]);
    $projeto_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

$mostrar_relatorio = isset($_GET['report']) && $_GET['report'] === '1';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de Pesquisa</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>📚 Plataforma de Pesquisa</h1>
                <div class="user-section">
                    <span class="user-name">Olá, <?php echo htmlspecialchars($nome_usuario); ?></span>
                    <a href="login_user/logout.php" class="btn-logout">Sair</a>
                </div>
            </div>
        </header>

        <main class="main">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">✓ Projeto cadastrado com sucesso!</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success">✓ Projeto atualizado com sucesso!</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">✓ Projeto excluído com sucesso!</div>
            <?php endif; ?>

            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?>">
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>

            <?php if ($mostrar_relatorio): ?>
                <!-- Simple report view -->
                <div class="report-container">
                    <div class="report-header">
                        <h2>📊 Relatório de Projetos</h2>
                        <div>
                            <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
                            <a href="index.php" class="btn btn-secondary">Voltar</a>
                        </div>
                    </div>
                    
                    <div class="report-summary">
                        <div class="summary-card">
                            <h3><?php echo count($projetos); ?></h3>
                            <p>Total de Projetos</p>
                        </div>
                        <?php
                        $status_count = [];
                        foreach ($projetos as $p) {
                            $st = $p['status_descricao'] ?? 'Indefinido';
                            $status_count[$st] = ($status_count[$st] ?? 0) + 1;
                        }
                        foreach ($status_count as $st => $count):
                        ?>
                        <div class="summary-card">
                            <h3><?php echo $count; ?></h3>
                            <p><?php echo htmlspecialchars($st); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Orientador</th>
                                <th>Área</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projetos as $p): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($p['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($p['orientador_nome'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($p['area_nome'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($p['status_descricao'] ?? '-'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($p['data_criacao'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($mostrar_form || $editando): ?>
                <!-- Add/Edit project form -->
                <div class="form-container">
                    <h3><?php echo $editando ? '✏️ Editar Projeto' : '➕ Novo Projeto'; ?></h3>
                    <form method="POST" class="project-form">
                        <input type="hidden" name="action" value="<?php echo $editando ? 'edit_project' : 'add_project'; ?>">
                        <?php if ($editando): ?>
                            <input type="hidden" name="id_projeto" value="<?php echo $projeto_edit['id_projeto']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="titulo">Título do Projeto *</label>
                            <input type="text" id="titulo" name="titulo" required 
                                   value="<?php echo $editando ? htmlspecialchars($projeto_edit['titulo']) : ''; ?>"
                                   placeholder="Digite o título do projeto" maxlength="150">
                        </div>

                        <div class="form-group">
                            <label for="resumo">Resumo/Descrição *</label>
                            <textarea id="resumo" name="resumo" required rows="4" 
                                      placeholder="Descreva o projeto de pesquisa..."><?php echo $editando ? htmlspecialchars($projeto_edit['resumo']) : ''; ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="orientador_nome">Orientador *</label>
                                <!-- Changed from select to text input -->
                                <input type="text" id="orientador_nome" name="orientador_nome" required 
                                       value="<?php echo $editando ? htmlspecialchars($projeto_edit['orientador_nome']) : ''; ?>"
                                       placeholder="Digite o nome do orientador" maxlength="100">
                            </div>

                            <div class="form-group">
                                <label for="area_nome">Área de Pesquisa *</label>
                                <!-- Changed from select to text input -->
                                <input type="text" id="area_nome" name="area_nome" required 
                                       value="<?php echo $editando ? htmlspecialchars($projeto_edit['area_nome']) : ''; ?>"
                                       placeholder="Digite a área de pesquisa" maxlength="100">
                            </div>

                            <div class="form-group">
                                <label for="id_status">Status *</label>
                                <select id="id_status" name="id_status" required>
                                    <option value="">Selecione o status</option>
                                    <?php foreach ($status_list as $status): ?>
                                        <option value="<?php echo $status['id_status']; ?>"
                                                <?php echo ($editando && $projeto_edit['id_status'] == $status['id_status']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($status['descricao']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="alunos_texto">Alunos Participantes</label>
                            <!-- Changed from checkboxes to textarea -->
                            <textarea id="alunos_texto" name="alunos_texto" rows="3" 
                                      placeholder="Digite os nomes dos alunos participantes (separe por vírgula ou linha)"><?php echo $editando ? htmlspecialchars($projeto_edit['alunos_texto']) : ''; ?></textarea>
                            <small class="form-hint">Exemplo: João Silva, Maria Santos, Pedro Oliveira</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">💾 Salvar Projeto</button>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Projects list with filters -->
                <div class="actions-bar">
                    <h2>Projetos de Pesquisa</h2>
                    <div class="action-buttons">
                        <a href="?report=1" class="btn btn-info">📊 Relatório</a>
                        <a href="?add=1" class="btn btn-primary">➕ Adicionar Projeto</a>
                    </div>
                </div>

                <!-- Filter section -->
                <div class="filters-container">
                    <form method="GET" class="filters-form">
                        <div class="filter-group">
                            <label for="filter-status">Status:</label>
                            <select id="filter-status" name="status" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <?php foreach ($status_list as $status): ?>
                                    <option value="<?php echo $status['id_status']; ?>"
                                            <?php echo $filtro_status == $status['id_status'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($status['descricao']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter-area">Área:</label>
                            <!-- Changed filter to text input with datalist for suggestions -->
                            <input type="text" id="filter-area" name="area" 
                                   value="<?php echo htmlspecialchars($filtro_area); ?>"
                                   placeholder="Digite para filtrar"
                                   list="areas-list">
                            <datalist id="areas-list">
                                <?php foreach ($areas_unicas as $area): ?>
                                    <option value="<?php echo htmlspecialchars($area); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="filter-group">
                            <label for="filter-orientador">Orientador:</label>
                            <!-- Changed filter to text input with datalist for suggestions -->
                            <input type="text" id="filter-orientador" name="orientador" 
                                   value="<?php echo htmlspecialchars($filtro_orientador); ?>"
                                   placeholder="Digite para filtrar"
                                   list="orientadores-list">
                            <datalist id="orientadores-list">
                                <?php foreach ($orientadores_unicos as $orientador): ?>
                                    <option value="<?php echo htmlspecialchars($orientador); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <!-- Added search button since filters don't auto-submit anymore -->
                        <button type="submit" class="btn btn-primary btn-small">🔍 Filtrar</button>

                        <?php if ($filtro_status || $filtro_area || $filtro_orientador): ?>
                            <a href="index.php" class="btn btn-secondary btn-small">✕ Limpar Filtros</a>
                        <?php endif; ?>
                    </form>
                </div>

                <?php if (count($projetos) > 0): ?>
                    <div class="projects-grid">
                        <?php foreach ($projetos as $projeto): ?>
                            <div class="project-card">
                                <div class="project-header">
                                    <h3><?php echo htmlspecialchars($projeto['titulo']); ?></h3>
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
                                
                                <p class="project-description">
                                    <?php echo htmlspecialchars(substr($projeto['resumo'], 0, 150)) . '...'; ?>
                                </p>
                                
                                <div class="project-info">
                                    <div class="info-row">
                                        <strong>👨‍🏫 Orientador:</strong> 
                                        <!-- Display text field instead of joined data -->
                                        <?php echo htmlspecialchars($projeto['orientador_nome'] ?? 'Não definido'); ?>
                                    </div>
                                    <div class="info-row">
                                        <strong>📚 Área:</strong> 
                                        <!-- Display text field instead of joined data -->
                                        <?php echo htmlspecialchars($projeto['area_nome'] ?? 'Não definida'); ?>
                                    </div>
                                    <!-- Added students display -->
                                    <?php if (!empty($projeto['alunos_texto'])): ?>
                                    <div class="info-row">
                                        <strong>👥 Alunos:</strong> 
                                        <?php echo htmlspecialchars($projeto['alunos_texto']); ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="info-row">
                                        <strong>📅 Criado em:</strong> 
                                        <?php echo date('d/m/Y', strtotime($projeto['data_criacao'])); ?>
                                    </div>
                                </div>

                                <!-- Add edit and delete buttons -->
                                <div class="project-actions">
                                    <a href="?edit=<?php echo $projeto['id_projeto']; ?>" class="btn btn-edit">✏️ Editar</a>
                                    <a href="?delete=<?php echo $projeto['id_projeto']; ?>" 
                                       class="btn btn-delete"
                                       onclick="return confirm('Tem certeza que deseja excluir este projeto?')">🗑️ Excluir</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📂</div>
                        <h3>Nenhum projeto encontrado</h3>
                        <p><?php echo ($filtro_status || $filtro_area || $filtro_orientador) ? 'Tente ajustar os filtros.' : 'Comece adicionando seu primeiro projeto de pesquisa.'; ?></p>
                        <a href="?add=1" class="btn btn-primary">➕ Adicionar Primeiro Projeto</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
