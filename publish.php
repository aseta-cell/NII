<?php
require_once "config.php";

require_login();

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$errors = [
    "fields" => "Please fill in the title, journal and abstract.",
    "file"   => "Please upload the manuscript as PDF, DOC or DOCX (max 20 MB).",
];
$error = $errors[$_GET["error"] ?? ""] ?? "";
$selected_journal = $_GET["journal"] ?? "";
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

            <a href="dashboard.php"
               class="bg-primary text-white px-6 py-2 rounded-lg hover:opacity-90 transition">
                My Account
            </a>

        </div>
</nav>
<main class="max-w-container-max mx-auto px-margin-desktop py-stack-xl">
    <form action="submit_article.php"
      method="POST"
      enctype="multipart/form-data">
<!-- Page Header & Stepper -->
<header class="mb-stack-xl">
<div class="mb-stack-lg">
<h1 class="font-headline-md text-headline-md text-primary mb-2">Submit New Manuscript</h1>
<p class="text-on-surface-variant font-body-md">Fill in the details below and upload your manuscript.</p>
<?php if ($error !== ""): ?>
<p class="mt-4 p-3 rounded-lg bg-red-50 text-red-700 font-body-md"><?= e($error) ?></p>
<?php endif; ?>
</div>
<!-- Horizontal Stepper -->
<div class="flex items-center justify-between py-stack-md overflow-x-auto no-scrollbar">
<div class="flex flex-col items-center gap-2 flex-shrink-0">
<div class="w-8 h-8 rounded-full border border-primary flex items-center justify-center bg-primary text-on-primary">
<span class="material-symbols-outlined text-body-sm">check</span>
</div>
<span class="text-label-caps font-label-caps text-primary">Select Journal</span>
</div>
<div class="stepper-line active"></div>
<div class="flex flex-col items-center gap-2 flex-shrink-0">
<div class="w-8 h-8 rounded-full border-2 border-primary flex items-center justify-center text-primary font-bold">2</div>
<span class="text-label-caps font-label-caps text-primary">Article Info</span>
</div>
<div class="stepper-line"></div>
<div class="flex flex-col items-center gap-2 flex-shrink-0">
<div class="w-8 h-8 rounded-full border border-outline-variant flex items-center justify-center text-on-surface-variant">3</div>
<span class="text-label-caps font-label-caps text-on-surface-variant">Upload</span>
</div>
<div class="stepper-line"></div>
<div class="flex flex-col items-center gap-2 flex-shrink-0">
<div class="w-8 h-8 rounded-full border border-outline-variant flex items-center justify-center text-on-surface-variant">4</div>
<span class="text-label-caps font-label-caps text-on-surface-variant">Confirm</span>
</div>
<div class="stepper-line"></div>
<div class="flex flex-col items-center gap-2 flex-shrink-0">
<div class="w-8 h-8 rounded-full border border-outline-variant flex items-center justify-center text-on-surface-variant">5</div>
<span class="text-label-caps font-label-caps text-on-surface-variant">Payment</span>
</div>
<div class="stepper-line"></div>
<div class="flex flex-col items-center gap-2 flex-shrink-0">
<div class="w-8 h-8 rounded-full border border-outline-variant flex items-center justify-center text-on-surface-variant">6</div>
<span class="text-label-caps font-label-caps text-on-surface-variant">Submit</span>
</div>
</div>
</header>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
<!-- Left Column: Form Content -->
<div class="lg:col-span-8 space-y-stack-xl">
<!-- Section 1: Article Metadata -->
<section class="space-y-stack-md bg-surface-container-lowest p-stack-lg border border-outline-variant rounded-lg">
<h2 class="text-label-caps font-label-caps text-primary uppercase border-b border-outline-variant pb-2">Manuscript Details</h2>
<div class="space-y-stack-md">
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Journal</label>
<select
class="w-full px-4 py-3 bg-transparent border border-outline-variant rounded-lg font-body-md text-primary"
name="journal"
required>
<option value="">Select a journal</option>
<?php foreach ($settings["journals"] as $journal_name): ?>
<option value="<?= e($journal_name) ?>" <?= $selected_journal === $journal_name ? "selected" : "" ?>><?= e($journal_name) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Article Title</label>
<input
class="w-full px-4 py-3 bg-transparent border border-outline-variant rounded-lg font-body-md text-primary"
name="title"
placeholder="Enter full manuscript title"
required
type="text"/> </div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Abstract</label>
<textarea
class="w-full px-4 py-3 bg-transparent border border-outline-variant rounded-lg font-body-md text-primary resize-none"
name="abstract"
placeholder="Provide a concise summary of your research (max 250 words)"
required
rows="6"></textarea>
<div class="flex justify-end">
<span class="text-code-sm font-code-sm text-on-surface-variant">0 / 250 words</span>
</div>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Keywords</label>
<input
class="w-full px-4 py-3 bg-transparent border border-outline-variant rounded-lg font-body-md text-primary"
name="keywords"
placeholder="Separated by commas (e.g. Molecular Biology, Genetics, CRISPR)"
type="text"/>
<p class="text-body-sm font-body-sm text-on-surface-variant italic">Add at least 3-5 keywords to improve indexing.</p>
<div class="flex flex-col gap-1">

<label class="text-label-caps font-label-caps text-on-surface-variant">
Manuscript PDF
</label>

<input
type="file"
name="article_file"
accept=".pdf,.doc,.docx"
required
class="w-full px-4 py-3 bg-transparent border border-outline-variant rounded-lg">

</div> 
</div>
</div>
</section>
<!-- Section 2: Author Information -->
<section class="space-y-stack-md bg-surface-container-lowest p-stack-lg border border-outline-variant rounded-lg">
<div class="flex justify-between items-end border-b border-outline-variant pb-2">
<h2 class="text-label-caps font-label-caps text-primary uppercase">Author Details</h2>
<a href="account-settings.php" class="text-label-caps font-label-caps text-secondary hover:text-primary transition-colors flex items-center gap-1">
<span class="material-symbols-outlined text-[16px]">edit</span> EDIT PROFILE
                        </a>
</div>
<div class="p-stack-md bg-surface-container-low rounded-lg border border-outline-variant border-dashed">
<div class="flex items-center gap-stack-sm mb-4">
<span class="bg-primary text-on-primary text-label-caps font-label-caps px-2 py-0.5 rounded">Primary Author</span>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-stack-md">
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Full Name</label>
<input class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-primary" type="text" value="<?= e($user["fullname"]) ?>" readonly/>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Highest Degree</label>
<select class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-primary">
<option>Ph.D.</option>
<option>M.D.</option>
<option>M.Sc.</option>
<option>B.Sc.</option>
</select>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Institutional Affiliation</label>
<input class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-primary" placeholder="e.g. Stanford University" type="text" value="<?= e($user["affiliation"]) ?>" readonly/>
</div>
<div class="flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">Email Address</label>
<input class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-primary" type="email" value="<?= e($user["email"]) ?>" readonly/>
</div>
<div class="md:col-span-2 flex flex-col gap-1">
<label class="text-label-caps font-label-caps text-on-surface-variant">ORCID iD</label>
<div class="relative">
<input class="w-full pl-12 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-primary" placeholder="0000-0000-0000-0000" type="text"/>
<div class="absolute left-4 top-1/2 -translate-y-1/2 w-6 h-6">
<img class="w-full h-full" data-alt="Official green ORCID icon for identity verification in academic research, minimalist and clear." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDAx9qgGJUOyx0j_YROYCU_JJMwubTM7-vKMpJU_Kjf9hstsOPD8KxLMrFKFTVo8nBIOQNYMZMM9h_RVWCww0BbjdGPIeL-AVSgzMPkTMKLwA6umj7W1HAleT_cesGOGzeE3ACtMkLOc1qrWKMiypghcXTXlZd6F_KepYhlpa90ENnHZIE2yPkv6hXbll1IhJ5OPHMuLrV5VVHlsG9TUNCFjr77WLn3ywMDs84jy4gj-X_nyMvELJg"/>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- Navigation Controls -->
<div class="flex justify-between items-center pt-stack-md">
<a href="dashboard.php" class="px-stack-lg py-3 border border-outline text-primary font-label-caps text-label-caps hover:bg-surface-variant transition-all rounded-lg flex items-center gap-2">
<span class="material-symbols-outlined text-body-md">arrow_back</span> BACK
                    </a>
<div class="flex gap-4">

<button
type="submit"
class="px-stack-lg py-3 bg-primary text-on-primary font-label-caps text-label-caps hover:opacity-90 transition-all rounded-lg flex items-center gap-2">

    SUBMIT ARTICLE

    <span class="material-symbols-outlined text-body-md">
        upload
    </span>

</button>
</div>
</div>
</div>
<!-- Right Column: Sidebar Info -->
<div class="lg:col-span-4 space-y-gutter">
<!-- Guidelines Card -->
<div class="bg-surface-container p-stack-lg border border-outline-variant rounded-lg space-y-4">
<h3 class="text-title-lg font-title-lg text-primary flex items-center gap-2">
<span class="material-symbols-outlined text-secondary">info</span> Guidelines
                    </h3>
<ul class="space-y-3 text-body-sm font-body-sm text-on-surface">
<li class="flex gap-2">
<span class="text-secondary font-bold">•</span>
                            Titles should be descriptive and avoid abbreviations.
                        </li>
<li class="flex gap-2">
<span class="text-secondary font-bold">•</span>
                            The abstract must not exceed 250 words and should summarize the background, methods, results, and conclusions.
                        </li>
<li class="flex gap-2">
<span class="text-secondary font-bold">•</span>
                            ORCID iDs are required for all corresponding authors to ensure proper attribution.
                        </li>
</ul>
<a class="inline-block text-secondary font-label-caps text-label-caps border-b border-secondary hover:text-primary hover:border-primary transition-all" href="#">DOWNLOAD FULL AUTHOR GUIDE</a>
</div>
<!-- Submission Status Card -->
<div class="bg-surface-container-low p-stack-lg border border-outline-variant rounded-lg space-y-4">
<h3 class="text-label-caps font-label-caps text-on-surface-variant uppercase">Selected Journal</h3>
<div class="flex gap-4 items-start">
<div class="w-16 h-20 bg-white border border-outline-variant flex-shrink-0 overflow-hidden">
<img class="w-full h-full object-cover" data-alt="Cover of the Journal of Science, showing an abstract scientific illustration of a DNA helix in navy blue and silver on a white background, professional academic publishing style." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDRNp5HZf93wqIkc8U01ds8nu3--7FC4f9b5kYwatTDqvHlZ6QTNsQ0Fatw691oxa58BSjTOhvbop-tCYphHlo1zJx800I29FxAH3JaEdaQf_zHmZA42o-F8u8LSjnNZ-FM6XfAcBDmpuE9qAdB8E3uyErwvoFKUT4VxhPlMp74vGD2baTLpUuW665x6klHiJkMil-kVrokdeN0VoXCv5xNGJE8dNSxegOVo9D12VbLUEM-Lzm2hyM"/>
</div>
<div>
<p class="font-title-lg text-title-lg text-primary leading-tight mb-1">Journal of Science</p>
<p class="text-code-sm font-code-sm text-secondary">Impact Factor: 4.2</p>
</div>
</div>
<div class="pt-2 border-t border-outline-variant flex justify-between items-center">
<span class="text-body-sm font-body-sm text-on-surface-variant">Submission Fee</span>
<span class="font-headline-md text-[20px] text-primary">$1,200.00</span>
</div>
</div>
<!-- Help Support -->
<div class="p-stack-lg border border-outline-variant rounded-lg bg-surface-container-lowest flex flex-col items-center text-center space-y-3">
<span class="material-symbols-outlined text-secondary text-[32px]">support_agent</span>
<p class="text-body-sm font-body-sm text-on-surface">Need help with your submission?</p>
<a href="about.html" class="block text-center w-full py-2 bg-on-secondary-container text-on-secondary font-label-caps text-label-caps rounded-lg hover:opacity-90">CONTACT EDITORIAL OFFICE</a>
</div>
</div>
</div>
</form>
</main>
<!-- Footer -->
<footer class="bg-surface-container-highest border-t border-outline-variant mt-stack-xl">
<div class="w-full py-stack-xl px-margin-desktop max-w-container-max mx-auto grid grid-cols-1 md:grid-cols-3 gap-gutter">
<div class="flex flex-col gap-2">
<span class="text-label-caps font-label-caps text-primary uppercase">Academia Institute</span>
<p class="text-body-sm font-body-sm text-on-surface-variant max-w-xs">Advancing the boundaries of human knowledge through rigorous peer review and open dissemination of research.</p>
</div>
<div class="flex flex-col gap-4">
<span class="text-label-caps font-label-caps text-on-surface uppercase">Resource Links</span>
<div class="flex flex-wrap gap-x-6 gap-y-2">
<a class="text-on-surface-variant font-label-caps text-label-caps hover:text-primary" href="#">Ethics</a>
<a class="text-on-surface-variant font-label-caps text-label-caps hover:text-primary" href="#">Privacy</a>
<a class="text-on-surface-variant font-label-caps text-label-caps hover:text-primary" href="#">Contacts</a>
<a class="text-on-surface-variant font-label-caps text-label-caps hover:text-primary" href="#">Crossref</a>
<a class="text-on-surface-variant font-label-caps text-label-caps hover:text-primary" href="#">DOI</a>
<a class="text-on-surface-variant font-label-caps text-label-caps hover:text-primary" href="#">ORCID</a>
</div>
</div>
<div class="flex flex-col gap-4 items-start md:items-end">
<p class="text-body-sm font-body-sm text-on-surface-variant md:text-right">© 2024 Academia Research Institute. All rights reserved.</p>
<div class="flex gap-4">
<span class="material-symbols-outlined text-on-surface-variant">language</span>
<span class="material-symbols-outlined text-on-surface-variant">verified_user</span>
</div>
</div>
</div>
</footer>
<script>
        // Micro-interaction for form saving notification
        document.querySelector('button:contains("SAVE DRAFT")')?.addEventListener('click', () => {
            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            btn.innerHTML = 'SAVING...';
            btn.disabled = true;
            setTimeout(() => {
                btn.innerHTML = 'DRAFT SAVED';
                btn.classList.add('text-green-600');
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('text-green-600');
                    btn.disabled = false;
                }, 2000);
            }, 1000);
        });

        // Simple helper for word count logic
        const abstractArea = document.querySelector('textarea');
        const counter = document.querySelector('.text-code-sm');
        abstractArea?.addEventListener('input', (e) => {
            const words = e.target.value.trim().split(/\s+/).filter(w => w.length > 0).length;
            counter.innerText = `${words} / 250 words`;
            if (words > 250) {
                counter.classList.add('text-error');
            } else {
                counter.classList.remove('text-error');
            }
        });
    </script>
</body></html>
