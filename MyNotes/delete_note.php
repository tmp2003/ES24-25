<?php
session_start();
require_once 'config.php';

// Verificar se o utilizador está logado
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["note_id"])) {
    $note_id = $_POST["note_id"];
    $user_id = $_SESSION["user_id"];

    // Verificar se a nota pertence ao utilizador antes de apagar
    $stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$note_id, $user_id]);

    // Redirecionar de volta para a página de notas
    header("Location: apontamentos.php");
    exit();
} else {
    header("Location: apontamentos.php");
    exit();
}
?>
