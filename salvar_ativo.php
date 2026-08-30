<?php
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticker = $_POST['ticker_nome'];
    $valor = $_POST['valor_aportado'];
    $meta = $_POST['meta_valor'];
    $categoria_id = $_POST['categoria_id'];
    $instituicao_id = $_POST['instituicao_id'];

    $stmt = $conn->prepare("INSERT INTO ativos (ticker_nome, valor_aportado, meta_valor, categoria_id, instituicao_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sddii", $ticker, $valor, $meta, $categoria_id, $instituicao_id);

    if ($stmt->execute()) {
        header("Location: index.php?status=sucesso");
    } else {
        echo "Erro ao cadastrar: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>