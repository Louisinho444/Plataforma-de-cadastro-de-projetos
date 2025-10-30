<?php
$host = "localhost";
$user = "root";
$pass = "root";
$db = "plataforma_pesquisa";

try {
    $conexao = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
}
?>
