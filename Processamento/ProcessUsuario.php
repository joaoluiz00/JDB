<?php
require_once "../Controller/ControllerUsuario.php";
$controller = new ControllerUsuario();
session_start();

if (isset($_POST['action']) && $_POST['action'] === 'set_profile_icon' && isset($_POST['id_icone'])) {
    $idUsuario = $_SESSION['id'];
    $idIcone = $_POST['id_icone'];

    $controllerUsuario = new ControllerUsuario();
    $result = $controllerUsuario->setProfileIcon($idUsuario, $idIcone);

    if ($result) {
        $_SESSION['success'] = "Foto de perfil atualizada com sucesso!";
    } else {
        $_SESSION['error'] = "Erro ao atualizar a foto de perfil.";
    }
    header("Location: ../View/Inventario.php");
    die();
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'register':
            if (isset($_POST['nome'], $_POST['senha'], $_POST['email'])) {
                $name = $_POST['nome'];
                $password = $_POST['senha'];
                $email = $_POST['email'];
                $coins = 0;
                $controller->createUser($name, $email, $password, $coins);
                header("Location: ../View/home.php");
                die();
            }
            break;

        case 'delete':
            if (isset($_POST['id'])) {
                $id = $_POST['id'];
                $controller->deleteUser($id);
                header("Location: ../View/GerenciarUsuario.php");
                die();
            }
            break;

        case 'update':
            if (isset($_POST['id'])) {
                $id = $_POST['id'];
                header("Location: ../View/AtualizarUsuario.php?id=$id");
                die();
            }
            break;

            case 'login':
                if (isset($_POST['email'], $_POST['senha'])) {
                    $email = $_POST['email'];
                    $password = $_POST['senha'];
            
                    // Chama o método loginUser
                    if ($controller->loginUser($email, $password)) {
                        header("Location: ../View/home.php");
                        die();
                    } else {
                        $_SESSION['res'] = "<span style='color: red;'>E-mail ou senha inválidos</span>";
                        header("Location: ../View/index.php");
                        die();
                    }
                } else {
                    $_SESSION['res'] = "<span style='color: red;'>Por favor, preencha todos os campos</span>";
                    header("Location: ../View/index.php");
                    die();
                }
            break;

        case 'logout':
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION = array();
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
            header("Location: ../View/");
            die();
            break;

        default:
            header("Location: error.php");
            die();
    }
    
} else {
    header("Location: error.php");
    die();

}