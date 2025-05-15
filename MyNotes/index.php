<?php
session_start(); // Iniciar sessão

// Verificar se o utilizador está logado
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php"); // Se não estiver logado, redirecionar para login
    exit();
}

// Dados do utilizador logado
$username = $_SESSION["username"];
$isAdmin = !empty($_SESSION["admin"]) && $_SESSION["admin"] == 2; // Verifica se é admin
$escola = $_SESSION["escola"];

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MyNotes</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Agora contém os estilos da sidebar também -->
</head>

<body>

    <!-- Navbar Superior (Dark Mode) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">MyNotes</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav w-100 align-items-center">

                    <!-- Barra de Pesquisa Centralizada -->
                    <li class="nav-item mx-auto search-form" style="width: 50%;">
                        <form class="d-flex">
                            <input class="form-control me-2" type="search" placeholder="Procurar Notas"
                                aria-label="Search">
                            <button class="btn btn-outline-success" type="submit">Procurar</button>
                        </form>
                    </li>

                    <!-- Links de Navegação -->


                    <!-- Botão de Logout -->
                    <?php if (isset($_SESSION["user_id"])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Sair</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar (Navbar Vertical) -->
    <div class="sidebar" style="text-align: center;">
        <a href="perfil.php" style="border: none; background: none; padding: 0;">
            <img src="./img/avatar.png" class="rounded-circle" style="width: 80px; border: none;" alt="Avatar" />
        </a>


        <p></p>
        <a href="#">Página Principal</a>
        <a href="apontamentos.php">Meus Apontamentos</a>

        <?php if ($isAdmin): ?>
            <a href="aprovar_contas.php" class="text-warning fw-bold">Aprovações</a>
        <?php endif; ?>
    </div>

    <!-- Conteúdo Principal -->
    <div class="content">
        <h1>Bem-vindo ao MyNotes</h1>
        <p class="lead">Organize suas anotações de forma fácil e prática.</p>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>