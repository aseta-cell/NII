<?php
require_once "config.php";

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}
$journal = "";

if (isset($_GET["journal"])) {
    $journal = $_GET["journal"];
}

$sql = "
SELECT
    submissions.*,
    users.fullname AS author
FROM submissions
JOIN users
ON submissions.user_id = users.id
WHERE submissions.status='Published'
";
if ($journal != "") {

    $journal = $conn->real_escape_string($journal);

    $sql .= " AND submissions.journal='$journal' ";
}
if ($search != "") {

    $search = $conn->real_escape_string($search);

    $sql .= "
    AND (
        submissions.title LIKE '%$search%'
        OR users.fullname LIKE '%$search%'
        OR submissions.doi LIKE '%$search%'
        OR submissions.journal LIKE '%$search%'
    )
    ";

}

$sql .= " ORDER BY submissions.created_at DESC";
if (!$result = $conn->query($sql)) {
    die("SQL Error: " . $conn->error);
}
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Global Archive | Scholarly Archive</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=Playfair+Display:wght@600;700&amp;family=JetBrains+Mono&amp;display=swap" rel="stylesheet"/>
<!-- Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "tertiary-fixed": "#ffdbcb",
                    "on-secondary-fixed-variant": "#2a486e",
                    "inverse-on-surface": "#eaf1ff",
                    "on-primary-container": "#708ab5",
                    "surface-container-low": "#eff4ff",
                    "primary": "#000a1e",
                    "tertiary-fixed-dim": "#ffb691",
                    "tertiary-container": "#3d1500",
                    "secondary-fixed": "#d3e3ff",
                    "on-secondary-container": "#3c5980",
                    "on-surface-variant": "#44474e",
                    "secondary-fixed-dim": "#abc8f5",
                    "primary-fixed": "#d6e3ff",
                    "inverse-surface": "#213145",
                    "surface-container-high": "#dce9ff",
                    "surface": "#f8f9ff",
                    "on-primary-fixed": "#001b3d",
                    "error": "#ba1a1a",
                    "inverse-primary": "#aec7f6",
                    "secondary-container": "#b3d1fe",
                    "error-container": "#ffdad6",
                    "on-tertiary-fixed": "#341100",
                    "background": "#f8f9ff",
                    "outline": "#74777f",
                    "on-secondary": "#ffffff",
                    "on-tertiary": "#ffffff",
                    "surface-bright": "#f8f9ff",
                    "on-primary-fixed-variant": "#2d476f",
                    "on-background": "#0b1c30",
                    "surface-tint": "#465f88",
                    "surface-container-highest": "#d3e4fe",
                    "tertiary": "#180500",
                    "surface-container": "#e5eeff",
                    "on-secondary-fixed": "#001c39",
                    "surface-variant": "#d3e4fe",
                    "on-error-container": "#93000a",
                    "secondary": "#426087",
                    "on-surface": "#0b1c30",
                    "outline-variant": "#c4c6cf",
                    "primary-container": "#002147",
                    "on-error": "#ffffff",
                    "on-primary": "#ffffff",
                    "on-tertiary-fixed-variant": "#6c391d",
                    "surface-container-lowest": "#ffffff",
                    "primary-fixed-dim": "#aec7f6",
                    "on-tertiary-container": "#b97958",
                    "surface-dim": "#cbdbf5"
            },
            "borderRadius": {
                    "DEFAULT": "0.125rem",
                    "lg": "0.25rem",
                    "xl": "0.5rem",
                    "full": "0.75rem"
            },
            "spacing": {
                    "stack-sm": "8px",
                    "stack-xl": "64px",
                    "gutter": "24px",
                    "margin-mobile": "20px",
                    "stack-md": "16px",
                    "stack-lg": "32px",
                    "container-max": "1200px",
                    "margin-desktop": "64px"
            },
            "fontFamily": {
                    "headline-md": ["Playfair Display"],
                    "label-caps": ["Inter"],
                    "title-lg": ["Inter"],
                    "body-sm": ["Inter"],
                    "body-md": ["Inter"],
                    "display-lg": ["Playfair Display"],
                    "display-lg-mobile": ["Playfair Display"],
                    "code-sm": ["JetBrains Mono"]
            },
            "fontSize": {
                    "headline-md": ["30px", {"lineHeight": "38px", "fontWeight": "600"}],
                    "label-caps": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                    "title-lg": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                    "body-sm": ["14px", {"lineHeight": "22px", "fontWeight": "400"}],
                    "body-md": ["16px", {"lineHeight": "26px", "fontWeight": "400"}],
                    "display-lg": ["48px", {"lineHeight": "60px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "display-lg-mobile": ["32px", {"lineHeight": "40px", "fontWeight": "700"}],
                    "code-sm": ["13px", {"lineHeight": "20px", "fontWeight": "400"}]
            }
          },
        },
      }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .active-dot {
            width: 6px;
            height: 6px;
            background-color: theme('colors.primary');
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .border-thin { border-width: 1px; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: theme('colors.surface-container-low'); }
        ::-webkit-scrollbar-thumb { background: theme('colors.outline-variant'); border-radius: 10px; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md selection:bg-secondary-container">
<!-- TopNavBar -->
<nav class="w-full top-0 sticky z-50 bg-surface dark:bg-background border-b border-outline-variant dark:border-outline flat no shadows h-20">
<div class="max-w-container-max mx-auto px-margin-desktop flex justify-between items-center h-full">
<div class="flex items-center gap-stack-lg">
<span class="font-headline-md text-headline-md text-primary dark:text-primary-fixed uppercase tracking-tight cursor-pointer active:opacity-70">
                    Scholarly Archive
                </span>
<div class="hidden md:flex gap-stack-md">
<div class="hidden md:flex gap-stack-md">

<a class="font-title-lg text-title-lg text-on-surface-variant hover:text-primary transition-colors duration-200"
href="journals.html">
Journals
</a>


<a class="font-title-lg text-title-lg text-on-surface-variant hover:text-primary transition-colors duration-200"
href="reviewers.html">
Peer Review
</a>


<a class="font-title-lg text-title-lg text-on-surface-variant hover:text-primary transition-colors duration-200"
href="my-submissions.html">
Submissions
</a>


<a class="font-title-lg text-title-lg text-on-surface-variant hover:text-primary transition-colors duration-200"
href="about.html">
About
</a>


</div>


</div>


<div class="flex items-center gap-stack-md">


<a href="institutional.html"
class="font-title-lg text-title-lg text-on-surface-variant hover:text-primary transition-colors duration-200">

Institutional Access

</a>



<a href="signin.php"
class="bg-primary text-on-primary px-6 py-2 rounded-lg font-title-lg text-title-lg hover:opacity-90 transition">

Sign In

</a>


</div>

        </div>
</div>
</nav>
<!-- Main Content Canvas -->
<main class="max-w-container-max mx-auto px-margin-desktop py-stack-lg">
<!-- Breadcrumbs -->
<nav class="flex items-center gap-2 mb-stack-md text-on-surface-variant font-label-caps text-[10px] uppercase tracking-widest">
<a class="hover:text-primary transition-colors" href="#">Archive</a>
<span class="material-symbols-outlined text-[12px]">chevron_right</span>
<a class="hover:text-primary transition-colors" href="#">AI Journal</a>
<span class="material-symbols-outlined text-[12px]">chevron_right</span>
<a class="hover:text-primary transition-colors" href="#">Volume 14</a>
<span class="material-symbols-outlined text-[12px]">chevron_right</span>
<span class="text-primary font-bold">Issue 2</span>
</nav>
<!-- Hero Section -->
<section class="mb-stack-xl border-b border-outline-variant pb-stack-lg">
<h1 class="font-display-lg text-display-lg text-primary mb-stack-md">Publication Archive</h1>
<?php if($journal!=""): ?>

<p class="text-xl mt-3">

Showing articles from

<b><?= htmlspecialchars($journal) ?></b>

</p>

<?php endif; ?>
<div class="relative max-w-2xl group">
<form method="GET" class="relative max-w-2xl group">
<input
class="w-full pl-12 pr-28 py-4 bg-surface-container-lowest border border-outline-variant"
type="text"
name="search"
placeholder="Search by DOI, Title, or Author..."
value="<?= htmlspecialchars($search) ?>">

<button
type="submit"
class="absolute right-2 top-1/2 -translate-y-1/2 bg-primary text-white px-6 py-2">

Search

</button>

</form></div>
</section>
<div class="flex flex-col lg:flex-row gap-gutter">
<!-- Sidebar Filter -->
<aside class="w-full lg:w-64 flex-shrink-0">
<div class="sticky top-28 space-y-stack-lg">
<!-- Categories -->
<div>
<h3 class="font-label-caps text-label-caps text-primary mb-stack-sm border-b border-outline-variant pb-1">Filter by Journal</h3>
<ul class="space-y-1 mt-stack-md">
<a href="journal.php?journal=Artificial%20Intelligence">
Artificial Intelligence
</a>

<a href="journal.php?journal=Clinical%20Medicine">
Clinical Medicine
</a>

<a href="journal.php?journal=Social%20Sciences">
Social Sciences
</a>

<a href="journal.php?journal=Engineering%20%26%20Tech">
Engineering &amp; Tech
</a>
</ul>
</div>
<!-- Year Range -->
<div>
<h3 class="font-label-caps text-label-caps text-primary mb-stack-sm border-b border-outline-variant pb-1">Publication Year</h3>
<div class="mt-stack-md space-y-2">
<label class="flex items-center gap-2 font-body-sm text-on-surface-variant cursor-pointer">
<input checked="" class="rounded-none border-outline-variant text-primary focus:ring-primary" type="checkbox"/> 2024
                            </label>
<label class="flex items-center gap-2 font-body-sm text-on-surface-variant cursor-pointer">
<input class="rounded-none border-outline-variant text-primary focus:ring-primary" type="checkbox"/> 2023
                            </label>
<label class="flex items-center gap-2 font-body-sm text-on-surface-variant cursor-pointer">
<input class="rounded-none border-outline-variant text-primary focus:ring-primary" type="checkbox"/> 2022
                            </label>
</div>
</div>
<!-- Doc Type -->
<div>
<h3 class="font-label-caps text-label-caps text-primary mb-stack-sm border-b border-outline-variant pb-1">Document Type</h3>
<div class="mt-stack-md space-y-2">
<label class="flex items-center gap-2 font-body-sm text-on-surface-variant cursor-pointer">
<input class="border-outline-variant text-primary focus:ring-primary" name="doctype" type="radio"/> Research Article
                            </label>
<label class="flex items-center gap-2 font-body-sm text-on-surface-variant cursor-pointer">
<input class="border-outline-variant text-primary focus:ring-primary" name="doctype" type="radio"/> Review Paper
                            </label>
</div>
</div>
</div>
</aside>
<!-- Main Listing Grid -->
<div class="flex-grow">
<!-- Journal Header -->

<?php

$title = "Journal";

if($journal=="Engineering & Tech"){
    $title="Journal of Engineering & Technology";
}

if($journal=="Artificial Intelligence"){
    $title="Journal of Artificial Intelligence";
}

if($journal=="Clinical Medicine"){
    $title="Journal of Clinical Medicine";
}

if($journal=="Social Sciences"){
    $title="Journal of Social Sciences";
}

?>
<!-- Volumes and Issues Accordion -->
<div class="space-y-stack-sm mb-stack-xl">
<!-- Volume 14 (Active) -->
<div class="border border-outline-variant">
<button class="w-full flex items-center justify-between p-4 bg-surface-container-low text-primary font-bold transition-all">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined">folder_open</span>
</div>
<span class="material-symbols-outlined">expand_less</span>
</button>

<!-- Issue Detail: Preview List -->

<?php if($result->num_rows == 0): ?>

<div class="p-8 border border-outline-variant bg-white text-center">
    <h3 class="text-xl font-bold">No articles found</h3>
    <p class="text-gray-500 mt-2">
        There are currently no published articles in this journal.
    </p>
</div>

<?php else: ?>

<?php while($row = $result->fetch_assoc()): ?>

<div class="bg-white border border-outline-variant rounded-lg p-6 mb-6 hover:shadow-lg transition">

    <div class="flex justify-between items-center mb-3">

        <span class="bg-primary text-white text-xs px-3 py-1 rounded">
            Research Article
        </span>

        <span class="text-gray-500 text-sm">
            <?= date("d M Y", strtotime($row["created_at"])) ?>
        </span>

    </div>

    <h2 class="text-2xl font-bold text-primary mb-3">
        <?= htmlspecialchars($row["title"]) ?>
    </h2>

    <p class="text-gray-700 mb-2">
        <strong>Authors:</strong>
        <?= htmlspecialchars($row["author"]) ?>
    </p>

    <p class="text-gray-700 mb-2">
        <strong>Journal:</strong>
        <?= htmlspecialchars($row["journal"]) ?>
    </p>

    <p class="text-gray-700 mb-4">
        <strong>DOI:</strong>
        <?= htmlspecialchars($row["doi"]) ?>
    </p>

    <p class="text-gray-600 leading-7 mb-6">
        <?= htmlspecialchars(substr($row["abstract"],0,300)) ?>...
    </p>

    <div class="flex gap-4">
<a href="article.php?id=<?= $row["id"] ?>">
    View Article (ID = <?= $row["id"] ?>)
</a>

        <a
            href="<?= htmlspecialchars($row["pdf"]) ?>"
            target="_blank"
            class="border border-primary text-primary px-5 py-2 rounded hover:bg-gray-100">
            Download PDF
        </a>

    </div>

</div>
<hr class="my-8">

<h2 class="text-3xl font-bold mb-4">
PDF Viewer
</h2>

<iframe
    src="<?= htmlspecialchars($article["pdf"]) ?>"
    width="100%"
    height="900"
    class="border rounded-lg">
</iframe>

<div class="mt-6">
    <a
        href="<?= htmlspecialchars($article["pdf"]) ?>"
        target="_blank"
        class="bg-primary text-white px-6 py-3 rounded">

        Download PDF

    </a>
</div>
<?php endwhile; ?>

<?php endif; ?>

</main>
<!-- Footer -->
<footer class="w-full mt-stack-xl bg-primary dark:bg-primary-container border-t border-outline dark:border-outline-variant flat no shadows">
<div class="max-w-container-max mx-auto px-margin-desktop py-stack-xl flex flex-col md:flex-row justify-between items-start gap-stack-md">
<div class="space-y-4">
<span class="font-title-lg text-title-lg text-on-primary">Scholarly Archive</span>
<p class="max-w-xs font-body-sm text-on-primary-fixed-variant opacity-80">
                    A global initiative for open-access research, ensuring the permanence of intellectual discovery through rigorous peer-review.
                </p>
</div>
<div class="flex flex-col gap-4">
<div class="flex flex-wrap gap-stack-lg">
<a class="font-body-sm text-body-sm text-on-primary-fixed-variant opacity-80 hover:opacity-100 transition-opacity cursor-pointer" href="#">Ethics Policy</a>
<a class="font-body-sm text-body-sm text-on-primary-fixed-variant opacity-80 hover:opacity-100 transition-opacity cursor-pointer" href="#">Governance</a>
<a class="font-body-sm text-body-sm text-on-primary-fixed-variant opacity-80 hover:opacity-100 transition-opacity cursor-pointer underline" href="#">Privacy</a>
<a class="font-body-sm text-body-sm text-on-primary-fixed-variant opacity-80 hover:opacity-100 transition-opacity cursor-pointer" href="#">Terms of Service</a>
<a class="font-body-sm text-body-sm text-on-primary-fixed-variant opacity-80 hover:opacity-100 transition-opacity cursor-pointer" href="#">Contact</a>
</div>
<span class="font-body-sm text-body-sm text-on-primary-fixed-variant opacity-60">
                    © 2024 Global Research Institute. All rights reserved. Peer-reviewed for excellence.
                </span>
</div>
</div>
</footer>
<script>
        // Micro-interactions
        document.querySelectorAll('button').forEach(button => {
            button.addEventListener('mousedown', () => {
                button.classList.add('scale-95');
            });
            button.addEventListener('mouseup', () => {
                button.classList.remove('scale-95');
            });
            button.addEventListener('mouseleave', () => {
                button.classList.remove('scale-95');
            });
        });

        // Simple filtering simulation log
        console.log('Archive UI Initialized: Scholarly Integrity Mode Active.');
    </script>
</body></html>