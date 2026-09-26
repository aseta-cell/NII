<?php
require_once "config.php";

require_login();

$user_id = $_SESSION["user_id"];

// Each submission with its latest review
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

$submissions = $stmt->get_result();

$total = $submissions->num_rows;
?>
<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Author Dashboard | Academia Institute</title>
<!-- Material Symbols -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=Playfair+Display:wght@600;700&amp;family=JetBrains+Mono&amp;display=swap" rel="stylesheet"/>
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
        body { background-color: #fbfbfc; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
        }
        .paper-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            transition: border-color 0.2s ease;
        }
        .paper-card:hover {
            border-color: #426087;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d3e4fe; border-radius: 10px; }
    </style>
</head>
<body class="font-body-md text-on-surface">


<!-- TopNavBar -->
<header class="bg-surface border-b border-outline-variant h-20 fixed top-0 left-0 w-full z-50">
<div class="flex justify-between items-center w-full px-margin-desktop max-w-container-max mx-auto h-full">
<div class="text-title-lg font-title-lg font-bold text-primary">Academia Institute</div>
<nav class="hidden md:flex items-center gap-stack-lg">
<a class="text-on-surface-variant font-body-md hover:text-primary transition-colors duration-200" href="about.html">About</a>
<a class="text-on-surface-variant font-body-md hover:text-primary transition-colors duration-200" href="journals.html">Journals</a>
<a class="text-on-surface-variant font-body-md hover:text-primary transition-colors duration-200" href="publish.php">Publish</a>
<a class="text-on-surface-variant font-body-md hover:text-primary transition-colors duration-200" href="authors.html" >Authors</a>
<a class="text-on-surface-variant font-body-md hover:text-primary transition-colors duration-200" href="reviewers.php">Reviewers</a>
<a class="text-on-surface-variant font-body-md hover:text-primary transition-colors duration-200" href="archive.php">Archive</a>
</nav>
<div class="flex items-center gap-4 border-l border-outline-variant pl-stack-md">
<a href="dashboard.php" class="flex items-center gap-stack-sm">

    <span class="material-symbols-outlined text-primary text-3xl">
        account_circle
    </span>

    <span class="hidden lg:block text-label-caps font-label-caps">
<?php echo e($_SESSION["fullname"] ?? $_SESSION["email"] ?? "User"); ?>    </span>

</a>

<a href="logout.php"
class="text-primary hover:underline text-sm">
Logout
</a>

</div>
</header>


<!-- SideNavBar -->
<aside class="bg-surface-container-low border-r border-outline-variant h-screen w-64 fixed left-0 top-0 pt-24 hidden md:flex flex-col gap-stack-md py-stack-lg">
<div class="px-6 mb-stack-md">
<?php echo e($_SESSION["fullname"] ?? $_SESSION["email"] ?? "User"); ?>
<h1 class="text-display-lg-mobile md:text-display-lg font-display-lg text-primary">
Welcome back,
</h1></div>
<nav class="flex flex-col">
<a href="dashboard.php" class="flex items-center gap-4 py-3 text-primary font-bold border-l-4 border-primary pl-4 scale-95 transition-transform">
<span class="material-symbols-outlined" data-icon="dashboard">dashboard</span>
<span class="text-label-caps font-label-caps">Overview</span>
</a>
<a href="my-submissions.php" class="flex items-center gap-4 py-3 text-on-secondary-container px-4 hover:bg-surface-variant transition-all duration-150">
<span class="material-symbols-outlined" data-icon="library_books">library_books</span>
<span class="text-label-caps font-label-caps">My Submissions</span>
</a>
<a href="my-submissions.php" class="flex items-center gap-4 py-3 text-on-secondary-container px-4 hover:bg-surface-variant transition-all duration-150">
<span class="material-symbols-outlined" data-icon="reviews">reviews</span>
<span class="text-label-caps font-label-caps">Peer Reviews</span>
</a>
<a href="billing.php" class="flex items-center gap-4 py-3 text-on-secondary-container px-4 hover:bg-surface-variant transition-all duration-150">
<span class="material-symbols-outlined" data-icon="payments">payments</span>
<span class="text-label-caps font-label-caps">Billing</span>
</a>
<a href="account-settings.php" class="flex items-center gap-4 py-3 text-on-secondary-container px-4 hover:bg-surface-variant transition-all duration-150">
<span class="material-symbols-outlined" data-icon="settings">settings</span>
<span class="text-label-caps font-label-caps">Account Settings</span>
</a>
</nav>
</aside>

<!-- Main Content Canvas -->
<main class="md:pl-64 pt-20">
<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-stack-lg">
<!-- Dashboard Header & Welcome -->
<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-stack-md mb-stack-xl">
<div>

<h1 class="text-display-lg-mobile md:text-display-lg font-display-lg text-primary">
    Welcome back,
    <?php echo e($_SESSION["fullname"] ?? $_SESSION["email"] ?? "Researcher"); ?>
</h1>

<p class="text-body-md font-body-md text-on-surface-variant max-w-xl">
    Monitor your active research submissions, manage correspondence with the editorial board, and track your institutional membership benefits.
</p>

</div>
<div class="flex gap-stack-sm">
<button class="px-6 py-2 border border-primary text-primary text-label-caps font-label-caps rounded hover:bg-primary hover:text-on-primary transition-all">
                        Download Certificate
                    </button>
<a
href="publish.php"
class="px-6 py-2 bg-primary text-on-primary text-label-caps font-label-caps rounded hover:opacity-90 transition-all inline-block">
Submit New Article
</a>
</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
<!-- Main Submissions List (Bento-ish Layout) -->
<div class="lg:col-span-8 space-y-stack-md">
<div class="flex items-center justify-between mb-2">
<h3 class="text-title-lg font-title-lg text-primary">
My Submissions
</h3>

<span class="text-label-caps font-label-caps text-secondary">

<?php echo $submissions->num_rows; ?>

Active Records

</span>
</div>
<!-- Submission Card 1: Revision Required -->
<?php if (isset($_GET["submitted"])): ?>
<div class="p-4 mb-4 rounded bg-green-100 text-green-800">
    Your manuscript was submitted. The editorial office will assign a reviewer.
</div>
<?php endif; ?>

<?php if ($total == 0): ?>

<div class="paper-card p-6 rounded-lg">
    <h3 class="text-title-lg text-primary">
        No submissions yet
    </h3>

    <p class="text-body-sm text-on-surface-variant mt-2">
        You haven't submitted any manuscripts.
    </p>

    <a href="publish.php"
       class="inline-block mt-4 px-5 py-2 bg-primary text-white rounded">
        Submit your first article
    </a>
</div>

<?php else: ?>

<?php while($row = $submissions->fetch_assoc()): ?>

<div class="paper-card p-6 rounded-lg">

<div class="flex justify-between">

<?php

$status = $row["status"];
$color = "bg-secondary-container";

if ($status == "Accepted" || $status == "Paid") {
    $color = "bg-green-200";
}

elseif ($status == "Under Review") {
    $color = "bg-yellow-200";
}

elseif ($status == "Rejected" || $status == "Revision") {
    $color = "bg-red-200";
}

elseif ($status == "Published") {
    $color = "bg-blue-200";
}

?>

<span class="px-3 py-1 rounded-full <?= $color ?>">
    <?= e($status) ?>
</span>

<span>

<?= date("d M Y", strtotime($row["created_at"])) ?>

</span>

</div>

<h3 class="text-headline-md mt-4">

<?= e($row["title"]) ?>

</h3>

<p class="mt-2 text-on-surface-variant">

<?= e($row["journal"]) ?>

</p>

<?php if (!empty($row["filename"])): ?>
<a
href="<?= e(upload_url($row["filename"])) ?>"
target="_blank"
class="text-primary mt-4 inline-block">

View Manuscript

</a>
<?php endif; ?>

<?php if ($status == "Accepted" || $status == "Payment Pending"): ?>
<a
href="payment.php?id=<?= (int)$row["id"] ?>"
class="inline-block mt-4 ml-4 px-5 py-2 bg-primary text-white rounded">

Pay publication fee

</a>
<?php elseif ($status == "Published"): ?>
<a
href="article.php?id=<?= (int)$row["id"] ?>"
class="inline-block mt-4 ml-4 text-primary font-bold">

Open published article →

</a>
<?php endif; ?>
<?php if(!empty($row["review_comment"])): ?>

<div class="mt-4 p-4 bg-surface-container rounded">

<p class="font-bold">
Reviewer Comment:
</p>

<p>
<?= e($row["review_comment"]) ?>
</p>

</div>

<?php endif; ?>
</div>

<?php endwhile; ?>

<?php endif; ?>
</div>
</div>
<!-- Sidebar Content -->
<div class="lg:col-span-4 space-y-gutter">
<!-- Membership Status Widget -->
<div class="paper-card p-6 rounded-lg relative overflow-hidden">
<!-- Subtle Gold Top Border for Professional Tier -->
<div class="absolute top-0 left-0 w-full h-1 bg-[#c5a059]"></div>
<div class="flex justify-between items-center mb-6">
<h3 class="text-label-caps font-label-caps text-secondary">MEMBERSHIP STATUS</h3>
<span class="material-symbols-outlined text-[#c5a059]" data-icon="verified" style="font-variation-settings: 'FILL' 1;">verified</span>
</div>
<div class="mb-6">
<p class="text-headline-md font-headline-md text-primary">Professional</p>
<p class="text-body-sm font-body-sm text-on-surface-variant">University of Zurich Institutional Tier</p>
</div>
<div class="space-y-3 mb-6">
<div class="flex items-center gap-2 text-body-sm">
<span class="material-symbols-outlined text-success text-sm" data-icon="check_circle">check_circle</span>
<span>Unlimited Submissions</span>
</div>
<div class="flex items-center gap-2 text-body-sm">
<span class="material-symbols-outlined text-success text-sm" data-icon="check_circle">check_circle</span>
<span>Priority Peer Review (14-day turnaround)</span>
</div>
<div class="flex items-center gap-2 text-body-sm">
<span class="material-symbols-outlined text-success text-sm" data-icon="check_circle">check_circle</span>
<span>Open Access Waiver (100% covered)</span>
</div>
</div>
<button class="w-full py-2 border border-outline text-on-surface text-label-caps font-label-caps rounded hover:bg-surface-container transition-all">
                            MANAGE SUBSCRIPTION
                        </button>
</div>
<!-- Pending Actions / Fees -->
<div class="paper-card p-6 rounded-lg bg-surface-container-low">
<h3 class="text-label-caps font-label-caps text-primary mb-6">PENDING ACTIONS</h3>
<div class="space-y-4">
<div class="flex items-start gap-4">
<div class="w-8 h-8 rounded-full bg-error-container flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-error text-lg" data-icon="priority_high">priority_high</span>
</div>
<div>
<p class="text-body-sm font-semibold">APC Fee Outstanding</p>
<p class="text-body-sm text-on-surface-variant mb-2">Manuscript #AS-11928 requires payment.</p>
<button class="text-label-caps font-label-caps text-primary hover:underline">PAY FEES ($450.00)</button>
</div>
</div>
<div class="flex items-start gap-4">
<div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-secondary text-lg" data-icon="edit_note">edit_note</span>
</div>
<div>
<p class="text-body-sm font-semibold">Sign Copyright Transfer</p>
<p class="text-body-sm text-on-surface-variant mb-2">Required for 'Heuristic Modeling...'</p>
<button class="text-label-caps font-label-caps text-primary hover:underline">SIGN DOCUMENT</button>
</div>
</div>
</div>
</div>
<!-- Journal Analytics / Quick Info -->
<div class="p-6">
<h3 class="text-label-caps font-label-caps text-secondary mb-4">YOUR IMPACT</h3>
<div class="grid grid-cols-2 gap-4">
<div class="text-center p-4 border-r border-outline-variant">
<p class="text-headline-md font-headline-md text-primary">14</p>
<p class="text-[10px] font-label-caps text-on-surface-variant">PUBLICATIONS</p>
</div>
<div class="text-center p-4">
<p class="text-headline-md font-headline-md text-primary">312</p>
<p class="text-[10px] font-label-caps text-on-surface-variant">CITATIONS</p>
</div>
</div>
</div>
</div>
</div>
</div>
</main>
<!-- Footer -->
<footer class="bg-surface-container-highest border-t border-outline-variant mt-stack-xl">
<div class="w-full py-stack-xl px-margin-desktop max-w-container-max mx-auto grid grid-cols-1 md:grid-cols-3 gap-gutter">
<div class="space-y-4">
<div class="text-label-caps font-label-caps text-primary">ACADEMIA RESEARCH INSTITUTE</div>
<p class="text-body-sm font-body-sm text-on-surface-variant">Advancing the frontiers of human knowledge through rigorous peer-review and open access publishing.</p>
</div>
<div class="flex flex-col gap-2">
<span class="text-label-caps font-label-caps text-primary mb-2">QUICK LINKS</span>
<a class="text-body-sm font-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#">Ethics &amp; Standards</a>
<a class="text-body-sm font-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#">Privacy Policy</a>
<a class="text-body-sm font-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#">Contact Editorial Office</a>
</div>
<div class="flex flex-col gap-4">
<span class="text-label-caps font-label-caps text-primary mb-2">PARTNERS</span>
<div class="flex flex-wrap gap-4 opacity-60 grayscale hover:grayscale-0 transition-all">
<span class="text-label-caps font-label-caps border border-outline-variant px-2 py-1">CROSSREF</span>
<span class="text-label-caps font-label-caps border border-outline-variant px-2 py-1">ORCID</span>
<span class="text-label-caps font-label-caps border border-outline-variant px-2 py-1">DOI</span>
</div>
<p class="text-body-sm font-body-sm text-on-surface-variant pt-4">© 2024 Academia Research Institute. All rights reserved.</p>
</div>
</div>
</footer>
<!-- Mobile Nav Anchor -->
<div class="md:hidden fixed bottom-0 left-0 w-full bg-surface border-t border-outline-variant flex justify-around py-4 z-50">
<button class="material-symbols-outlined text-primary" data-icon="dashboard">dashboard</button>
<button class="material-symbols-outlined text-on-surface-variant" data-icon="library_books">library_books</button>
<button class="material-symbols-outlined text-on-surface-variant" data-icon="add_circle" style="font-size: 32px;">add_circle</button>
<button class="material-symbols-outlined text-on-surface-variant" data-icon="notifications">notifications</button>
<button class="material-symbols-outlined text-on-surface-variant" data-icon="account_circle">account_circle</button>
</div>
<script>
        // Simple Interaction logic
        document.querySelectorAll('.paper-card').forEach(card => {
            card.addEventListener('click', () => {
                // Future expansion: open detailed view
                console.log('Card clicked: Handling navigation to details.');
            });
        });
    </script>
</body></html>