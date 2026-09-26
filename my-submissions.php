<?php

require_once "config.php";

require_login();

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT
        submissions.*,
        r.status AS review_status,
        r.comments AS review_comment
    FROM submissions
    LEFT JOIN reviews r
        ON r.id = (
            SELECT MAX(id) FROM reviews WHERE reviews.submission_id = submissions.id
        )
    WHERE submissions.user_id = ?
    ORDER BY submissions.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Publish Article - Academia Institute</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=Playfair+Display:wght@600;700&amp;family=JetBrains+Mono&amp;display=swap" rel="stylesheet"/>
<!-- Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-tertiary-container": "#b97958",
                        "surface-container-high": "#dce9ff",
                        "on-tertiary-fixed-variant": "#6c391d",
                        "surface": "#f8f9ff",
                        "inverse-surface": "#213145",
                        "surface-variant": "#d3e4fe",
                        "surface-bright": "#f8f9ff",
                        "surface-container": "#e5eeff",
                        "surface-container-lowest": "#ffffff",
                        "on-primary-fixed": "#001b3d",
                        "error": "#ba1a1a",
                        "primary-container": "#002147",
                        "on-secondary-fixed-variant": "#2a486e",
                        "tertiary-container": "#3d1500",
                        "primary-fixed": "#d6e3ff",
                        "secondary-fixed": "#d3e3ff",
                        "outline": "#74777f",
                        "tertiary": "#180500",
                        "secondary": "#426087",
                        "on-primary": "#ffffff",
                        "surface-tint": "#465f88",
                        "secondary-fixed-dim": "#abc8f5",
                        "on-surface-variant": "#44474e",
                        "on-primary-container": "#708ab5",
                        "on-error-container": "#93000a",
                        "on-secondary": "#ffffff",
                        "on-secondary-container": "#3c5980",
                        "surface-container-highest": "#d3e4fe",
                        "inverse-on-surface": "#eaf1ff",
                        "tertiary-fixed": "#ffdbcb",
                        "on-tertiary": "#ffffff",
                        "primary-fixed-dim": "#aec7f6",
                        "background": "#f8f9ff",
                        "on-primary-fixed-variant": "#2d476f",
                        "on-error": "#ffffff",
                        "on-background": "#0b1c30",
                        "surface-container-low": "#eff4ff",
                        "tertiary-fixed-dim": "#ffb691",
                        "inverse-primary": "#aec7f6",
                        "surface-dim": "#cbdbf5",
                        "on-secondary-fixed": "#001c39",
                        "on-tertiary-fixed": "#341100",
                        "primary": "#000a1e",
                        "error-container": "#ffdad6",
                        "outline-variant": "#c4c6cf",
                        "on-surface": "#0b1c30",
                        "secondary-container": "#b3d1fe"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "margin-mobile": "20px",
                        "stack-xl": "64px",
                        "stack-md": "16px",
                        "stack-lg": "32px",
                        "margin-desktop": "64px",
                        "container-max": "1200px",
                        "gutter": "24px",
                        "stack-sm": "8px"
                    },
                    "fontFamily": {
                        "label-caps": ["Inter"],
                        "code-sm": ["JetBrains Mono"],
                        "body-sm": ["Inter"],
                        "headline-md": ["Playfair Display"],
                        "display-lg": ["Playfair Display"],
                        "body-md": ["Inter"],
                        "display-lg-mobile": ["Playfair Display"],
                        "title-lg": ["Inter"]
                    },
                    "fontSize": {
                        "label-caps": ["12px", { "lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700" }],
                        "code-sm": ["13px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "body-sm": ["14px", { "lineHeight": "22px", "fontWeight": "400" }],
                        "headline-md": ["30px", { "lineHeight": "38px", "fontWeight": "600" }],
                        "display-lg": ["48px", { "lineHeight": "60px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "body-md": ["16px", { "lineHeight": "26px", "fontWeight": "400" }],
                        "display-lg-mobile": ["32px", { "lineHeight": "40px", "fontWeight": "700" }],
                        "title-lg": ["20px", { "lineHeight": "28px", "fontWeight": "600" }]
                    }
                },
            },
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .stepper-line {
            height: 1px;
            flex-grow: 1;
            background-color: #c4c6cf; /* outline-variant */
            margin: 0 12px;
        }
        .stepper-line.active {
            background-color: #000a1e; /* primary */
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #000a1e !important;
            box-shadow: none !important;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md">
<!-- TopNavBar -->
<nav class="bg-surface border-b border-outline-variant h-20 flex justify-between items-center w-full px-margin-desktop max-w-container-max mx-auto sticky top-0 z-50">
<div class="flex items-center gap-gutter">
<span class="text-title-lg font-title-lg font-bold text-primary">Academia Institute</span>
<div class="hidden md:flex gap-stack-lg">
<a class="text-on-surface-variant font-body-md hover:text-primary transition-colors duration-200" href="about.html">About</a>
<a class="text-on-surface-variant font-body-md hover:text-primary transition-colors duration-200" href="journals.html">Journals</a>
<a class="text-primary border-b-2 border-primary font-bold font-body-md" href="publish.php">Publish</a>
<a class="text-on-surface-variant font-body-md hover:text-primary transition-colors duration-200" href="authors.html">Authors</a>
<a class="text-on-surface-variant font-body-md hover:text-primary transition-colors duration-200" href="reviewers.php">Reviewers</a>
<a class="text-on-surface-variant font-body-md hover:text-primary transition-colors duration-200" href="archive.php">Archive</a>
</div>
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
<main class="max-w-container-max mx-auto px-margin-desktop py-stack-xl">
<!-- Page Header & Stepper -->
<head>

<meta charset="UTF-8">
<title>My Submissions</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

<div class="max-w-6xl mx-auto mt-10">

<h1 class="text-3xl font-bold mb-8">
My Submissions
</h1>

<table class="w-full bg-white shadow rounded">

<thead class="bg-gray-200">

<tr>

<th class="p-4 text-left">Title</th>

<th class="p-4">Status</th>

<th class="p-4">Date</th>

<th class="p-4">PDF</th>

</tr>

</thead>


<tbody>

<?php while($row = $result->fetch_assoc()): ?>

<tr class="border-b">

    <td class="p-4">
        <?= e($row["title"]) ?>
        <div class="text-sm text-gray-500"><?= e($row["journal"]) ?></div>
        <?php if (!empty($row["review_comment"])): ?>
        <div class="mt-2 text-sm bg-gray-50 p-2 rounded">
            <strong>Reviewer (<?= e($row["review_status"]) ?>):</strong>
            <?= nl2br(e($row["review_comment"])) ?>
        </div>
        <?php endif; ?>
    </td>

    <td class="p-4 text-center">
        <span class="px-3 py-1 rounded bg-blue-100 text-blue-700">
            <?= e($row["status"]) ?>
        </span>
    </td>

    <td class="p-4 text-center">
        <?= date("d.m.Y", strtotime($row["created_at"])) ?>
    </td>

    <td class="p-4 text-center">

        <?php if(!empty($row["filename"])): ?>

            <a
                href="<?= e(upload_url($row["filename"])) ?>"
                target="_blank"
                class="text-blue-600 underline"
            >
                View file
            </a>

        <?php else: ?>

            —

        <?php endif; ?>

        <?php if ($row["status"] === "Accepted" || $row["status"] === "Payment Pending"): ?>
            <br>
            <a href="payment.php?id=<?= (int)$row["id"] ?>" class="text-blue-600 underline font-bold">
                Pay fee
            </a>
        <?php endif; ?>

    </td>

</tr>

<?php endwhile; ?>

</tbody>