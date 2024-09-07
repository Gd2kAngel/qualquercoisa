<?php
session_start();
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="../assets/css/signin.css" />
    <title>Sign In</title>
    <style>
        .fa-eye, .fa-eye-slash {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .label-float {
            position: relative;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php
        if (isset($_POST["login"])) {
            $username = $_POST["usuario"];
            $password = $_POST["senha"];

            require_once(__DIR__ . '../../assets/api/Context.php');
            require_once(__DIR__ . '../../assets/api/Model/User.php');

            $context = new Context();
            $conn = $context->getConnection();

            if (!$conn) {
                die("Erro na conexão com o banco de dados: " . mysqli_connect_error());
            }

            $user = new User($conn);

            if ($user->loadByUsername($username)) {
                if (password_verify($password, $user->password)) {
                    session_start();
                    $_SESSION["user"] = $user->id;
                    header("Location: ../index.php");
                    die();
                } else {
                    echo "<div class='alert alert-danger'>Password does not match</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Username does not match</div>";
            }
        }
        ?>

        <form action="signin.php" method="post">
            <div class="card">
                <h1>Entrar</h1>

                <div id="msgError"></div>

                <div class="label-float">
                    <input type="text" id="usuario" name="usuario" placeholder="" required />
                    <label id="userLabel" for="usuario">Username</label>
                </div>

                <div class="label-float">
                    <input type="password" id="senha" name="senha" placeholder="" required />
                    <label id="senhaLabel" for="senha">Password</label>
                    <i class="fa fa-eye" aria-hidden="true" onclick="togglePassword()"></i>
                </div>

                <div class="justify-center">
                    <input type="submit" value="Sign In" class="btn btn-primary" name="login">
                </div>

                <div class="justify-center">
                    <hr />
                </div>
                <div>
                    <p>
                        Not registered yet?
                        <a href="./signup.php">
                            Sign Up
                        </a>
                    </p>
                </div>
                </p>
            </div>
    </div>
    </form>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById("senha");
            const eyeIcon = document.querySelector(".fa-eye, .fa-eye-slash");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }
    </script>
</body>

</html>
