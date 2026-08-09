<?php

session_start();
require_once "config.php";


// =====================================
// CHECK LOGIN
// =====================================

if (!isset($_SESSION["user_id"])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION["user_id"];


// =====================================
// CHECK EDITOR ROLE
// =====================================

$stmt = $conn->prepare("
    SELECT role, fullname
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user || $user["role"] !== "editor") {
    die("Access denied");
}


// =====================================
// ASSIGN REVIEWER
// =====================================

if (isset($_POST["assign"])) {

    $submission_id = (int)$_POST["submission_id"];
    $reviewer_id = (int)$_POST["reviewer_id"];

    $stmt = $conn->prepare("
        UPDATE submissions
        SET reviewer_id = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ii",
        $reviewer_id,
        $submission_id
    );

    $stmt->execute();

    header("Location: editor-dashboard.php");
    exit();
}


// =====================================
// PUBLISH ARTICLE
// =====================================

if (isset($_POST["publish"])) {

    $submission_id = (int)$_POST["submission_id"];

    $stmt = $conn->prepare("
        UPDATE submissions
        SET status = 'Published'
        WHERE id = ?
    ");

    $stmt->bind_param(
        "i",
        $submission_id
    );

    $stmt->execute();

    header("Location: editor-dashboard.php");
    exit();
}


// =====================================
// CREATE EDITORIAL CONTENT
// =====================================

if (isset($_POST["create_content"])) {

    $type = trim($_POST["type"] ?? "");
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $media_url = trim($_POST["media_url"] ?? "");
    $thumbnail_url = trim($_POST["thumbnail_url"] ?? "");
    $status = $_POST["status"] ?? "Draft";

    if ($type === "" || $title === "") {
        die("Title and content type are required.");
    }

    $published_at = null;

    if ($status === "Published") {
        $published_at = date("Y-m-d H:i:s");
    }

    $stmt = $conn->prepare("
        INSERT INTO editorial_content
        (
            type,
            title,
            description,
            content,
            media_url,
            thumbnail_url,
            status,
            author_id,
            published_at
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssssssis",
        $type,
        $title,
        $description,
        $content,
        $media_url,
        $thumbnail_url,
        $status,
        $user_id,
        $published_at
    );

    $stmt->execute();

    header("Location: editor-dashboard.php");
    exit();
}


// =====================================
// PUBLISH EDITORIAL CONTENT
// =====================================

if (isset($_POST["publish_content"])) {

    $content_id = (int)$_POST["content_id"];

    $stmt = $conn->prepare("
        UPDATE editorial_content
        SET
            status = 'Published',
            published_at = NOW()
        WHERE id = ?
    ");

    $stmt->bind_param(
        "i",
        $content_id
    );

    $stmt->execute();

    header("Location: editor-dashboard.php");
    exit();
}


// =====================================
// DELETE EDITORIAL CONTENT
// =====================================

if (isset($_POST["delete_content"])) {

    $content_id = (int)$_POST["content_id"];

    $stmt = $conn->prepare("
        DELETE FROM editorial_content
        WHERE id = ?
    ");

    $stmt->bind_param(
        "i",
        $content_id
    );

    $stmt->execute();

    header("Location: editor-dashboard.php");
    exit();
}


// =====================================
// REVIEWERS
// =====================================

$reviewers = $conn->query("
    SELECT id, fullname
    FROM users
    WHERE role = 'reviewer'
    ORDER BY fullname
");


// =====================================
// ARTICLES
// =====================================

$sql = "

SELECT
    submissions.*,
    users.fullname AS author,
    reviews.status AS review_status,
    reviews.comments

FROM submissions

JOIN users
    ON submissions.user_id = users.id

LEFT JOIN reviews
    ON submissions.id = reviews.submission_id

ORDER BY submissions.created_at DESC

";

$result = $conn->query($sql);


// =====================================
// EDITORIAL CONTENT
// =====================================

$content_result = $conn->query("

SELECT
    editorial_content.*,
    users.fullname AS editor_name

FROM editorial_content

JOIN users
    ON editorial_content.author_id = users.id

ORDER BY editorial_content.created_at DESC

");

?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Academia Institute | Excellence in Scientific Publishing</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#000a1e",
                        "primary-container": "#002147",
                        "secondary": "#426087",
                        "secondary-container": "#b3d1fe",
                        "surface": "#f8f9ff",
                        "surface-container": "#e5eeff",
                        "surface-container-low": "#eff4ff",
                        "surface-container-high": "#dce9ff",
                        "surface-container-highest": "#d3e4fe",
                        "surface-variant": "#d3e4fe",
                        "on-primary": "#ffffff",
                        "on-primary-container": "#708ab5",
                        "on-surface": "#0b1c30",
                        "on-surface-variant": "#44474e",
                        "outline": "#74777f",
                        "outline-variant": "#c4c6cf",
                        "error": "#ba1a1a"
                    },

                    spacing: {
                        "margin-desktop": "64px",
                        "margin-mobile": "20px",
                        "stack-sm": "8px",
                        "stack-md": "16px",
                        "stack-lg": "32px",
                        "stack-xl": "64px",
                        "container-max": "1200px",
                        "gutter": "24px"
                    },

                    borderRadius: {
                        DEFAULT: "2px",
                        lg: "6px",
                        xl: "10px"
                    }
                }
            }
        }
    </script>

    <style>
        .material-symbols-outlined {
            font-variation-settings:
                "FILL" 0,
                "wght" 400,
                "GRAD" 0,
                "opsz" 24;
            vertical-align: middle;
        }

        .scrolling-fade {
            mask-image: linear-gradient(to right,
                    transparent,
                    black 10%,
                    black 90%,
                    transparent);
        }

        .journal-card:hover .journal-overlay {
            opacity: 1;
        }

        body {
            background: #f8f9ff;
            color: #0b1c30;
            margin: 0;
            font-family: Inter, sans-serif;
        }
    </style>

<body>

<header class="fixed top-0 left-0 right-0 z-50 bg-surface border-b border-outline-variant backdrop-blur-md">

    <nav class="flex justify-between items-center max-w-container-max mx-auto h-20 px-margin-desktop">

        <div>

            <a href="about.html" class="no-underline">

                <div class="text-2xl font-bold tracking-tight text-primary uppercase">
                    ARAI
                </div>

                <div class="text-[11px] tracking-[2px] uppercase text-on-surface-variant">
                    Academic Research AI
                </div>

            </a>

        </div>

        <div class="hidden md:flex gap-stack-lg items-center">

            <a href="about.html" class="text-primary border-b-2 border-primary font-bold">
                About
            </a>

            <a href="journals.html" class="hover:text-primary transition">
                Journals
            </a>

            <a href="register.php" class="hover:text-primary transition">
                Publish
            </a>

            <a href="authors.html" class="hover:text-primary transition">
                Authors
            </a>

            <a href="reviewers.html" class="hover:text-primary transition">
                Reviewers
            </a>

            <a href="archive.php" class="hover:text-primary transition">
                Archive
            </a>

        </div>

        <div class="flex items-center gap-stack-md">

            <a href="archive.php" class="material-symbols-outlined hover:scale-110 transition">
                search
            </a>

            <a href="signin.php"
               class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition">
                Sign In
            </a>

        </div>

    </nav>


<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Editorial Dashboard</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>


<body class="bg-slate-100 text-slate-900">


<!-- =====================================
     HEADER
===================================== -->

<header class="bg-white border-b px-8 py-5">

    <div class="max-w-7xl mx-auto flex justify-between items-center">

        <div>

            <h1 class="text-2xl font-bold">
                Editorial Dashboard
            </h1>

            <p class="text-slate-500">
                Welcome, <?= htmlspecialchars($user["fullname"]) ?>
            </p>

        </div>

        <a
            href="about.html"
            target="_blank"
            class="px-4 py-2 bg-slate-900 text-white rounded-lg"
        >
            View About Page
        </a>

    </div>

</header>


<main class="max-w-7xl mx-auto px-8 py-10">


<!-- =====================================
     CREATE NEWS / VIDEO / COURSE
===================================== -->

<section class="bg-white rounded-xl shadow-sm p-8 mb-10">

    <h2 class="text-xl font-bold mb-2">
        Create Website Content
    </h2>

    <p class="text-slate-500 mb-6">
        Publish news, videos, courses and announcements directly to About page.
    </p>


    <form method="POST" class="space-y-5">

        <input
            type="hidden"
            name="create_content"
            value="1"
        >


        <!-- TYPE -->

        <div>

            <label class="block font-semibold mb-2">
                Content Type
            </label>

            <select
                name="type"
                required
                class="w-full border rounded-lg px-4 py-3"
            >

                <option value="">
                    Select content type
                </option>

                <option value="News">
                    📰 News
                </option>

                <option value="Video">
                    🎥 Video
                </option>

                <option value="Course">
                    🎓 Course
                </option>

                <option value="Announcement">
                    📢 Announcement
                </option>

            </select>

        </div>


        <!-- TITLE -->

        <div>

            <label class="block font-semibold mb-2">
                Title
            </label>

            <input
                type="text"
                name="title"
                required
                placeholder="Content title"
                class="w-full border rounded-lg px-4 py-3"
            >

        </div>


        <!-- DESCRIPTION -->

        <div>

            <label class="block font-semibold mb-2">
                Short Description
            </label>

            <textarea
                name="description"
                rows="3"
                placeholder="Short description..."
                class="w-full border rounded-lg px-4 py-3"
            ></textarea>

        </div>


        <!-- CONTENT -->

        <div>

            <label class="block font-semibold mb-2">
                Content
            </label>

            <textarea
                name="content"
                rows="7"
                placeholder="Write the full news, course information or announcement..."
                class="w-full border rounded-lg px-4 py-3"
            ></textarea>

        </div>


        <!-- VIDEO / LINK -->

        <div>

            <label class="block font-semibold mb-2">
                Video / Course / External URL
            </label>

            <input
                type="url"
                name="media_url"
                placeholder="https://..."
                class="w-full border rounded-lg px-4 py-3"
            >

        </div>


        <!-- IMAGE -->

        <div>

            <label class="block font-semibold mb-2">
                Thumbnail / Image URL
            </label>

            <input
                type="url"
                name="thumbnail_url"
                placeholder="https://..."
                class="w-full border rounded-lg px-4 py-3"
            >

        </div>


        <!-- STATUS -->

        <div>

            <label class="block font-semibold mb-2">
                Status
            </label>

            <select
                name="status"
                class="w-full border rounded-lg px-4 py-3"
            >

                <option value="Draft">
                    Draft
                </option>

                <option value="Published">
                    Publish immediately
                </option>

            </select>

        </div>


        <button
            type="submit"
            class="bg-blue-700 text-white px-6 py-3 rounded-lg font-bold hover:bg-blue-800"
        >

            Publish / Save Content

        </button>

    </form>

</section>


<!-- =====================================
     EXISTING WEBSITE CONTENT
===================================== -->

<section class="mb-10">

    <h2 class="text-xl font-bold mb-5">
        News / Videos / Courses
    </h2>


    <div class="space-y-4">

        <?php while ($content = $content_result->fetch_assoc()): ?>

            <div class="bg-white rounded-xl shadow-sm p-6">

                <div class="flex justify-between items-start gap-5">

                    <div>

                        <span class="text-xs font-bold uppercase text-blue-700">

                            <?= htmlspecialchars($content["type"]) ?>

                        </span>


                        <h3 class="text-lg font-bold mt-1">

                            <?= htmlspecialchars($content["title"]) ?>

                        </h3>


                        <p class="text-slate-500 mt-2">

                            <?= htmlspecialchars($content["description"]) ?>

                        </p>


                        <p class="text-sm mt-3">

                            Status:

                            <strong>

                                <?= htmlspecialchars($content["status"]) ?>

                            </strong>

                        </p>

                    </div>


                    <div class="flex gap-2">

                        <?php if ($content["status"] !== "Published"): ?>

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="content_id"
                                    value="<?= $content["id"] ?>"
                                >

                                <button
                                    type="submit"
                                    name="publish_content"
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg"
                                >

                                    Publish

                                </button>

                            </form>

                        <?php endif; ?>


                        <form method="POST">

                            <input
                                type="hidden"
                                name="content_id"
                                value="<?= $content["id"] ?>"
                            >

                            <button
                                type="submit"
                                name="delete_content"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg"
                                onclick="return confirm('Delete this content?')"
                            >

                                Delete

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        <?php endwhile; ?>

    </div>

</section>


<!-- =====================================
     ARTICLES
===================================== -->

<section>

    <h2 class="text-xl font-bold mb-5">
        Submitted Articles
    </h2>


    <?php while ($row = $result->fetch_assoc()): ?>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-5">

            <h3 class="text-lg font-bold">

                <?= htmlspecialchars($row["title"]) ?>

            </h3>


            <p class="mt-2">

                Author:

                <?= htmlspecialchars($row["author"]) ?>

            </p>


            <p class="mt-1">

                Status:

                <strong>

                    <?= htmlspecialchars($row["status"]) ?>

                </strong>

            </p>


            <p class="mt-1">

                Reviewer Decision:

                <?= htmlspecialchars(
                    $row["review_status"] ?? "Waiting for review"
                ) ?>

            </p>


            <?php if (!empty($row["comments"])): ?>

                <p class="mt-3 text-slate-600">

                    Reviewer Comment:

                    <?= htmlspecialchars($row["comments"]) ?>

                </p>

            <?php endif; ?>


            <!-- ASSIGN REVIEWER -->

            <form method="POST" class="mt-5 flex gap-3">

                <input
                    type="hidden"
                    name="submission_id"
                    value="<?= $row["id"] ?>"
                >


                <select
                    name="reviewer_id"
                    required
                    class="border rounded-lg px-4 py-2"
                >

                    <option value="">
                        Choose Reviewer
                    </option>


                    <?php

                    $reviewers->data_seek(0);

                    while ($reviewer = $reviewers->fetch_assoc()):

                    ?>

                        <option
                            value="<?= $reviewer["id"] ?>"
                        >

                            <?= htmlspecialchars(
                                $reviewer["fullname"]
                            ) ?>

                        </option>

                    <?php endwhile; ?>

                </select>


                <button
                    type="submit"
                    name="assign"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg"
                >

                    Assign Reviewer

                </button>

            </form>


            <div class="mt-5">

                <a
                    href="editor-review.php?id=<?= $row["id"] ?>"
                    class="text-blue-700 font-semibold"
                >

                    Open Manuscript →

                </a>

            </div>


            <?php if ($row["review_status"] === "Accept"): ?>

                <form method="POST" class="mt-4">

                    <input
                        type="hidden"
                        name="submission_id"
                        value="<?= $row["id"] ?>"
                    >

                    <button
                        type="submit"
                        name="publish"
                        class="bg-green-600 text-white px-5 py-2 rounded-lg"
                    >

                        Publish Article

                    </button>

                </form>

            <?php endif; ?>

        </div>

    <?php endwhile; ?>

</section>


</main>

</body>

</html>