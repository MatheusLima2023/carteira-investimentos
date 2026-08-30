<?php
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $ticker = $_POST['ticker_nome'];
    $valor = $_POST['valor_aportado'];
    $meta = $_POST['meta_valor'];
    $categoria_id = $_POST['categoria_id'];
    $instituicao_id = $_POST['instituicao_id'];

    $stmt = $conn->prepare("UPDATE ativos SET ticker_nome = ?, valor_aportado = ?, meta_valor = ?, categoria_id = ?, instituicao_id = ? WHERE id = ?");
    $stmt->bind_param("sddiii", $ticker, $valor, $meta, $categoria_id, $instituicao_id, $id);

    if ($stmt->execute()) {
        header("Location: index.php?status=atualizado");
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>