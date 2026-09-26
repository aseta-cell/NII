<?php
require_once "config.php";

$reviewer = require_role("reviewer");
$user_id = $reviewer["id"];

// Manuscripts assigned to this reviewer, with this reviewer's latest review
$stmt = $conn->prepare("
SELECT
    submissions.*,
    users.fullname AS author,
    r.status AS review_status,
    r.comments
FROM submissions
JOIN users
    ON submissions.user_id = users.id
LEFT JOIN reviews r
    ON r.id = (
        SELECT MAX(id) FROM reviews
        WHERE reviews.submission_id = submissions.id
        AND reviews.reviewer_id = ?
    )
WHERE submissions.reviewer_id = ?
ORDER BY submissions.created_at DESC
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Reviewer Dashboard | Scholarly Archive</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=Playfair+Display:wght@600;700&amp;family=JetBrains+Mono:wght@400&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "surface-container-low": "#eff4ff",
                      "surface-container": "#e5eeff",
                      "tertiary-fixed-dim": "#ffb691",
                      "on-secondary-container": "#3c5980",
                      "secondary-container": "#b3d1fe",
                      "surface-bright": "#f8f9ff",
                      "primary-container": "#002147",
                      "on-secondary-fixed": "#001c39",
                      "tertiary": "#180500",
                      "surface-dim": "#cbdbf5",
                      "inverse-surface": "#213145",
                      "on-surface": "#0b1c30",
                      "surface-tint": "#465f88",
                      "on-primary": "#ffffff",
                      "surface-container-highest": "#d3e4fe",
                      "on-surface-variant": "#44474e",
                      "on-secondary": "#ffffff",
                      "on-error-container": "#93000a",
                      "on-tertiary": "#ffffff",
                      "surface-variant": "#d3e4fe",
                      "on-primary-fixed": "#001b3d",
                      "on-error": "#ffffff",
                      "secondary-fixed": "#d3e3ff",
                      "on-primary-container": "#708ab5",
                      "on-background": "#0b1c30",
                      "on-primary-fixed-variant": "#2d476f",
                      "surface": "#f8f9ff",
                      "secondary-fixed-dim": "#abc8f5",
                      "inverse-on-surface": "#eaf1ff",
                      "background": "#f8f9ff",
                      "surface-container-lowest": "#ffffff",
                      "on-secondary-fixed-variant": "#2a486e",
                      "on-tertiary-fixed": "#341100",
                      "surface-container-high": "#dce9ff",
                      "primary": "#000a1e",
                      "tertiary-container": "#3d1500",
                      "secondary": "#426087",
                      "on-tertiary-container": "#b97958",
                      "inverse-primary": "#aec7f6",
                      "outline": "#74777f",
                      "primary-fixed": "#d6e3ff",
                      "tertiary-fixed": "#ffdbcb",
                      "error": "#ba1a1a",
                      "error-container": "#ffdad6",
                      "outline-variant": "#c4c6cf",
                      "primary-fixed-dim": "#aec7f6",
                      "on-tertiary-fixed-variant": "#6c391d"
              },
              "borderRadius": {
                      "DEFAULT": "0.125rem",
                      "lg": "0.25rem",
                      "xl": "0.5rem",
                      "full": "0.75rem"
              },
              "spacing": {
                      "stack-lg": "32px",
                      "gutter": "24px",
                      "margin-desktop": "64px",
                      "stack-md": "16px",
                      "stack-xl": "64px",
                      "margin-mobile": "20px",
                      "stack-sm": "8px",
                      "container-max": "1200px"
              },
              "fontFamily": {
                      "body-md": ["Inter"],
                      "display-lg": ["Playfair Display"],
                      "code-sm": ["JetBrains Mono"],
                      "headline-md": ["Playfair Display"],
                      "title-lg": ["Inter"],
                      "label-caps": ["Inter"],
                      "body-sm": ["Inter"]
              },
              "fontSize": {
                      "body-md": ["16px", {"lineHeight": "26px", "fontWeight": "400"}],
                      "display-lg": ["48px", {"lineHeight": "60px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                      "code-sm": ["13px", {"lineHeight": "20px", "fontWeight": "400"}],
                      "headline-md": ["30px", {"lineHeight": "38px", "fontWeight": "600"}],
                      "title-lg": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                      "label-caps": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                      "body-sm": ["14px", {"lineHeight": "22px", "fontWeight": "400"}]
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
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md overflow-x-hidden">
<!-- TopNavBar -->
<header class="bg-surface-container-lowest dark:bg-primary border-b border-outline-variant dark:border-outline fixed top-0 w-full z-50 h-20 transition-all duration-200 ease-in-out">
<div class="flex justify-between items-center w-full px-margin-desktop max-w-container-max mx-auto h-full">
<div class="flex items-center gap-8">
<span class="font-display-lg text-display-lg text-primary dark:text-on-primary-fixed tracking-tight">Scholarly Archive</span>
<nav class="hidden md:flex gap-6 items-center h-full">
<a class="text-on-surface-variant dark:text-on-surface-variant hover:text-primary dark:hover:text-primary-fixed transition-colors font-body-md text-body-md" href="journals.html">Journals</a>
<a class="text-on-surface-variant dark:text-on-surface-variant hover:text-primary dark:hover:text-primary-fixed transition-colors font-body-md text-body-md" href="archive.php">Archive </a>
<a class="text-on-surface-variant dark:text-on-surface-variant hover:text-primary dark:hover:text-primary-fixed transition-colors font-body-md text-body-md" href="publish.php">Submission</a>
<a class="text-on-surface-variant dark:text-on-surface-variant hover:text-primary dark:hover:text-primary-fixed transition-colors font-body-md text-body-md" href="about.html">About</a>
</nav>
</div>
<div class="flex items-center gap-4">
 <div class="flex items-center gap-stack-md">

            <a href="archive.php" class="material-symbols-outlined hover:scale-110 transition">
                search
            </a>

            <a href="dashboard.php"
               class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition">
               Me
            </a>

        </div>
<div class="w-10 h-10 rounded-full border border-outline-variant overflow-hidden">
<img class="w-full h-full object-cover" data-alt="A professional headshot of a distinguished middle-aged female academic with a warm, intelligent expression, wearing dark-framed glasses and a professional charcoal blazer. The background is a soft-focus library with rows of leather-bound books, illuminated by gentle, natural side-lighting that enhances the scholarly and authoritative atmosphere of the scene." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAh_cjyiWoFe8B6blr1V6kpqyyW6ET5jZwIpyOLoX6qt4YJa88hBgOXQCKhoNwPQwnsfjmd9flCTBM9Rdz4x8mnVepkNw0Kdka1VImu4pV2L6gcE1sfQS6pD3jq_ynVggZC9wWXmI9zlj7kIbc63BBqD6SnDJSdw2u6ulR4fOr5BuYpCCiWJD9fecjR3cRlsShJ2yrLDnQL0Nqp5aI4j2R_GWZbIN_6emsEhO2L_JtnRyvG8McWCZo"/>
</div>
</div>
</div>
</header>
<div class="flex min-h-screen pt-20">
<!-- SideNavBar -->
<aside class="hidden md:flex flex-col h-full w-64 fixed left-0 top-20 bg-surface-container-low dark:bg-surface-container-lowest border-r border-outline-variant py-stack-md px-4 z-40">
<div class="flex items-center gap-3 mb-8 px-2">
<div class="w-10 h-10 bg-secondary-container rounded-lg flex items-center justify-center">
<span class="material-symbols-outlined text-on-secondary-container">account_circle</span>
</div>
<div>
<h3 class="font-title-lg text-body-sm font-bold truncate">Dr. Elena Rossi</h3>
<p class="text-on-surface-variant text-[11px] font-label-caps">SENIOR EDITOR</p>
</div>
</div>
<nav class="flex-grow space-y-1">
<a class="flex items-center gap-3 px-4 py-3 text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-high transition-transform scale-95 active:scale-90 rounded-lg" href="#">
<span class="material-symbols-outlined">dashboard</span>
<span class="font-label-caps text-label-caps">Dashboard</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 bg-secondary-container dark:bg-on-secondary-container text-on-secondary-container dark:text-secondary-container font-bold rounded-lg transition-transform scale-95 active:scale-90" href="#">
<span class="material-symbols-outlined">rate_review</span>
<span class="font-label-caps text-label-caps">Pending Reviews</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-high transition-transform scale-95 active:scale-90 rounded-lg" href="#">
<span class="material-symbols-outlined">task_alt</span>
<span class="font-label-caps text-label-caps">Completed Reviews</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-high transition-transform scale-95 active:scale-90 rounded-lg" href="#">
<span class="material-symbols-outlined">history</span>
<span class="font-label-caps text-label-caps">Invitation History</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 text-on-surface-variant dark:text-on-surface-variant hover:bg-surface-container-high transition-transform scale-95 active:scale-90 rounded-lg" href="#">
<span class="material-symbols-outlined">person</span>
<span class="font-label-caps text-label-caps">Profile &amp; Interests</span>
</a>
</nav>
<div class="mt-auto pt-6 border-t border-outline-variant/30 space-y-1">
<button class="w-full bg-primary text-on-primary py-3 px-4 rounded-lg font-label-caps text-label-caps hover:bg-primary/90 transition-all mb-4">
                    SUBMIT NEW PAPER
                </button>
<a class="flex items-center gap-3 px-4 py-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg" href="#">
<span class="material-symbols-outlined">help</span>
<span class="font-label-caps text-label-caps">Support</span>
</a>
<a class="flex items-center gap-3 px-4 py-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg" href="#">
<span class="material-symbols-outlined">archive</span>
<span class="font-label-caps text-label-caps">Archive</span>
</a>
</div>
</aside>
<!-- Main Content Area -->
<main class="flex-1 md:ml-64 px-margin-mobile md:px-margin-desktop py-stack-lg max-w-container-max mx-auto w-full">
<!-- Welcome Header -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-stack-xl fade-in">
<div>
<h1 class="font-display-lg text-headline-md mb-2">Reviewer Workspace</h1>
<p class="text-on-surface-variant max-w-xl">Manage your active manuscript reviews and track your contributions to the global scientific record.</p>
</div>
<!-- Recognition Summary -->
<div class="flex gap-4">
<div class="bg-surface-container-lowest border border-outline-variant p-4 rounded-xl flex items-center gap-4 min-w-[200px]">
<div class="w-12 h-12 bg-tertiary-fixed rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-on-tertiary-fixed text-3xl" style="font-variation-settings: 'FILL' 1;">workspace_premium</span>
</div>
<div>
<p class="text-label-caps font-label-caps text-on-surface-variant">Level</p>
<p class="font-title-lg text-primary">Silver Scholar</p>
</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant p-4 rounded-xl flex items-center gap-4 min-w-[200px]">
<div class="w-12 h-12 bg-secondary-container rounded-full flex items-center justify-center">
<span class="material-symbols-outlined text-on-secondary-container text-3xl">verified</span>
</div>
<div>
<p class="text-label-caps font-label-caps text-on-surface-variant">Completed</p>
<p class="font-title-lg text-primary">24 Reviews</p>
</div>
</div>
</div>
</div>
<!-- Bento Layout Container -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
<!-- Pending Reviews Section (Main Column) -->
<div class="lg:col-span-8 space-y-gutter">
<div class="flex items-center justify-between">

<h2 class="font-headline-md text-title-lg">Pending Reviews (<?= $result->num_rows ?>)</h2>

<button class="text-secondary font-label-caps flex items-center gap-2 hover:underline">
                            View All <span class="material-symbols-outlined text-sm">arrow_forward</span>
</button>
</div>
<!-- Manuscript Cards -->
<div class="space-y-4">
<!-- Card 1: Urgent -->
<?php if (isset($_GET["reviewed"])): ?>
<div class="p-4 rounded bg-green-100 text-green-800">Review sent. Thank you!</div>
<?php endif; ?>
<?php if ($result->num_rows === 0): ?>
<div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl">
No manuscripts are assigned to you yet.
</div>
<?php endif; ?>
<?php while($row = $result->fetch_assoc()): ?>

<div class="bg-surface-container-lowest border border-outline-variant p-6 rounded-xl">

<div class="flex justify-between">

<div>

<h3 class="font-display-lg text-title-lg">

<?= e($row["title"]) ?>

</h3>
<p class="text-secondary">
    Journal:
    <?= e($row["journal"]) ?>
</p>
<p class="text-secondary">
Author:
<?= isset($row["author"]) ? e($row["author"]) : "Unknown" ?>
</p>
<p>
Status:

<?= e($row["review_status"] ?? "Pending Review") ?>

</p>

</div>

<span class="px-3 py-1 rounded-full bg-secondary-container">

<?= e($row["status"]) ?>

</span>

</div>

<div class="mt-5">

<a
href="<?= e(upload_url($row["filename"])) ?>"
target="_blank"
class="text-primary">

Download Manuscript

</a>

</div>

<div class="mt-5">

<a
href="review.php?id=<?= (int)$row["id"] ?>"
class="bg-primary text-white px-5 py-2 rounded">

Review Article

</a>

</div>

</div>

<br>

<?php endwhile; ?>
</div>
</div>
<!-- Right Column: Sidebar Panels -->
<div class="lg:col-span-4 space-y-gutter">
<!-- Quick-Access Guidelines -->
<section class="bg-surface-container-low border border-outline-variant rounded-xl overflow-hidden">
<div class="p-6">
<div class="flex items-center gap-3 mb-6">
<span class="material-symbols-outlined text-primary">gavel</span>
<h2 class="font-title-lg text-body-md font-bold uppercase tracking-wider">Reviewer Guidelines</h2>
</div>
<ul class="space-y-4">
<li class="flex gap-3">
<span class="material-symbols-outlined text-secondary text-lg">check_circle</span>
<p class="text-body-sm text-on-surface">Maintain absolute confidentiality of all unpublished manuscripts.</p>
</li>
<li class="flex gap-3">
<span class="material-symbols-outlined text-secondary text-lg">check_circle</span>
<p class="text-body-sm text-on-surface">Disclose any potential conflicts of interest immediately.</p>
</li>
<li class="flex gap-3">
<span class="material-symbols-outlined text-secondary text-lg">check_circle</span>
<p class="text-body-sm text-on-surface">Provide constructive, evidence-based feedback to authors.</p>
</li>
</ul>
<a class="mt-6 inline-flex items-center gap-2 text-primary font-label-caps text-label-caps group" href="#">
                                READ FULL ETHICAL POLICY 
                                <span class="material-symbols-outlined text-sm group-hover:translate-x-1 transition-transform">open_in_new</span>
</a>
</div>
<div class="bg-surface-container-high p-4 flex items-center justify-between">
<span class="text-label-caps text-[10px] text-on-surface-variant">LAST UPDATED: JAN 2024</span>
<span class="material-symbols-outlined text-on-surface-variant text-lg">download</span>
</div>
</section>
<!-- Profile & Interests Panel -->
<section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6">
<div class="flex items-center justify-between mb-6">
<h2 class="font-title-lg text-body-md font-bold uppercase tracking-wider">Your Interests</h2>
<button class="material-symbols-outlined text-on-surface-variant">edit</button>
</div>
<div class="flex flex-wrap gap-2">
<span class="px-3 py-1 bg-surface-container rounded-full text-label-caps text-on-secondary-container">Machine Learning</span>
<span class="px-3 py-1 bg-surface-container rounded-full text-label-caps text-on-secondary-container">Bio-Ethics</span>
<span class="px-3 py-1 bg-surface-container rounded-full text-label-caps text-on-secondary-container">Data Privacy</span>
<span class="px-3 py-1 bg-surface-container rounded-full text-label-caps text-on-secondary-container">Neuroscience</span>
<span class="px-3 py-1 bg-surface-container rounded-full text-label-caps text-on-secondary-container">Public Policy</span>
</div>
<p class="mt-6 text-body-sm text-on-surface-variant italic">Refining your interests helps us match you with high-relevance manuscripts.</p>
</section>
<!-- Activity Timeline (Micro-minimalist) -->
<section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6">
<h2 class="font-title-lg text-body-md font-bold uppercase tracking-wider mb-6">Recent Activity</h2>
<div class="space-y-6 relative before:content-[''] before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-[1px] before:bg-outline-variant">
<div class="relative pl-8">
<div class="absolute left-0 top-1 w-6 h-6 bg-surface-container-lowest border-2 border-primary rounded-full flex items-center justify-center z-10">
<div class="w-2 h-2 bg-primary rounded-full"></div>
</div>
<p class="text-label-caps text-on-surface-variant text-[10px]">TODAY</p>
<p class="text-body-sm">Accepted invitation to review "Quantifying Open Access Impact"</p>
</div>
<div class="relative pl-8">
<div class="absolute left-0 top-1 w-6 h-6 bg-surface-container-lowest border-2 border-outline-variant rounded-full flex items-center justify-center z-10">
<span class="material-symbols-outlined text-secondary text-sm" style="font-variation-settings: 'FILL' 1;">check</span>
</div>
<p class="text-label-caps text-on-surface-variant text-[10px]">3 DAYS AGO</p>
<p class="text-body-sm">Completed review for Manuscript #4412-A</p>
</div>
<div class="relative pl-8">
<div class="absolute left-0 top-1 w-6 h-6 bg-surface-container-lowest border-2 border-outline-variant rounded-full flex items-center justify-center z-10">
<div class="w-2 h-2 bg-outline-variant rounded-full"></div>
</div>
<p class="text-label-caps text-on-surface-variant text-[10px]">1 WEEK AGO</p>
<p class="text-body-sm">Updated research interests profile</p>
</div>
</div>
</section>
</div>
</div>
</main>
</div>
<!-- Footer -->
<footer class="bg-primary dark:bg-surface-container-lowest w-full py-stack-xl mt-stack-xl">
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter px-margin-desktop max-w-container-max mx-auto text-on-primary dark:text-on-surface">
<div class="col-span-1 lg:col-span-2">
<span class="font-display-lg text-display-lg text-on-primary mb-4 block">Scholarly Archive</span>
<p class="font-body-sm text-body-sm opacity-80 max-w-md">The premier global destination for rigorous scientific discourse and peer-reviewed excellence since 1984. Empowering researchers across every discipline.</p>
</div>
<div>
<h4 class="font-label-caps text-label-caps font-bold mb-6 opacity-60">Governance</h4>
<ul class="space-y-3 font-body-sm">
<li><a class="hover:text-on-primary/80 transition-colors" href="#">Privacy Policy</a></li>
<li><a class="hover:text-on-primary/80 transition-colors" href="#">Ethical Guidelines</a></li>
<li><a class="hover:text-on-primary/80 transition-colors" href="#">Open Access</a></li>
<li><a class="hover:text-on-primary/80 transition-colors" href="#">Indexing</a></li>
</ul>
</div>
<div>
<h4 class="font-label-caps text-label-caps font-bold mb-6 opacity-60">Connect</h4>
<ul class="space-y-3 font-body-sm">
<li><a class="hover:text-on-primary/80 transition-colors" href="#">API Access</a></li>
<li><a class="hover:text-on-primary/80 transition-colors" href="#">Contact</a></li>
<li><a class="hover:text-on-primary/80 transition-colors" href="#">Twitter</a></li>
<li><a class="hover:text-on-primary/80 transition-colors" href="#">LinkedIn</a></li>
</ul>
</div>
</div>
<div class="px-margin-desktop max-w-container-max mx-auto border-t border-on-primary/10 mt-stack-lg pt-stack-md flex flex-col md:flex-row justify-between items-center gap-4">
<p class="font-body-sm text-body-sm opacity-60">© 2024 Global Research Institute. All rights reserved. Peer-reviewed excellence.</p>
<div class="flex gap-4">
<span class="material-symbols-outlined text-on-primary/40">language</span>
<span class="material-symbols-outlined text-on-primary/40">shield_with_heart</span>
</div>
</div>
</footer>
<!-- Interaction Script -->
<script>
        document.addEventListener('DOMContentLoaded', () => {
            // Simple interaction: hover effect on cards
            const cards = document.querySelectorAll('.group');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.classList.add('shadow-sm');
                });
                card.addEventListener('mouseleave', () => {
                    card.classList.remove('shadow-sm');
                });
            });

            // Smooth entrance animations for list items
            const manuscripts = document.querySelectorAll('.space-y-4 > div');
            manuscripts.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = `all 0.5s ease-out ${index * 0.1}s`;
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, 100);
            });
        });
    </script>
</body></html>