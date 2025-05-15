<?php
session_start();
require_once "config.php"; // Arquivo para conexão com a base de dados

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Verificar se o email é válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Formato de email inválido!";
    } elseif (!empty($email) && !empty($password)) {
        // Consulta SQL para verificar o utilizador
        $sql = "SELECT id, username, email, password, aprovado, admin, escola FROM userdata WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (!$user["aprovado"]) {
                $error = "A sua conta ainda não foi aprovada pelos administradores.";
            } elseif (password_verify($password, $user["password"])) {
                // Guardar sessão do utilizador
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["admin"] = $user["admin"];
                $_SESSION["escola"] = $user["escola"]; // Adicionar o ID da escola à sessão
                
                header("Location: index.php"); // Redirecionar após login
                exit();
            } else {
                $error = "Email ou palavra-passe incorretos!";
            }
        } else {
            $error = "Email ou palavra-passe incorretos!";
        }
    } else {
        $error = "Preencha todos os campos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body style="background-color: #F6F7FB;">
    <section class="vh-100">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col col-xl-10">
                    <div class="card" style="border-radius: 1rem;">
                        <div class="row g-0">
                            <div class="col-md-6 col-lg-5 d-none d-md-block">
                                <img src="./img/login.jpg" alt="login form" class="img-fluid" style="border-radius: 1rem 0 0 1rem;" />
                            </div>
                            <div class="col-md-4 col-lg-4 d-flex align-items-center">
                                <div class="card-body p-5 text-black">
                                    <form method="POST">
                                        <div class="d-flex align-items-center mb-3 pb-1">
                                            <span class="h1 fw-bold mb-0">MyNotes</span>
                                        </div>

                                        <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Faça aqui o seu Login</h5>

                                        <?php if (isset($error)) { echo "<p class='text-danger'>" . htmlspecialchars($error) . "</p>"; } ?>

                                        <div class="form-outline mb-3">
                                            <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required />
                                        </div>

                                        <div class="form-outline mb-3">
                                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required />
                                        </div>

                                        <div class="pt-1 mb-3">
                                            <button class="btn btn-dark btn-lg btn-block" type="submit">Login</button>
                                        </div>

                                        <a class="small text-muted" href="#">Esqueceu-se da password?</a>
                                        <p class="mb-5 pb-lg-2" style="color: #393f81;">Não tem conta? <a href="register.php" style="color: #393f81;">Registe-se Aqui</a></p>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>