<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION["user"])) {
    header("Location: ../index.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/signup.css" />
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <?php
        if (isset($_POST["submit"])) {
            $fullName = $_POST["nome"];
            $email = $_POST["email"];
            $username = $_POST["usuario"];
            $password = $_POST["senha"];
            $passwordRepeat = $_POST["confirmSenha"];

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $errors = array();

            if (empty($fullName) || empty($email) || empty($username) || empty($password) || empty($passwordRepeat)) {
                array_push($errors, "All fields are required");
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($errors, "Email is not valid");
            }
            if (strlen($password) < 5) {
                array_push($errors, "Password must be at least 5 characters long");
            }
            if ($password !== $passwordRepeat) {
                array_push($errors, "Password does not match");
            }

            require_once(__DIR__ . '../../assets/api/Context.php');
            $context = new Context();
            $conn = $context->getConnection();

            // Verifica se a conexão foi estabelecida com sucesso
            if (!$conn) {
                die("Connection failed: " . $this->$conn->connect_error);
            }

            // Usando consultas preparadas para prevenir injeção SQL
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $rowCount = $result->num_rows;

            if ($rowCount > 0) {
                array_push($errors, "Email already exists!");
            }

            $sql = "SELECT * FROM users WHERE username = ?";
            $stmt->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $rowCount = $result->num_rows;

            if ($rowCount > 0) {
                array_push($errors, "Username already exists!");
            }

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    echo "<div class='alert alert-danger'>$error</div>";
                }
            } else {
                $sql = "INSERT INTO users (full_name, email, username, password) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $fullName, $email, $username, $passwordHash);

                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>You are registered successfully.</div>";
                    header("Location: ../index.php");
                    exit;
                } else {
                    echo "Error: " . $stmt->error;
                }

                $stmt->close();
            }

            $conn->close();
        }
        ?>
        <form action="./signup.php" method="POST">
            <fieldset>
                <div class="card">
                    <h1>Sign Up</h1>

                    <div id="msgError"></div>
                    <div id="msgSuccess"></div>

                    <div class="label-float">
                        <input type="text" name="nome" id="nome" placeholder=" " required />
                        <label id="labelNome" for="nome">Name</label>
                    </div>

                    <div class="label-float">
                        <input type="text" name="usuario" id="usuario" placeholder=" " required />
                        <label id="labelUsuario" for="usuario">Username</label>
                    </div>

                    <div class="label-float">
                        <input type="text" name="email" id="email" placeholder=" " required />
                        <label id="labelUsuario" for="usuario">Email</label>
                    </div>

                    <div class="label-float">
                        <input type="password" name="senha" id="senha" placeholder=" " required />
                        <label id="labelSenha" for="senha">Password</label>
                        <i id="verSenha" class="fa fa-eye" aria-hidden="true"></i>
                    </div>

                    <div class="label-float">
                        <input type="password" name="confirmSenha" id="confirmSenha" placeholder=" " required />
                        <label id="labelConfirmSenha" for="confirmSenha">Confirm Password</label>
                        <i id="verConfirmSenha" class="fa fa-eye" aria-hidden="true"></i>
                    </div>
                    <div class="justify-center">
                        <br>
                        <input type="submit" class="btn btn-primary" value="Register" name="submit">
                    </div>
                    <div>
                        <br>
                        <hr>
                        <p class="justify-center">Already Registered? <a href="./signin.php"> Login Here</a></p>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>

</body>

</html>