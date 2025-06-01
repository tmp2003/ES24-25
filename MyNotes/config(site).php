<?php
// Para connectar ao Infinity Free plataforma para o site ficar online

$host = "sql112.infinityfree.com"; // Servidor do XAMPP
$dbname = "if0_38671332_notesdb";
$username = "if0_38671332"; // XAMPP usa 'root' por padrão
$password = "iuIroreLLk266Y"; // Sem senha por padrão

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
