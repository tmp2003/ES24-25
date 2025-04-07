<?php
session_start();
require_once "config.php";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    // Safe point: Impede apagar ou despromover a si próprio
    if (
        isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id &&
        in_array($action, ['demote', 'delete'])
    ) {
        header("Location: aprovar_contas.php?error=nao_podes_" . $action . "_te");
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
            // Promover a Moderador
            $sql = "UPDATE userdata SET admin = 1 WHERE id = :id";
            break;

        case "promote2":
            // Promover a Admin
            $sql = "UPDATE userdata SET admin = 2 WHERE id = :id";
            break;

        case "demote":
            // Despromover: requer parâmetro "to" (0 ou 1)
            if (!isset($_GET['to']) || !in_array($_GET['to'], ['0', '1'])) {
                header("Location: aprovar_contas.php?error=parametro_invalido");
                exit();
            }
            $to = intval($_GET['to']);
            $sql = "UPDATE userdata SET admin = :to WHERE id = :id";
            break;

        default:
        header("Location: aprovar_contas.php?error=1");
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);

    if ($action === 'demote') {
        $stmt->bindParam(":to", $to, PDO::PARAM_INT);
    }

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
