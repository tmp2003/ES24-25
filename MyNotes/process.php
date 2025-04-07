<?php
session_start();
require_once "config.php";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    // Obter dados do utilizador alvo
    $stmtUser = $conn->prepare("SELECT * FROM userdata WHERE id = :id");
    $stmtUser->bindParam(":id", $id, PDO::PARAM_INT);
    $stmtUser->execute();
    $targetUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$targetUser) {
        header("Location: aprovar_contas.php?error=utilizador_nao_encontrado");
        exit();
    }

    $targetEmail = $targetUser['email'] ?? '';
    $isTargetIpcb = str_ends_with($targetEmail, '@ipcb.pt');
    $loggedAdminLevel = $_SESSION['admin'] ?? 0;

    // Safe point: não te podes apagar ou despromover
    if (
        isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id &&
        in_array($action, ['demote', 'delete'])
    ) {
        header("Location: aprovar_contas.php?error=nao_podes_" . $action . "_te");
        exit();
    }

    // Controlo de permissões por tipo de ação
    switch ($action) {
        case "approve":
            if ($loggedAdminLevel < 1) {
                header("Location: aprovar_contas.php?error=sem_permissao_aprovar");
                exit();
            }
            $sql = "UPDATE userdata SET aprovado = 1 WHERE id = :id";
            break;

        case "delete":
        case "promote":
        case "promote2":
        case "demote":
            if ($loggedAdminLevel != 2 || !$isTargetIpcb) {
                header("Location: aprovar_contas.php?error=sem_permissao_admin");
                exit();
            }

            if ($action == 'delete') {
                $sql = "DELETE FROM userdata WHERE id = :id";
                break;
            }

            if ($action == 'promote') {
                $sql = "UPDATE userdata SET admin = 1 WHERE id = :id";
                break;
            }

            if ($action == 'promote2') {
                $sql = "UPDATE userdata SET admin = 2 WHERE id = :id";
                break;
            }

            if (!isset($_GET['to']) || !in_array($_GET['to'], ['0', '1'])) {
                header("Location: aprovar_contas.php?error=parametro_invalido");
                exit();
            }

            $to = intval($_GET['to']);
            $sql = "UPDATE userdata SET admin = :to WHERE id = :id";
            break;

        default:
            die("Ação inválida!");
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
