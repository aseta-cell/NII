<?php
require_once "config.php";

$journal = $settings["journals"][basename(__FILE__)];

$user_id = $_SESSION["user_id"] ?? null;


// -------------------------
// LIKE COUNT
// -------------------------

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM journal_likes
    WHERE journal = ?
");

$stmt->bind_param("s", $journal);
$stmt->execute();

$like_result = $stmt->get_result()->fetch_assoc();

$like_count = $like_result["total"];


// -------------------------
// COMMENT COUNT
// -------------------------

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM journal_comments
    WHERE journal = ?
    AND status = 'Published'
");

$stmt->bind_param("s", $journal);
$stmt->execute();

$comment_result = $stmt->get_result()->fetch_assoc();

$comment_count = $comment_result["total"];


// -------------------------
// VIEW COUNT
// -------------------------

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM journal_views
    WHERE journal = ?
");

$stmt->bind_param("s", $journal);
$stmt->execute();

$view_result = $stmt->get_result()->fetch_assoc();

$view_count = $view_result["total"];


// -------------------------
// CHECK LIKE
// -------------------------

$user_liked = false;

if ($user_id) {

    $stmt = $conn->prepare("
        SELECT id
        FROM journal_likes
        WHERE journal = ?
        AND user_id = ?
    ");

    $stmt->bind_param("si", $journal, $user_id);
    $stmt->execute();

    $user_liked = $stmt->get_result()->num_rows > 0;
}


// -------------------------
// CHECK FOLLOW
// -------------------------

$user_following = false;

if ($user_id) {

    $stmt = $conn->prepare("
        SELECT id
        FROM journal_follows
        WHERE journal = ?
        AND user_id = ?
    ");

    $stmt->bind_param("si", $journal, $user_id);
    $stmt->execute();

    $user_following = $stmt->get_result()->num_rows > 0;
}


// -------------------------
// ADD VIEW
// -------------------------

$stmt = $conn->prepare("
    INSERT INTO journal_views (journal, user_id)
    VALUES (?, ?)
");

$stmt->bind_param("si", $journal, $user_id);
$stmt->execute();

$view_count++;

?><!DOCTYPE html>

<html class="scroll-smooth" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>International Journal of Medicine | Academia Institute</title>
<!-- Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
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
                      "label-caps": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                      "code-sm": ["13px", {"lineHeight": "20px", "fontWeight": "400"}],
                      "body-sm": ["14px", {"lineHeight": "22px", "fontWeight": "400"}],
                      "headline-md": ["30px", {"lineHeight": "38px", "fontWeight": "600"}],
                      "display-lg": ["48px", {"lineHeight": "60px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                      "body-md": ["16px", {"lineHeight": "26px", "fontWeight": "400"}],
                      "display-lg-mobile": ["32px", {"lineHeight": "40px", "fontWeight": "700"}],
                      "title-lg": ["20px", {"lineHeight": "28px", "fontWeight": "600"}]
              }
            },
          },
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
        }
        .scrolling-wrapper {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .scrolling-wrapper::-webkit-scrollbar {
            display: none;
        }
        .paper-card {
            transition: border-color 0.2s ease, transform 0.2s ease;
        }
        .paper-card:hover {
            border-color: #426087;
        }
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md selection:bg-primary-fixed selection:text-on-primary-fixed">
<!-- TopNavBar -->
<nav class="bg-surface dark:bg-inverse-surface border-b border-outline-variant dark:border-outline fixed top-0 left-0 right-0 z-50 h-20">
<div class="flex justify-between items-center w-full px-margin-desktop max-w-container-max mx-auto h-full">
<div class="text-title-lg font-title-lg font-bold text-primary dark:text-inverse-primary">
                Academia Institute
            </div>
<div class="hidden md:flex gap-gutter items-center">
<a class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-inverse-primary transition-colors duration-200 font-body-md text-body-md" href="about.html">About</a>
<a class="text-primary dark:text-inverse-primary border-b-2 border-primary font-bold transition-colors duration-200 font-body-md text-body-md" href="#">Journals</a>
<a class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-inverse-primary transition-colors duration-200 font-body-md text-body-md" href="publish.php">Publish</a>
<a class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-inverse-primary transition-colors duration-200 font-body-md text-body-md" href="authors.html">Authors</a>
<div class="flex items-center gap-stack-sm ml-stack-lg"><a href="dashboard.php">
    <span class="material-symbols-outlined text-primary cursor-pointer hover:text-secondary transition-all">
        account_circle
    </span>
</a>

<a href="signin.php">
    <button class="bg-primary-container text-on-primary-container px-6 py-2 rounded font-label-caps text-label-caps hover:opacity-80 transition-all cursor-pointer">
        Sign In
    </button>
</a>
</div>
</div>
</div>
</nav>
<!-- Page Layout Wrapper -->
<div class="max-w-container-max mx-auto px-margin-desktop flex flex-col md:flex-row pt-20 min-h-screen">
<!-- SideNavBar -->
<aside class="w-full md:w-64 md:fixed md:h-[calc(100vh-80px)] bg-surface-container-low dark:bg-surface-container border-r border-outline-variant dark:border-outline flex flex-col gap-stack-md py-stack-lg z-40 overflow-y-auto">
<div class="px-6 mb-stack-md">
<h2 class="text-title-lg font-title-lg text-primary">International Journal of Medicine </h2>
<p class="text-body-sm font-body-sm text-on-surface-variant opacity-70">Impact Factor: 4.8</p>
</div>
<nav class="flex flex-col">
<a class="text-primary dark:text-inverse-primary font-bold border-l-4 border-primary pl-4 py-3 bg-surface-container hover:bg-surface-variant transition-all duration-150 flex items-center gap-stack-sm" href="#aims">
<span class="material-symbols-outlined text-[20px]" data-icon="info">info</span>
<span class="text-label-caps font-label-caps">Aims &amp; Scope</span>
</a>
<a class="text-on-secondary-container dark:text-on-secondary px-4 py-3 hover:bg-surface-variant transition-all duration-150 flex items-center gap-stack-sm" href="#editorial">
<span class="material-symbols-outlined text-[20px]" data-icon="groups">groups</span>
<span class="text-label-caps font-label-caps">Editorial Board</span>
</a>
<a class="text-on-secondary-container dark:text-on-secondary px-4 py-3 hover:bg-surface-variant transition-all duration-150 flex items-center gap-stack-sm" href="#issues">
<span class="material-symbols-outlined text-[20px]" data-icon="library_books">library_books</span>
<span class="text-label-caps font-label-caps">Issues</span>
</a>
<a class="text-on-secondary-container dark:text-on-secondary px-4 py-3 hover:bg-surface-variant transition-all duration-150 flex items-center gap-stack-sm" href="#instructions">
<span class="material-symbols-outlined text-[20px]" data-icon="description">description</span>
<span class="text-label-caps font-label-caps">Instructions</span>
</a>
<a class="text-on-secondary-container dark:text-on-secondary px-4 py-3 hover:bg-surface-variant transition-all duration-150 flex items-center gap-stack-sm" href="#ethics">
<span class="material-symbols-outlined text-[20px]" data-icon="gavel">gavel</span>
<span class="text-label-caps font-label-caps">Ethics</span>
</a>
<a class="text-on-secondary-container dark:text-on-secondary px-4 py-3 hover:bg-surface-variant transition-all duration-150 flex items-center gap-stack-sm" href="#contacts">
<span class="material-symbols-outlined text-[20px]" data-icon="mail">mail</span>
<span class="text-label-caps font-label-caps">Contacts</span>
</a>
</nav>
<div class="mt-auto px-4 pb-stack-lg">
    <a href="my-submissions.php"
       class="block w-full bg-primary text-on-primary py-3 px-4 rounded-lg font-label-caps text-label-caps hover:opacity-90 active:scale-95 transition-all text-center">
        Submit Manuscript
    </a>
</div>
</aside>
<!-- Main Content Area -->
<main class="flex-1 md:ml-64 py-stack-xl">
<div class="max-w-[800px] mx-auto px-4">
<!-- Journal Header Section -->
<section class="mb-stack-xl">
<div class="flex flex-col md:flex-row gap-stack-lg items-start">
<div class="w-48 h-64 bg-surface-container shadow-sm border border-outline-variant flex-shrink-0 relative">
<div class="absolute inset-0 bg-cover bg-center" data-alt="A sophisticated academic journal cover design featuring abstract blue and white neural 
network patterns with 'International Journal of Medicine ' written in elegant serif typography. 
The lighting is clean and professional, evoking a sense of scientific rigor and prestige in the field of high-tech 
research and digital intelligence." style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuApKet2IO_wOnj5bWcpyKkzyAWgJZsbPQqzUx-lfO0n_PUfmjtnGPvnmHUTRMUdlaZgCD6EFKneDkTXEqUBVcoGy9gT5Nj_bk33n6X-IcSbPphZa6aBRrhVhmEm6ZK56uxJGpsMQKQ1OebF_hsRrDCTrjnqBkLc0-vGA6TfZSYGjS2KcBS5xhMmGbWv8B7e7GCC0Jzog6FJkkB_e1CYITq0rm3w_Z289cDJHM8igpsOUuH9NwvH_tI')"></div>
</div>
<div class="flex-1">
    <!-- Journal Social Actions -->

<div class="flex flex-wrap items-center gap-3 mb-6">

    <!-- LIKE -->

    <form action="journal_like.php" method="POST">

        <input
            type="hidden"
            name="journal"
            value="<?= e($journal) ?>"
        >

        <button
            type="submit"
            class="flex items-center gap-2 px-4 py-2 border border-outline-variant rounded-lg hover:bg-surface-container transition-all <?= $user_liked ? 'bg-red-50 text-red-600' : 'text-on-surface' ?>"
        >

            <span class="material-symbols-outlined">
                favorite
            </span>

            <span>
                <?= $like_count ?>
            </span>

            <span class="hidden sm:inline">
                Like
            </span>

        </button>

    </form>


    <!-- VIEWS -->

    <div class="flex items-center gap-2 px-4 py-2 border border-outline-variant rounded-lg">

        <span class="material-symbols-outlined">
            visibility
        </span>

        <span>
            <?= $view_count ?>
        </span>

        <span class="hidden sm:inline">
            Views
        </span>

    </div>


    <!-- COMMENTS -->

    <a
        href="#comments"
        class="flex items-center gap-2 px-4 py-2 border border-outline-variant rounded-lg hover:bg-surface-container transition-all"
    >

        <span class="material-symbols-outlined">
            chat_bubble
        </span>

        <span>
            <?= $comment_count ?>
        </span>

        <span class="hidden sm:inline">
            Comments
        </span>

    </a>


    <!-- FOLLOW -->

    <form action="journal_follow.php" method="POST">

        <input
            type="hidden"
            name="journal"
            value="<?= e($journal) ?>"
        >

        <button
            type="submit"
            class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg hover:opacity-90 transition-all"
        >

            <span class="material-symbols-outlined">
                star
            </span>

            <?= $user_following ? "Following" : "Follow Journal" ?>

        </button>

    </form>


    <!-- SHARE -->

    <button
        onclick="shareJournal()"
        class="flex items-center gap-2 px-4 py-2 border border-outline-variant rounded-lg hover:bg-surface-container transition-all"
    >

        <span class="material-symbols-outlined">
            share
        </span>

        Share

    </button>

</div>
<span class="text-label-caps font-label-caps text-secondary block mb-2">SCIENTIFIC PERIODICAL</span>
<h1 class="text-display-lg-mobile md:text-display-lg font-display-lg text-primary mb-stack-md leading-tight">
International Journal of Medicine </h1>
<div class="grid grid-cols-2 gap-stack-md py-4 border-y border-outline-variant">
<div>
<p class="text-label-caps font-label-caps text-on-surface-variant">ISSN (PRINT)</p>
<p class="text-code-sm font-code-sm">0028-4793</p>
</div>
<div>
<p class="text-label-caps font-label-caps text-on-surface-variant">ISSN (ONLINE)</p>
<p class="text-code-sm font-code-sm">0028-4793</p>
</div>
<div>
<p class="text-label-caps font-label-caps text-on-surface-variant">IMPACT FACTOR</p>
<p class="text-title-lg font-title-lg text-secondary">4.756 (2025)</p>
</div>
<div>
<p class="text-label-caps font-label-caps text-on-surface-variant">DOI PREFIX</p>
<p class="text-code-sm font-code-sm">10.4659/ijai</p>
</div>
</div>
</div>
</div>
</section>
<!-- Aims & Scope -->
<section class="mb-stack-xl" id="aims">
<h2 class="text-headline-md font-headline-md text-primary mb-stack-md">Aims &amp; Scope</h2>
<p class="text-body-md font-body-md text-on-surface leading-relaxed mb-4">
    The International Journal of Medicine (IJM) provides a premier global forum for the publication of high-quality research advancing medical science, clinical practice, and healthcare innovation. The journal welcomes original research, clinical studies, translational medicine, and evidence-based approaches that improve patient care and public health worldwide.
</p>

<p class="text-body-md font-body-md text-on-surface leading-relaxed">
    Core topics include, but are not limited to, Internal Medicine, Surgery, Cardiology, Oncology, Neurology, Infectious Diseases, Medical Imaging, Public Health, Precision Medicine, and Healthcare Technologies. All submitted manuscripts undergo a rigorous double-blind peer-review process to ensure scientific integrity, clinical relevance, and the highest standards of medical research.
</p>
</section>
<!-- Current Issue Section -->
<section class="mb-stack-xl" id="issues">
<div class="flex justify-between items-end mb-stack-md">
    <div class="flex flex-wrap gap-3 mb-6">

    <a
        href="archive.php?journal=<?= urlencode($journal) ?>"
        class="bg-primary text-on-primary px-5 py-3 rounded-lg font-bold hover:opacity-90 transition-all flex items-center gap-2"
    >

        <span class="material-symbols-outlined">
            menu_book
        </span>

        Read Current Issue

    </a>


    <a
        href="archive.php?journal=<?= urlencode($journal) ?>"
        target="_blank"
        class="border border-primary text-primary px-5 py-3 rounded-lg font-bold hover:bg-surface-container transition-all flex items-center gap-2"
    >

        <span class="material-symbols-outlined">
            download
        </span>

        Download Full Issue PDF

    </a>

</div>
<h2 class="text-headline-md font-headline-md text-primary">Current Issue</h2>
<span class="text-label-caps font-label-caps text-on-surface-variant">Volume 14, Issue 2 • June 2024</span>
</div>
<!-- Article 1 -->
<div class="paper-card bg-surface-container-lowest p-6 border border-outline-variant rounded shadow-sm">
    <span class="text-label-caps font-label-caps text-secondary">RESEARCH ARTICLE</span>

    <h3 class="text-title-lg font-title-lg text-primary mt-2 mb-3">
        Semaglutide and Cardiovascular Outcomes in Obesity without Diabetes
    </h3>

    <p class="text-body-sm font-body-sm italic text-on-surface-variant mb-4">
        A. M. Lincoff, Steven R. Nissen, Jens J. K. Kastelein, and SELECT Trial Investigators
    </p>

    <div class="flex items-center gap-gutter text-code-sm font-code-sm">
        <a class="text-secondary hover:underline flex items-center gap-1"
           href="https://doi.org/10.1056/NEJMoa2307563"
           target="_blank"
           rel="noopener noreferrer">
            <span class="material-symbols-outlined text-[14px]">link</span>
            10.1056/NEJMoa2307563
        </a>

        <span class="text-on-surface-variant opacity-50">|</span>

        <span class="text-on-surface-variant">
            New England Journal of Medicine (2023)
        </span>
    </div>
</div>

<!-- Article 2 -->
<div class="paper-card bg-surface-container-lowest p-6 border border-outline-variant rounded shadow-sm">
    <span class="text-label-caps font-label-caps text-secondary">SYSTEMATIC REVIEW</span>

    <h3 class="text-title-lg font-title-lg text-primary mt-2 mb-3">
        Global Burden of Bacterial Antimicrobial Resistance, 1990–2021: A Systematic Analysis
    </h3>

    <p class="text-body-sm font-body-sm italic text-on-surface-variant mb-4">
        GBD 2021 Antimicrobial Resistance Collaborators
    </p>

    <div class="flex items-center gap-gutter text-code-sm font-code-sm">
        <a class="text-secondary hover:underline flex items-center gap-1"
           href="https://doi.org/10.1016/S0140-6736(24)01867-1"
           target="_blank"
           rel="noopener noreferrer">
            <span class="material-symbols-outlined text-[14px]">link</span>
            10.1016/S0140-6736(24)01867-1
        </a>

        <span class="text-on-surface-variant opacity-50">|</span>

        <span class="text-on-surface-variant">
            The Lancet (2024)
        </span>
    </div>
</div>
<!-- Article 3 -->
<!-- Article 3 -->
<div class="paper-card bg-surface-container-lowest p-6 border border-outline-variant rounded shadow-sm">
    <span class="text-label-caps font-label-caps text-secondary">RESEARCH ARTICLE</span>

    <h3 class="text-title-lg font-title-lg text-primary mt-2 mb-3">
        The Limits of Fair Medical Imaging AI in Real-World Generalization
    </h3>

    <p class="text-body-sm font-body-sm italic text-on-surface-variant mb-4">
        Yuzhe Yang, Haoran Zhang, Judy W. Gichoya, Dina Katabi, Marzyeh Ghassemi et al.
    </p>

    <div class="flex items-center gap-gutter text-code-sm font-code-sm">
        <a class="text-secondary hover:underline flex items-center gap-1"
           href="https://doi.org/10.1038/s41591-024-03113-4"
           target="_blank"
           rel="noopener noreferrer">
            <span class="material-symbols-outlined text-[14px]">link</span>
            10.1038/s41591-024-03113-4
        </a>

        <span class="text-on-surface-variant opacity-50">|</span>

        <span class="text-on-surface-variant">
            Nature Medicine, Vol. 30 (2024)
        </span>
    </div>
</div>
</div>
<button class="mt-stack-lg flex items-center gap-2 text-primary font-bold hover:gap-4 transition-all">
                        View All Past Issues
                        <span class="material-symbols-outlined" data-icon="arrow_forward">arrow_forward</span>
</button>
</section>

</section>


<!-- COMMENTS -->


<section
    class="mb-stack-xl"
    id="comments"
>

    <h2 class="text-headline-md font-headline-md text-primary mb-6">

        Comments

    </h2>


    <?php if ($user_id): ?>

        <form
            action="journal_comment.php"
            method="POST"
            class="mb-8"
        >

            <input
                type="hidden"
                name="journal"
                value="<?= e($journal) ?>"
            >


            <textarea
                name="comment"
                required
                rows="4"
                placeholder="Write your comment about this journal..."
                class="w-full border border-outline-variant bg-surface px-4 py-3 rounded-lg focus:border-primary focus:ring-0"
            ></textarea>


            <button
                type="submit"
                class="mt-3 bg-primary text-on-primary px-6 py-3 rounded-lg font-bold hover:opacity-90"
            >

                Post Comment

            </button>

        </form>

    <?php else: ?>

        <div class="bg-surface-container-low border border-outline-variant p-5 rounded-lg mb-8">

            <p>

                Please sign in to leave a comment.

            </p>

            <a
                href="signin.php"
                class="text-secondary font-bold hover:underline"
            >

                Sign In

            </a>

        </div>

    <?php endif; ?>


    <?php

    $stmt = $conn->prepare("
        SELECT
            journal_comments.*,
            users.fullname
        FROM journal_comments
        JOIN users
        ON journal_comments.user_id = users.id
        WHERE journal_comments.journal = ?
        AND journal_comments.status = 'Published'
        ORDER BY journal_comments.created_at DESC
    ");

    $stmt->bind_param("s", $journal);

    $stmt->execute();

    $comments = $stmt->get_result();

    ?>


    <div class="space-y-5">

        <?php if ($comments->num_rows > 0): ?>

            <?php while ($comment = $comments->fetch_assoc()): ?>

                <div class="border-b border-outline-variant pb-5">

                    <div class="flex justify-between">

                        <strong>
                            <?= e($comment["fullname"]) ?>
                        </strong>

                        <span class="text-sm text-on-surface-variant">

                            <?= date(
                                "d M Y",
                                strtotime($comment["created_at"])
                            ) ?>

                        </span>

                    </div>


                    <p class="mt-2 text-on-surface-variant">

                        <?= nl2br(
                            e($comment["comment"])
                        ) ?>

                    </p>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p class="text-on-surface-variant">

                No comments yet. Be the first to comment.

            </p>

        <?php endif; ?>

    </div>

</section>
<!-- Editorial Board Section -->
<section class="mb-stack-xl" id="editorial">
<h2 class="text-headline-md font-headline-md text-primary mb-stack-md">Editorial Board</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
<div class="flex items-center gap-4 p-4 border border-transparent hover:border-outline-variant transition-colors">
<div class="w-16 h-16 rounded-full overflow-hidden bg-surface-variant">
</div>
<div>
<h4 class="text-title-lg font-title-lg">Aset Aiganym </h4>
<p class="text-body-sm font-body-sm italic text-on-surface-variant">Editor-in-Chief, NIS </p>
</div>
</div>
<div class="flex items-center gap-4 p-4 border border-transparent hover:border-outline-variant transition-colors">
<div class="w-16 h-16 rounded-full overflow-hidden bg-surface-variant">
    <a href="https://photos.google.com/photo/AF1QipM2HDh2JSOoW149vJdh4HpKjxADC1P7hS4QzUcw" target="_blank">
        <img
            class="w-full h-full object-cover"
            src="https://photos.google.com/photo/AF1QipM2HDh2JSOoW149vJdh4HpKjxADC1P7hS4QzUcw"
            alt="Gulshat Sarsenova">
    </a>
</div>
<div>
<h4 class="text-title-lg font-title-lg">Dr.Sarsenova Gulshat</h4>
<p class="text-body-sm font-body-sm italic text-on-surface-variant">Associate Editor, Science Research Univercity SRI </p>
</div>
</div>
<div class="flex items-center gap-4 p-4 border border-transparent hover:border-outline-variant transition-colors">
<div class="w-16 h-16 rounded-full overflow-hidden bg-surface-variant">
</div>
<div>
<h4 class="text-title-lg font-title-lg">Aset Aigerym </h4>
<p class="text-body-sm font-body-sm italic text-on-surface-variant"> Assistent , NIS </p>
</div>
</div>

</div>
</div>
</section>
<!-- Indexing Section -->
<section class="mb-stack-xl pt-stack-lg border-t border-outline-variant">
    <h2 class="text-label-caps font-label-caps text-on-surface-variant text-center mb-stack-lg">
        INDEXED AND ABSTRACTED IN
    </h2>

    <div class="flex flex-wrap justify-center items-center gap-stack-xl grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all">

        <a href="https://www.scopus.com/" target="_blank" rel="noopener noreferrer">
            <div class="h-8 w-24 bg-on-surface-variant/20 rounded flex items-center justify-center font-bold text-sm hover:bg-on-surface-variant/30 transition">
                Scopus
            </div>
        </a>

        <a href="https://www.webofscience.com/" target="_blank" rel="noopener noreferrer">
            <div class="h-8 w-32 bg-on-surface-variant/20 rounded flex items-center justify-center font-bold text-sm hover:bg-on-surface-variant/30 transition">
                Web of Science
            </div>
        </a>

        <a href="https://pubmed.ncbi.nlm.nih.gov/" target="_blank" rel="noopener noreferrer">
            <div class="h-8 w-20 bg-on-surface-variant/20 rounded flex items-center justify-center font-bold text-sm hover:bg-on-surface-variant/30 transition">
                PubMed
            </div>
        </a>

        <a href="https://www.crossref.org/" target="_blank" rel="noopener noreferrer">
            <div class="h-8 w-28 bg-on-surface-variant/20 rounded flex items-center justify-center font-bold text-sm hover:bg-on-surface-variant/30 transition">
                CrossRef
            </div>
        </a>

        <a href="https://scholar.google.com/" target="_blank" rel="noopener noreferrer">
            <div class="h-8 w-24 bg-on-surface-variant/20 rounded flex items-center justify-center font-bold text-sm hover:bg-on-surface-variant/30 transition">
                Google Scholar
            </div>
        </a>

        <a href="https://ui.adsabs.harvard.edu/" target="_blank" rel="noopener noreferrer">
            <div class="h-8 w-28 bg-on-surface-variant/20 rounded flex items-center justify-center font-bold text-sm hover:bg-on-surface-variant/30 transition">
                Harvard ADS
            </div>
        </a>

        <a href="https://www.frontiersin.org/" target="_blank" rel="noopener noreferrer">
            <div class="h-8 w-24 bg-on-surface-variant/20 rounded flex items-center justify-center font-bold text-sm hover:bg-on-surface-variant/30 transition">
                Frontiers
            </div>
        </a>

        <a href="https://www.questel.com/" target="_blank" rel="noopener noreferrer">
            <div class="h-8 w-20 bg-on-surface-variant/20 rounded flex items-center justify-center font-bold text-sm hover:bg-on-surface-variant/30 transition">
                QUEST
            </div>
        </a>
<a href="https://www.proquest.com/" target="_blank" rel="noopener noreferrer">
    <div class="h-8 w-24 bg-on-surface-variant/20 rounded flex items-center justify-center font-bold text-sm hover:bg-on-surface-variant/30 transition">
        ProQuest
    </div>
</a>

    </div>
</section>
</div>
</main>
</div>
<!-- Footer -->
<footer class="bg-surface-container-highest dark:bg-surface-container-lowest border-t border-outline-variant dark:border-outline w-full py-stack-xl">
<div class="px-margin-desktop max-w-container-max mx-auto grid grid-cols-1 md:grid-cols-3 gap-gutter">
<div class="flex flex-col gap-4">
<div class="text-label-caps font-label-caps text-primary dark:text-inverse-primary">Academia Institute</div>
<p class="text-body-sm font-body-sm text-on-surface-variant">Elevating global research standards through rigorous peer review and high-impact publishing since 1924.</p>
<p class="text-body-sm font-body-sm text-on-surface dark:text-on-surface-variant mt-2">© 2024 Academia Research Institute. All rights reserved.</p>
</div>
<div class="flex flex-col gap-2">
<h4 class="text-label-caps font-label-caps text-primary mb-2">Navigation</h4>
<div class="grid grid-cols-2 gap-2">
<a class="text-body-sm font-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#">Ethics</a>
<a class="text-body-sm font-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#">Privacy</a>
<a class="text-body-sm font-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#">Contacts</a>
<a class="text-body-sm font-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#">Crossref</a>
<a class="text-body-sm font-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#">DOI</a>
<a class="text-body-sm font-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#">ORCID</a>
</div>
</div>
<div class="flex flex-col gap-4">
<h4 class="text-label-caps font-label-caps text-primary">Stay Informed</h4>
<div class="flex">
<input class="flex-1 bg-surface border border-outline-variant px-4 py-2 text-body-sm focus:border-primary focus:ring-0" placeholder="Journal newsletter" type="email"/>
<button class="bg-primary text-on-primary px-4 py-2 font-label-caps text-label-caps">Join</button>
</div>
<div class="flex gap-4 opacity-70">
<span class="material-symbols-outlined cursor-pointer hover:text-primary" data-icon="rss_feed">rss_feed</span>
<span class="material-symbols-outlined cursor-pointer hover:text-primary" data-icon="share">share</span>
<span class="material-symbols-outlined cursor-pointer hover:text-primary" data-icon="info">info</span>
</div>
</div>
</div>
</footer>
<script>
        // Micro-interactions and simple state management
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 100,
                        behavior: 'smooth'
                    });

                    // Update active state in sidebar
                    document.querySelectorAll('aside nav a').forEach(a => {
                        a.classList.remove('text-primary', 'font-bold', 'border-l-4', 'border-primary', 'bg-surface-container');
                        a.classList.add('text-on-secondary-container', 'px-4');
                    });
                    this.classList.add('text-primary', 'font-bold', 'border-l-4', 'border-primary', 'bg-surface-container');
                    this.classList.remove('text-on-secondary-container', 'px-4');
                    this.classList.add('pl-4');
                }
            });
        });

        // Sticky header behavior
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            const nav = document.querySelector('nav');
            if (currentScroll > lastScroll && currentScroll > 100) {
                nav.style.transform = 'translateY(-100%)';
            } else {
                nav.style.transform = 'translateY(0)';
            }
            nav.style.transition = 'transform 0.3s ease-in-out';
            lastScroll = currentScroll;
        });
  function shareJournal() {

    const journalTitle =
        "International Journal of Engineering & Applied Physics";

    const journalUrl =
        window.location.href;


    if (navigator.share) {

        navigator.share({

            title: journalTitle,

            text: "Read this scientific journal",

            url: journalUrl

        });

    } else {

        navigator.clipboard.writeText(journalUrl);

        alert("Journal link copied!");

    }

}  </script>
</body></html>