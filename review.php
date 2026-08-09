<?php
session_start();
require_once "config.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}


$user_id = $_SESSION["user_id"];

$sql = "SELECT role FROM users WHERE id=$user_id";
$result = $conn->query($sql);

$user = $result->fetch_assoc();


if ($user["role"] != "reviewer") {
    die("Access denied. Only reviewers can submit reviews.");
} 
if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}


if (!isset($_GET["id"])) {
    die("Article not found");
}

$id = $_GET["id"];

$sql = "SELECT * FROM submissions WHERE id=$id";

$result = $conn->query($sql);

$article = $result->fetch_assoc();
if ($_SERVER["REQUEST_METHOD"] == "POST") {


$comments = $_POST["comments"];

$status = $_POST["status"];
$reviewer = $_SESSION["username"] ?? "Reviewer";

$sql = "INSERT INTO reviews
(submission_id, reviewer_name, status, comments)

VALUES

('$id','$reviewer','$status','$comments')";


$conn->query($sql);


echo "Review submitted successfully";

}

?>
<h1>
<?= htmlspecialchars($article["title"]) ?>
</h1>


<p>
Journal:
<?= htmlspecialchars($article["journal"]) ?>
</p>


<a href="uploads/<?= urlencode($article["filename"]) ?>"
target="_blank">

Open Manuscript

</a>
<h2>
Submit Review
</h2>


<form method="POST">


<textarea 
name="comments"
placeholder="Write your comments..."
required>
</textarea>


<br>


<select name="status">

<option value="Pending">
Pending
</option>

<option value="Accepted">
Accept
</option>

<option value="Revision">
Revision Required
</option>

<option value="Rejected">
Reject
</option>

</select>


<br>


<button type="submit">

Send Review

</button>


</form>