<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["user_id"])) {
    die("Acesso negado!");
}

$user_id = $_SESSION["user_id"];
$note_id = $_GET["note_id"] ?? null;
$file_name = $_GET["file"] ?? null;

if (!$note_id || !$file_name) {
    die("Parâmetros inválidos!");
}

$filePath = $file_name;

if (!file_exists($filePath)) {
    die("Ficheiro não encontrado!<br>Filename: " . htmlspecialchars($file_name) . "<br>Filepath: " . htmlspecialchars($filePath));
    
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
?>