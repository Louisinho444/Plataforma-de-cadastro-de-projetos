<?php
include __DIR__ . '/../login_user/conexao.php';

$mensagem = '';
$tipo_mensagem = '';

// Buscar orientadores e áreas para os selects
$orientadores = $pdo->query("SELECT id_orientador, nome FROM orientadores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$areas = $pdo->query("SELECT id_area, nome_area FROM areas ORDER BY nome_area")->fetchAll(PDO::FETCH_ASSOC);
$status_list = $pdo->query("SELECT id_status, descricao FROM status ORDER BY id_status")->fetchAll(PDO::FETCH_ASSOC);
$alunos = $pdo->query("SELECT id_aluno, nome FROM alunos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $titulo = $_POST['titulo'];
        $resumo = $_POST['resumo'];
        $id_orientador = $_POST['id_orientador'];
        $id_area = $_POST['id_area'];
        $id_status = $_POST['id_status'];
        $alunos_selecionados = isset($_POST['alunos']) ? $_POST['alunos'] : [];

        // Inserir projeto
        $sql = "INSERT INTO projetos (titulo, resumo, id_orientador, id_area, id_status) 
                VALUES (:titulo, :resumo, :id_orientador, :id_area, :id_status)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':resumo' => $resumo,
            ':id_orientador' => $id_orientador,
            ':id_area' => $id_area,
            ':id_status' => $id_status
        ]);

        $id_projeto = $pdo->lastInsertId();

        // Associar alunos ao projeto
        if (!empty($alunos_selecionados)) {
            $sql_aluno = "INSERT INTO projeto_aluno (id_projeto, id_aluno) VALUES (:id_projeto, :id_aluno)";
            $stmt_aluno = $pdo->prepare($sql_aluno);
            
            foreach ($alunos_selecionados as $id_aluno) {
                $stmt_aluno->execute([
                    ':id_projeto' => $id_projeto,
                    ':id_aluno' => $id_aluno
                ]);
            }
        }

        $mensagem = "Projeto cadastrado com sucesso!";
        $tipo_mensagem = "success";
        $_POST = [];
        
    } catch (PDOException $e) {
        $mensagem = "Erro ao cadastrar projeto: " . $e->getMessage();
        $tipo_mensagem = "error";
    }
}
?>

<?php if ($mensagem): ?>
<div class="alert alert-<?php echo $tipo_mensagem; ?>">
    <?php echo htmlspecialchars($mensagem); ?>
</div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h2 style="color: #2c3e50; margin: 0;">➕ Novo Projeto de Pesquisa</h2>
    <a href="?page=cadastrar-aluno" class="btn btn-secondary">🎓 Cadastrar Aluno</a>
</div>

<form method="POST" class="form-projeto">
    <div class="form-group">
        <label for="titulo">Título do Projeto *</label>
        <input type="text" id="titulo" name="titulo" required 
               placeholder="Digite o título do projeto" maxlength="150">
    </div>

    <div class="form-group">
        <label for="resumo">Resumo/Descrição *</label>
        <textarea id="resumo" name="resumo" required rows="6" 
                  placeholder="Descreva o projeto de pesquisa..."></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="id_orientador">Orientador *</label>
            <select id="id_orientador" name="id_orientador" required>
                <option value="">Selecione um orientador</option>
                <?php foreach ($orientadores as $orientador): ?>
                    <option value="<?php echo $orientador['id_orientador']; ?>">
                        <?php echo htmlspecialchars($orientador['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_area">Área de Pesquisa *</label>
            <select id="id_area" name="id_area" required>
                <option value="">Selecione uma área</option>
                <?php foreach ($areas as $area): ?>
                    <option value="<?php echo $area['id_area']; ?>">
                        <?php echo htmlspecialchars($area['nome_area']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_status">Status *</label>
            <select id="id_status" name="id_status" required>
                <option value="">Selecione o status</option>
                <?php foreach ($status_list as $status): ?>
                    <option value="<?php echo $status['id_status']; ?>">
                        <?php echo htmlspecialchars($status['descricao']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label>Alunos Participantes</label>
        <div class="checkbox-group">
            <?php foreach ($alunos as $aluno): ?>
                <label class="checkbox-label">
                    <input type="checkbox" name="alunos[]" value="<?php echo $aluno['id_aluno']; ?>">
                    <?php echo htmlspecialchars($aluno['nome']); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <small style="color: #7f8c8d;">Selecione os alunos que participarão do projeto</small>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-success">💾 Cadastrar Projeto</button>
        <a href="?page=projetos" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<style>
.form-projeto {
    max-width: 900px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 10px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
    margin-top: 10px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 5px;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    cursor: pointer;
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
