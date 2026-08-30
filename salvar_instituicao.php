<?php
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome_instituicao']);

    if (!empty($nome)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO instituicoes (nome) VALUES (?)");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: index.php");
exit();
?>