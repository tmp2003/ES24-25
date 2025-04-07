<?php
session_start();
require_once "config.php"; // Conexão com a base de dados

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $escola = trim($_POST["escola"]);

    // Validação dos campos
if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($escola)) {
    $error = "Preencha todos os campos!";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Formato de email inválido!";
} elseif (!preg_match('/@ipcb\.pt$|@ipcbcampus\.pt$/', $email)) {
    $error = "O email deve ser do domínio @ipcb.pt ou @ipcbcampus.pt!";
} elseif ($password !== $confirm_password) {
    $error = "As senhas não coincidem!";
} else {
    // Verificar se o email já existe
    $sql = "SELECT id FROM userdata WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
        
    if ($stmt->fetch()) {
        $error = "Este email já está registado!";
    } else {
        // Inserir novo utilizador
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO userdata (username, email, password,escola) VALUES (:username, :email, :password,:escola)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(":escola", $escola, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            
            header("Location: login.php"); // Redirecionar após registo bem-sucedido
            exit();
        } else {
            $error = "Erro ao registrar. Tente novamente!";
        }
    }
}

}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
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
                                <img src="./img/login.jpg" alt="register form" class="img-fluid" style="border-radius: 1rem 0 0 1rem;" />
                            </div>
                            <div class="col-md-4 col-lg-4 d-flex align-items-center">
                                <div class="card-body p-5 text-black">
                                    <form method="POST">
                                        <div class="d-flex align-items-center mb-3 pb-1">
                                            <span class="h1 fw-bold mb-0">MyNotes</span>
                                        </div>
                                        <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Registe-se Aqui</h5>

                                        <?php if (isset($error)) { echo "<p class='text-danger'>" . htmlspecialchars($error) . "</p>"; } ?>

                                        <div class="form-outline mb-3">
                                            <input type="text" name="username" class="form-control form-control-lg" placeholder="Nome de Utilizador" required />
                                        </div>
                                        <div class="form-outline mb-3">
                                            <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required />
                                        </div>
                                        <div class="form-outline mb-3">
                                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required />
                                        </div>
                                        <div class="form-outline mb-3">
                                            <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Confirme a Password" required />
                                        </div>
                                        <div class="form-outline mb-3">
                                        <select class="form-select" aria-label="Default select example" name="escola">
                                            <option selected>Escola</option>
                                            <option value="ESART">ESART</option>
                                            <option value="ESA">ESA</option>
                                            <option value="ESE">ESE</option>
                                            <option value="EST">EST</option>
                                            <option value="ESGIN">ESGIN</option>
                                            <option value="ESALD">ESALD</option>
                                        </select>
                                        </div>
                                        <div class="pt-1 mb-3">
                                            <button class="btn btn-dark btn-lg btn-block" type="submit">Registrar</button>
                                        </div>
                                        <p class="mb-5 pb-lg-2" style="color: #393f81;">Já tem conta? <a href="login.php" style="color: #393f81;">Logue-se Aqui</a></p>
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