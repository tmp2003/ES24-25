<?php
session_start();
require_once "config.php";


if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($_GET['action'] == "approve") {
        $sql = "UPDATE userdata SET aprovado = 1 WHERE id = :id";
    } elseif ($_GET['action'] == "delete") {
        $sql = "DELETE FROM userdata WHERE id = :id";
    } else {
        die("Ação inválida!");
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        header("Location: index.php?success=1");
        exit();
    } else {
        header("Location: index.php?error=1");
        exit();
    }
} else {
    die("Parâmetros inválidos!");
}
