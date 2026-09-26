<?php
require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST["fullname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $plain_password = $_POST["password"] ?? "";
    $affiliation = trim($_POST["affiliation"] ?? "");

    $role = $_POST["role"] ?? "author";
    $access_code = trim($_POST["access_code"] ?? "");
    $special_code = $_POST["special_code"] ?? "";

    if (!in_array($role, ["author", "reviewer", "editor"], true)) {
        $role = "author";
    }

    if ($fullname === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter your name and a valid email.";

    }
    elseif (strlen($plain_password) < 6) {

        $message = "Password must be at least 6 characters.";

    }
    // Staff roles need an access code from config
    elseif ($role === "reviewer" && !hash_equals($settings["reviewer_code"], $access_code)) {

        $message = "Invalid reviewer access code.";

    }
    elseif ($role === "editor" && !hash_equals($settings["editor_code"], $access_code)) {

        $message = "Invalid editor access code.";

    }
    elseif ($special_code !== "" && !is_free_access_code($special_code)) {

        sleep(1); // slows down password guessing
        $message = "Invalid special access password.";

    }
    else {

        // Check whether the email is already registered
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {

            $message = "This email is already registered.";

        }
        else {

            $password = password_hash($plain_password, PASSWORD_DEFAULT);

            $free_access = $special_code !== "" ? 1 : 0;

            $stmt = $conn->prepare("
                INSERT INTO users (fullname, email, password, affiliation, role, free_access)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param("sssssi", $fullname, $email, $password, $affiliation, $role, $free_access);

            if ($stmt->execute()) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $conn->insert_id;
                $_SESSION["fullname"] = $fullname;
                $_SESSION["email"] = $email;
                $_SESSION["role"] = $role;

                switch ($role) {
                    case "editor":
                        redirect("editor-dashboard.php");
                    case "reviewer":
                        redirect("reviewer-dashboard.php");
                    default:
                        redirect("dashboard.php");
                }

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

<?php if ($message !== ""): ?><p class="error"><?= e($message) ?></p><?php endif; ?>
<form method="POST">

<input
type="text"
name="fullname"
value="<?= e($_POST["fullname"] ?? "") ?>"
placeholder="Full Name"
required>

<input
type="email"
name="email"
value="<?= e($_POST["email"] ?? "") ?>"
placeholder="Email"
required>

<input
type="password"
name="password"
placeholder="Password (min. 6 characters)"
minlength="6"
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

<details style="margin-bottom:20px;">
<summary style="cursor:pointer;">I have a special access password</summary>
<input
type="password"
name="special_code"
placeholder="Special access password"
autocomplete="off">
</details>

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

<p style="margin-top:20px;">
Already have an account? <a href="signin.php">Sign in</a>
</p>

</div>

</body>

</html>
