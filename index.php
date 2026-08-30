<?php
include 'conexao.php';

// Filtros da busca
$filtro_busca = $_GET['busca'] ?? '';
$filtro_categoria = $_GET['filtro_categoria'] ?? '';
$filtro_instituicao = $_GET['filtro_instituicao'] ?? '';

// 1. Consultas para os Cards de Resumo
$total_geral = $conn->query("SELECT SUM(valor_aportado) AS total FROM ativos")->fetch_assoc()['total'] ?? 0;
$total_meta = $conn->query("SELECT SUM(meta_valor) AS total FROM ativos")->fetch_assoc()['total'] ?? 0;
$total_ativos = $conn->query("SELECT COUNT(id) AS qtd FROM ativos")->fetch_assoc()['qtd'] ?? 0;

// 2. Consulta para o Gráfico
$sql_grafico = "SELECT c.nome AS categoria, SUM(a.valor_aportado) AS total 
                FROM ativos a
                JOIN categorias c ON a.categoria_id = c.id
                GROUP BY c.id, c.nome";
$res_grafico = $conn->query($sql_grafico);

$labels_grafico = [];
$dados_grafico = [];

if ($res_grafico && $res_grafico->num_rows > 0) {
    while ($row_g = $res_grafico->fetch_assoc()) {
        $labels_grafico[] = $row_g['categoria'];
        $dados_grafico[] = $row_g['total'];
    }
}

// 3. Busca Categorias e Instituições para os menus
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nome ASC");
$lista_categorias = [];
while ($cat = $categorias->fetch_assoc()) {
    $lista_categorias[] = $cat;
}

$instituicoes = $conn->query("SELECT * FROM instituicoes ORDER BY nome ASC");
$lista_instituicoes = [];
while ($inst = $instituicoes->fetch_assoc()) {
    $lista_instituicoes[] = $inst;
}

// 4. Query Dinâmica de Ativos com Filtros
$where = ["1=1"];
if (!empty($filtro_busca)) {
    $busca_clean = $conn->real_escape_string($filtro_busca);
    $where[] = "a.ticker_nome LIKE '%$busca_clean%'";
}
if (!empty($filtro_categoria)) {
    $where[] = "a.categoria_id = " . intval($filtro_categoria);
}
if (!empty($filtro_instituicao)) {
    $where[] = "a.instituicao_id = " . intval($filtro_instituicao);
}

$sql_where = implode(" AND ", $where);
$sql = "SELECT a.id, a.ticker_nome, a.valor_aportado, a.meta_valor, a.categoria_id, a.instituicao_id, a.data_aporte, 
               c.nome AS categoria, i.nome AS instituicao 
        FROM ativos a
        JOIN categorias c ON a.categoria_id = c.id
        JOIN instituicoes i ON a.instituicao_id = i.id
        WHERE $sql_where
        ORDER BY a.id DESC";
$ativos = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minha Carteira de Investimentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    
    <!-- Cabeçalho com Botões das Modais -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary m-0">📊 Painel da Carteira de Investimentos</h2>
        <div>
            <button class="btn btn-outline-success me-2" data-bs-toggle="modal" data-bs-target="#modalCategoria">
                + Nova Categoria
            </button>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalInstituicao">
                + Nova Instituição
            </button>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h6 class="card-title text-white-50">Patrimônio Total Aportado</h6>
                    <h3 class="fw-bold m-0">R$ <?= number_format($total_geral, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h6 class="card-title text-white-50">Meta Total da Carteira</h6>
                    <h3 class="fw-bold m-0">R$ <?= number_format($total_meta, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark text-white shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h6 class="card-title text-white-50">Total de Operações</h6>
                    <h3 class="fw-bold m-0"><?= $total_ativos ?> cadastros</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Alocação por Categoria -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title m-0 text-secondary">Alocação por Categoria</h5>
        </div>
        <div class="card-body d-flex justify-content-center align-items-center" style="max-height: 280px;">
            <?php if (!empty($dados_grafico)): ?>
                <canvas id="graficoCategorias"></canvas>
            <?php else: ?>
                <p class="text-muted m-0 my-3">Cadastre investimentos para visualizar o gráfico.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formulário de Novo Aporte -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Novo Aporte</h5>
        </div>
        <div class="card-body">
            <form action="salvar_ativo.php" method="POST" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Ticker / Ativo</label>
                    <input type="text" name="ticker_nome" class="form-control" placeholder="Ex: GMAT3" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Valor Aportado (R$)</label>
                    <input type="number" step="0.01" name="valor_aportado" class="form-control" placeholder="100.00" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Meta do Ativo (R$)</label>
                    <input type="number" step="0.01" name="meta_valor" class="form-control" placeholder="200.00" value="0.00">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Categoria</label>
                    <select name="categoria_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach($lista_categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= $cat['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Instituição</label>
                    <select name="instituicao_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach($lista_instituicoes as $inst): ?>
                            <option value="<?= $inst['id'] ?>"><?= $inst['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success">Salvar Investimento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Área da Tabela e Filtros -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Meus Ativos</h5>
        </div>
        
        <!-- Barra de Filtros -->
        <div class="card-body bg-light border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="busca" class="form-control" placeholder="Filtrar por nome/ticker..." value="<?= htmlspecialchars($filtro_busca) ?>">
                </div>
                <div class="col-md-3">
                    <select name="filtro_categoria" class="form-select">
                        <option value="">Todas as Categorias</option>
                        <?php foreach($lista_categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $filtro_categoria == $cat['id'] ? 'selected' : '' ?>><?= $cat['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="filtro_instituicao" class="form-select">
                        <option value="">Todas as Instituições</option>
                        <?php foreach($lista_instituicoes as $inst): ?>
                            <option value="<?= $inst['id'] ?>" <?= $filtro_instituicao == $inst['id'] ? 'selected' : '' ?>><?= $inst['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                    <a href="index.php" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ativo</th>
                        <th>Aportado / Meta</th>
                        <th>Progresso da Meta</th>
                        <th>Categoria</th>
                        <th>Instituição</th>
                        <th>Data</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($ativos && $ativos->num_rows > 0): ?>
                        <?php while($row = $ativos->fetch_assoc()): 
                            $valor = $row['valor_aportado'];
                            $meta = $row['meta_valor'];
                            $pct = ($meta > 0) ? min(100, round(($valor / $meta) * 100, 1)) : 0;
                        ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><strong><?= htmlspecialchars($row['ticker_nome']) ?></strong></td>
                                <td>
                                    R$ <?= number_format($valor, 2, ',', '.') ?>
                                    <br><small class="text-muted">Meta: R$ <?= number_format($meta, 2, ',', '.') ?></small>
                                </td>
                                <td style="width: 180px;">
                                    <div class="progress" style="height: 18px;">
                                        <div class="progress-bar <?= $pct >= 100 ? 'bg-success' : 'bg-info text-dark' ?>" 
                                             role="progressbar" 
                                             style="width: <?= $pct ?>%;">
                                            <?= $pct ?>%
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-info text-dark"><?= $row['categoria'] ?></span></td>
                                <td><?= $row['instituicao'] ?></td>
                                <td><?= date('d/m/Y', strtotime($row['data_aporte'])) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-warning me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditar"
                                            data-id="<?= $row['id'] ?>"
                                            data-ticker="<?= htmlspecialchars($row['ticker_nome']) ?>"
                                            data-valor="<?= $row['valor_aportado'] ?>"
                                            data-meta="<?= $row['meta_valor'] ?>"
                                            data-categoria="<?= $row['categoria_id'] ?>"
                                            data-instituicao="<?= $row['instituicao_id'] ?>">
                                        Editar
                                    </button>
                                    <a href="deletar_ativo.php?id=<?= $row['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Excluir este ativo?')">
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-3 text-muted">Nenhum ativo encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Editar Ativo -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="editar_ativo.php" method="POST">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark">Editar Aporte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    
                    <div class="mb-3">
                        <label class="form-label">Ticker / Ativo</label>
                        <input type="text" name="ticker_nome" id="edit-ticker" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Valor Aportado (R$)</label>
                        <input type="number" step="0.01" name="valor_aportado" id="edit-valor" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta do Ativo (R$)</label>
                        <input type="number" step="0.01" name="meta_valor" id="edit-meta" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoria</label>
                        <select name="categoria_id" id="edit-categoria" class="form-select" required>
                            <?php foreach($lista_categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['nome'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instituição</label>
                        <select name="instituicao_id" id="edit-instituicao" class="form-select" required>
                            <?php foreach($lista_instituicoes as $inst): ?>
                                <option value="<?= $inst['id'] ?>"><?= $inst['nome'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nova Categoria -->
<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="salvar_categoria.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Cadastrar Nova Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Nome da Categoria</label>
                    <input type="text" name="nome_categoria" class="form-control" placeholder="Ex: Criptomoedas, BDRs" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar Categoria</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nova Instituição -->
<div class="modal fade" id="modalInstituicao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="salvar_instituicao.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Cadastrar Nova Corretora / Banco</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Nome da Instituição</label>
                    <input type="text" name="nome_instituicao" class="form-control" placeholder="Ex: C6 Bank, XP, Binance" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Instituição</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts Chart.js & Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Script do Gráfico
<?php if (!empty($dados_grafico)): ?>
    const ctx = document.getElementById('graficoCategorias').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels_grafico) ?>,
            datasets: [{
                data: <?= json_encode($dados_grafico) ?>,
                backgroundColor: [
                    '#0d6efd', '#198754', '#ffc107', '#0dcaf0', 
                    '#6f42c1', '#fd7e14', '#d63384', '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
<?php endif; ?>

// Script para preencher o Modal de Edição
const modalEditar = document.getElementById('modalEditar');
modalEditar.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    
    document.getElementById('edit-id').value = button.getAttribute('data-id');
    document.getElementById('edit-ticker').value = button.getAttribute('data-ticker');
    document.getElementById('edit-valor').value = button.getAttribute('data-valor');
    document.getElementById('edit-meta').value = button.getAttribute('data-meta');
    document.getElementById('edit-categoria').value = button.getAttribute('data-categoria');
    document.getElementById('edit-instituicao').value = button.getAttribute('data-instituicao');
});
</script>

</body>
</html>