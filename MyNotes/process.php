<?php
session_start();
require_once "config.php";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    // Evita autodespromoção
    if ($action === 'demote' && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
        header("Location: index.php?error=nao_podes_despromover_te");
        exit();
    }

    switch ($action) {
        case "approve":
            $sql = "UPDATE userdata SET aprovado = 1 WHERE id = :id";
            break;
        case "delete":
            $sql = "DELETE FROM userdata WHERE id = :id";
            break;
        case "promote":
            $sql = "UPDATE userdata SET admin = 1 WHERE id = :id";
            break;
        case "demote":
            $sql = "UPDATE userdata SET admin = 0 WHERE id = :id";
            break;
        default:
            die("Ação inválida!");
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        header("Location: aprovar_contas.php?success=1");
        exit();
    } else {
        header("Location: aprovar_contas.php?error=1");
        exit();
    }
} else {
    die("Parâmetros inválidos!");
}

