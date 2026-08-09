<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"]);
    $journal = trim($_POST["journal"]);
    $abstract = trim($_POST["abstract"]);

    $filename = "";

    if (isset($_FILES["paper"]) && $_FILES["paper"]["error"] == 0) {

        $uploadDir = "uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES["paper"]["name"]);

        move_uploaded_file(
            $_FILES["paper"]["tmp_name"],
            $uploadDir . $filename
        );
    }

    $stmt = $conn->prepare("
        INSERT INTO submissions
        (user_id, title, journal, abstract, filename)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issss",
        $_SESSION["user_id"],
        $title,
        $journal,
        $abstract,
        $filename
    );

    if ($stmt->execute()) {
        $message = "Article submitted successfully!";
    } else {
        $message = "Database error.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<title>Submit Article</title>

<style>

body{
font-family:Arial;
background:#f5f5f5;
}

.container{
width:700px;
margin:40px auto;
background:white;
padding:30px;
border-radius:10px;
}

input,
textarea,
select{

width:100%;
padding:12px;
margin-top:10px;
margin-bottom:20px;

}

textarea{
height:180px;
resize:vertical;
}

button{

padding:12px 25px;
background:#002147;
color:white;
border:none;
cursor:pointer;

}

.success{

color:green;
font-weight:bold;

}

</style>

</head>

<body>

<div class="container">

<h2>Submit New Article</h2>

<?php if($message!=""){ ?>

<p class="success"><?php echo $message; ?></p>

<?php } ?>

<form method="POST" enctype="multipart/form-data">

<label>Article Title</label>

<input
type="text"
name="title"
required>

<label>Select Journal</label>

<select name="journal">

<option>Journal of Engineering</option>

<option>Journal of Medicine</option>

<option>Journal of Artificial Intelligence</option>

<option>Journal of Architecture</option>

<option>Journal of Economics</option>

</select>

<label>Abstract</label>

<textarea
name="abstract"
required></textarea>

<label>Upload PDF</label>

<input
type="file"
name="paper"
accept=".pdf,.doc,.docx"
required>

<button type="submit">

Submit Article

</button>

</form>

</div>

</body>

</html>