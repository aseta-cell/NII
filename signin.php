<?php
require_once "config.php";
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE email = ?"
    );
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    
        if (password_verify($password, $user["password"])) {
session_regenerate_id(true);
$_SESSION["user_id"] = $user["id"];
$_SESSION["fullname"] = $user["fullname"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];

switch ($user["role"]) {

    case "editor":
        redirect("editor-dashboard.php");

    case "reviewer":
        redirect("reviewer-dashboard.php");

    default:
        redirect("dashboard.php");
}
        } 
        else {
            $error = "Incorrect password.";
        }
    } 
    else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Sign In | Scholarly Insights - Academia Institute</title>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=JetBrains+Mono&amp;family=Playfair+Display:wght@700&amp;display=swap" rel="stylesheet"/>
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
                    "tertiary-fixed-dim": "#ffb691",
                    "inverse-on-surface": "#eaf1ff",
                    "outline-variant": "#c4c6cf",
                    "on-primary-fixed-variant": "#2d476f",
                    "error-container": "#ffdad6",
                    "on-background": "#0b1c30",
                    "background": "#f8f9ff",
                    "on-secondary-fixed-variant": "#2a486e",
                    "primary-fixed-dim": "#aec7f6",
                    "on-primary": "#ffffff",
                    "on-surface-variant": "#44474e",
                    "surface": "#f8f9ff",
                    "primary-fixed": "#d6e3ff",
                    "secondary-fixed": "#d3e3ff",
                    "on-surface": "#0b1c30",
                    "on-secondary": "#ffffff",
                    "inverse-primary": "#aec7f6",
                    "secondary-container": "#b3d1fe",
                    "on-secondary-fixed": "#001c39",
                    "on-primary-fixed": "#001b3d",
                    "secondary-fixed-dim": "#abc8f5",
                    "surface-container-lowest": "#ffffff",
                    "primary": "#000a1e",
                    "surface-container-high": "#dce9ff",
                    "surface-variant": "#d3e4fe",
                    "surface-tint": "#465f88",
                    "tertiary-fixed": "#ffdbcb",
                    "error": "#ba1a1a",
                    "inverse-surface": "#213145",
                    "tertiary": "#180500",
                    "on-secondary-container": "#3c5980",
                    "on-primary-container": "#708ab5",
                    "surface-bright": "#f8f9ff",
                    "secondary": "#426087",
                    "primary-container": "#002147",
                    "on-error": "#ffffff",
                    "surface-container": "#e5eeff",
                    "surface-dim": "#cbdbf5",
                    "on-tertiary-container": "#b97958",
                    "on-error-container": "#93000a",
                    "surface-container-low": "#eff4ff",
                    "on-tertiary-fixed": "#341100",
                    "surface-container-highest": "#d3e4fe",
                    "outline": "#74777f",
                    "tertiary-container": "#3d1500",
                    "on-tertiary-fixed-variant": "#6c391d",
                    "on-tertiary": "#ffffff"
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
                    "margin-desktop": "64px",
                    "container-max": "1200px",
                    "stack-md": "16px",
                    "stack-sm": "8px",
                    "stack-lg": "32px",
                    "gutter": "24px"
            },
            "fontFamily": {
                    "code-sm": ["JetBrains Mono"],
                    "body-md": ["Inter"],
                    "display-lg": ["Playfair Display"],
                    "title-lg": ["Inter"],
                    "label-caps": ["Inter"],
                    "display-lg-mobile": ["Playfair Display"],
                    "headline-md": ["Playfair Display"],
                    "body-sm": ["Inter"]
            },
            "fontSize": {
                    "code-sm": ["13px", {"lineHeight": "20px", "fontWeight": "400"}],
                    "body-md": ["16px", {"lineHeight": "26px", "fontWeight": "400"}],
                    "display-lg": ["48px", {"lineHeight": "60px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "title-lg": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                    "label-caps": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                    "display-lg-mobile": ["32px", {"lineHeight": "40px", "fontWeight": "700"}],
                    "headline-md": ["30px", {"lineHeight": "38px", "fontWeight": "600"}],
                    "body-sm": ["14px", {"lineHeight": "22px", "fontWeight": "400"}]
            }
          },
        },
      }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .academic-gradient {
            background: linear-gradient(135deg, #002147 0%, #0b1c30 100%);
        }
        .fade-in { animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
<body class="bg-surface font-body-md text-on-surface selection:bg-secondary-container">
<!-- TopNavBar -->
<header class="bg-surface border-b border-outline-variant h-20 fixed top-0 w-full z-50">
<nav class="flex justify-between items-center w-full px-margin-desktop max-w-container-max mx-auto h-full">
<div class="flex items-center gap-10">
<a class="font-display-lg text-display-lg text-primary" href="about.html">Scholarly Insights</a>
<div class="hidden md:flex gap-8 items-center">
<a class="font-label-caps text-label-caps text-on-surface-variant hover:text-primary transition-colors duration-200" href="journals.html">Journals</a>
<a class="font-label-caps text-label-caps text-on-surface-variant hover:text-primary transition-colors duration-200" href="archive.php">Archive</a>
<a class="font-label-caps text-label-caps text-on-surface-variant hover:text-primary transition-colors duration-200" href="my-submissions.php">Submissions</a>
<a class="font-label-caps text-label-caps text-on-surface-variant hover:text-primary transition-colors duration-200" href="archive.php">Directory</a>
</div>
</div>
<div class="flex items-center gap-6">

    <a href="institutional.html"
       class="bg-primary-container text-on-primary-container px-5 py-2 
              font-label-caps text-label-caps rounded-lg 
              hover:opacity-90 transition-all active:scale-95">
        Institutional Access
    </a>


    <a href="dashboard.php"
       class="flex items-center gap-2 text-primary hover:opacity-80 transition">

        <span class="material-symbols-outlined text-3xl">
            account_circle
        </span>

        <span class="hidden lg:block font-title-lg text-title-lg">
            Your Name
        </span>

    </a>

</div>
</nav>
</header>
<main class="min-h-screen pt-20 flex flex-col items-center justify-center relative">
<!-- Split Layout / Hero Section -->
<div class="w-full max-w-[1400px] grid lg:grid-cols-2 min-h-[calc(100vh-80px)] overflow-hidden">
<!-- Left Side: Visual Anchor -->
<div class="hidden lg:block relative overflow-hidden bg-primary-container">
<div class="absolute inset-0 z-10 academic-gradient opacity-40"></div>
<div class="h-full w-full bg-cover bg-center" data-alt="A grand, hyper-modern university library at dusk with floor-to-ceiling glass walls. The interior glows with warm light, showcasing rows of clean white shelving and minimalist reading pods. Outside, the sky is a deep indigo, matching the Academia Institute navy palette. The atmosphere is quiet, prestigious, and intellectually focused, with sharp architectural lines reflecting the academic minimalism style." style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuD0P6s8CfQue0biQ2GOITopg9f3_Rci_Pu-4vL98Yx954kc1AFMqSBV6DZMxzcrtvZ9YuQ3iT1h5nSM24vYuSNZxvCZ8_OCQDXiG818mLkcPm3lS312Xp-iLVZu4aIWGm0lhg7vYrx561FyI4saVbyp8q9YEs8ZxHrGBaqRi82kD2hOIZr337ekk_VgCiiLy_C2z89A3-A0fFWCAbm74QhN1Rv8LV4h9nI7AT8VGF8oOw7isMiMiH4')"></div>
<div class="absolute bottom-margin-desktop left-margin-desktop z-20 max-w-md text-white">
<h2 class="font-display-lg text-display-lg mb-4">Empowering the world's leading minds.</h2>
<p class="font-body-md text-on-primary-container opacity-90">Join a network of over 10,000 institutions contributing to the global repository of peer-reviewed research and scholarly excellence.</p>
</div>
</div>
<!-- Right Side: Sign In Form -->
<div class="flex flex-col items-center justify-center p-8 lg:p-margin-desktop bg-surface-bright">
<div class="w-full max-w-md fade-in">
<div class="mb-stack-lg">
<span class="font-label-caps text-label-caps text-secondary tracking-widest block mb-2">ACADEMIA INSTITUTE</span>
<h1 class="font-headline-md text-headline-md text-primary mb-2">Welcome Back</h1>
<p class="text-on-surface-variant font-body-sm">Access your scholarly dashboard and active peer reviews.</p>
</div>
<!-- Institutional Login Section (Primary) -->
<div class="mb-stack-lg p-6 bg-white border border-outline-variant rounded-lg">
<h3 class="font-title-lg text-title-lg text-primary mb-4 flex items-center gap-2">
<span class="material-symbols-outlined text-secondary" data-icon="account_balance">account_balance</span>
                            Institutional Access
                        </h3>
<p class="text-body-sm text-on-surface-variant mb-4">Use your university or research center credentials for immediate full-access.</p>
<div class="relative group mb-4">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline" data-icon="search">search</span>
<input class="w-full pl-10 pr-4 py-3 bg-surface border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none font-body-sm" placeholder="Search for your institution..." type="text"/>
</div>
<button class="w-full bg-primary-container text-white py-4 font-label-caps text-label-caps rounded-lg hover:bg-primary transition-all flex items-center justify-center gap-2">
                            Sign in via your Institution
                            <span class="material-symbols-outlined text-[18px]" data-icon="login">login</span>
</button>
<div class="mt-4 text-center">
<a class="text-secondary font-label-caps text-[10px] hover:underline" href="#">REQUEST INSTITUTIONAL ACCESS</a>
</div>
</div>
<div class="flex items-center gap-4 mb-stack-lg">
<div class="h-[1px] bg-outline-variant flex-1"></div>
<span class="font-label-caps text-[10px] text-outline">OR INDIVIDUAL LOGIN</span>
<div class="h-[1px] bg-outline-variant flex-1"></div>
</div>
<!-- Individual Login Section -->
<?php if ($error !== ""): ?>
<p class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 font-body-sm"><?= e($error) ?></p>
<?php endif; ?>
<form
class="space-y-4"
action="signin.php"
method="POST">
<div>
<label class="block font-label-caps text-[11px] text-primary mb-1">EMAIL ADDRESS</label>
<input
class="w-full px-4 py-3 bg-white border border-outline-variant rounded-lg focus:ring-1 focus:ring-secondary focus:border-secondary outline-none font-body-sm transition-all"
placeholder="dr.rossi@institute.edu"
type="email"
name="email"
value="<?= e($_POST["email"] ?? "") ?>"
required>
</div>
<div>
<div class="flex justify-between items-center mb-1">
<label class="block font-label-caps text-[11px] text-primary">PASSWORD</label>
<a class="text-secondary font-label-caps text-[10px] hover:underline" href="#">FORGOT PASSWORD?</a>
</div>
<input
class="w-full px-4 py-3 bg-white border border-outline-variant rounded-lg focus:ring-1 focus:ring-secondary focus:border-secondary outline-none font-body-sm transition-all"
placeholder="••••••••"
type="password"
name="password"
required>  
</div>
<button
    type="submit"
    class="w-full border border-primary text-primary py-4 font-label-caps text-label-caps rounded-lg hover:bg-primary hover:text-white transition-all">
    Sign In to Individual Account
</button>
<p style="margin-top:20px; text-align:center;">

Don't have an account?

<a href="register.php">

Register

</a>

</p>
</form>
<p class="mt-stack-lg text-center font-body-sm text-on-surface-variant">
                        New to Scholarly Insights? 
                        <a class="text-primary font-bold hover:underline" href="register.php">Create an Account</a>
</p>
</div>
</div>
</div>
</main>
<!-- Footer -->
<footer class="bg-primary text-on-primary w-full py-stack-xl mt-0 border-t border-primary-container">
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter px-margin-desktop max-w-container-max mx-auto">
<div class="col-span-1 lg:col-span-1">
<h4 class="font-display-lg text-display-lg text-on-primary mb-4">Scholarly Insights</h4>
<p class="text-body-sm text-on-primary-container opacity-80 mb-6">Advancing human knowledge through rigorous peer review and global scholarly distribution.</p>
</div>
<div>
<h5 class="font-label-caps text-label-caps mb-4 text-tertiary-fixed-dim">Research Tools</h5>
<ul class="space-y-2">
<li><a class="text-body-sm text-on-primary-container hover:text-tertiary-fixed-dim transition-colors" href="#">Ethics &amp; Policies</a></li>
<li><a class="text-body-sm text-on-primary-container hover:text-tertiary-fixed-dim transition-colors" href="#">Open Access</a></li>
<li><a class="text-body-sm text-on-primary-container hover:text-tertiary-fixed-dim transition-colors" href="#">DOI Foundation</a></li>
</ul>
</div>
<div>
<h5 class="font-label-caps text-label-caps mb-4 text-tertiary-fixed-dim">Institutional</h5>
<ul class="space-y-2">
<li><a class="text-body-sm text-on-primary-container hover:text-tertiary-fixed-dim transition-colors" href="#">Library Partners</a></li>
<li><a class="text-body-sm text-on-primary-container hover:text-tertiary-fixed-dim transition-colors" href="#">Board of Governors</a></li>
<li><a class="text-body-sm text-on-primary-container hover:text-tertiary-fixed-dim transition-colors" href="#">Contact</a></li>
</ul>
</div>
<div>
<h5 class="font-label-caps text-label-caps mb-4 text-tertiary-fixed-dim">Legal</h5>
<ul class="space-y-2">
<li><a class="text-body-sm text-on-primary-container hover:text-tertiary-fixed-dim transition-colors" href="#">Terms of Service</a></li>
<li><a class="text-body-sm text-on-primary-container hover:text-tertiary-fixed-dim transition-colors" href="#">Privacy Policy</a></li>
</ul>
</div>
</div>
<div class="px-margin-desktop max-w-container-max mx-auto mt-12 pt-8 border-t border-primary-container">
<p class="font-body-sm text-on-primary-container text-center lg:text-left opacity-60">© 2024 Global Research Institute Publishing House. All rights reserved.</p>
</div>
</footer>
<script>
        // Simple micro-interaction for institutional search
        const institutionalInput = document.querySelector('input[type="text"]');
        institutionalInput.addEventListener('focus', () => {
            institutionalInput.parentElement.classList.add('scale-[1.01]');
        });
        institutionalInput.addEventListener('blur', () => {
            institutionalInput.parentElement.classList.remove('scale-[1.01]');
        });

        // Form submission prevention for demo
    
    </script>
</body></html>