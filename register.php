<?php
session_start();
require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $affiliation = trim($_POST["affiliation"]);

    $role = $_POST["role"] ?? "author";
    $access_code = trim($_POST["access_code"] ?? "");

    // Проверка кодов доступа
    if ($role == "reviewer" && $access_code != "NII2026REVIEW") {

        $message = "Invalid reviewer access code.";

    }
    elseif ($role == "editor" && $access_code != "NII2026EDIT") {

        $message = "Invalid editor access code.";

    }
    else {

        // Проверяем, существует ли email
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "This email is already registered.";

        }
        else {

            // Создаем пользователя
            $stmt = $conn->prepare("
                INSERT INTO users
                (fullname,email,password,affiliation,role)
                VALUES (?,?,?,?,?)
            ");

            $stmt->bind_param(
                "sssss",
                $fullname,
                $email,
                $password,
                $affiliation,
                $role
            );

            if ($stmt->execute()) {

                $_SESSION["user_id"] = $conn->insert_id;
                $_SESSION["fullname"] = $fullname;
                $_SESSION["email"] = $email;
                $_SESSION["role"] = $role;

                switch ($role) {

                    case "editor":
                        header("Location: editor-dashboard.php");
                        break;

                    case "reviewer":
                        header("Location: reviewer-dashboard.php");
                        break;

                    default:
                        header("Location: dashboard.php");
                        break;
                }

                exit();

            }
            else {

                $message = "Registration failed.";

            }

        }

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>Register</title>

<style>

body{

font-family:Arial;
background:#f5f5f5;

}

.container{

width:450px;
margin:60px auto;
background:white;
padding:30px;
border-radius:10px;

}

input{

width:100%;
padding:12px;
margin-top:10px;
margin-bottom:20px;

}

button{

padding:12px 25px;
background:#003366;
color:white;
border:none;
cursor:pointer;

}

.error{

color:red;

}

</style>

</head>

<body>

<div class="container">

<h2>Create Account</h2>

<?php echo "<p class='error'>$message</p>"; ?>
<form method="POST">

<input
type="text"
name="fullname"
placeholder="Full Name"
required>

<input
type="email"
name="email"
placeholder="Email"
required>

<input
type="password"
name="password"
placeholder="Password"
required>

<input
type="text"
name="affiliation"
placeholder="Affiliation">

<label>Register as</label>

<select
name="role"
id="role"
onchange="checkRole()">

    <option value="author">Author</option>
    <option value="reviewer">Reviewer</option>
    <option value="editor">Editor</option>

</select>

<div id="accessBox" style="display:none; margin-top:15px;">

<input
type="password"
name="access_code"
placeholder="Access code">

</div>

<button type="submit">

Register

</button>

</form>

<script>

function checkRole(){

    let role = document.getElementById("role").value;

    if(role == "reviewer" || role == "editor"){

        document.getElementById("accessBox").style.display = "block";

    }else{

        document.getElementById("accessBox").style.display = "none";

    }

}

// Проверяем сразу при загрузке страницы
checkRole();

</script>