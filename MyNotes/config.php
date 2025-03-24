<?php
$host = "localhost"; // Servidor do XAMPP
$dbname = "notesdb";
$username = "root"; // XAMPP usa 'root' por padrão
$password = ""; // Sem senha por padrão

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
