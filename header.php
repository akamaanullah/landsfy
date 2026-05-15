<?php 
if (session_status() === PHP_SESSION_NONE) session_start(); 
require_once __DIR__ . '/includes/database/db.php';

// --- DYNAMIC SEO LOGIC ---
$current_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($current_uri, PHP_URL_PATH);
// Get the relative path (remove base directory if necessary)
// For this project, we assume it runs in root or subfolder
$base_path = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
$relative_path = str_replace($base_path, '', $path);
$relative_path = trim($relative_path, '/');

// Default values (Home)
$meta_title = "Landsfy | Buy, Sell & Rent Property in Pakistan";
$meta_desc = "Landsfy helps you find your favorite homes across Pakistan. Explore verified houses, plots, and commercial properties for sale and rent today.";

// Routing Logic for Meta Tags
if (empty($relative_path) || $relative_path == 'index') {
    // Home stays default
} elseif ($relative_path == 'properties') {
    $meta_title = "Properties for Sale & Rent in Pakistan | Landsfy";
    $meta_desc = "Browse verified property listings across Pakistan. Find houses, plots, apartments, and commercial spaces on Landsfy.";
    if (isset($_GET['purpose'])) {
        $purpose = ucfirst($_GET['purpose']);
        $meta_title = "Properties for $purpose in Pakistan | Landsfy";
    }
} elseif (strpos($relative_path, 'properties/') === 0) {
    $slug = str_replace('properties/', '', $relative_path);
    $stmt = $pdo->prepare("SELECT title FROM properties WHERE slug = ?");
    $stmt->execute([$slug]);
    $prop = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($prop) {
        $meta_title = $prop['title'] . " | Landsfy Pakistan";
        $meta_desc = "Check out " . $prop['title'] . " on Landsfy. View price, location, photos, and features of this property.";
    }
} elseif ($relative_path == 'agencies') {
    $meta_title = "Verified Real Estate Agencies in Pakistan | Landsfy";
    $meta_desc = "Connect with top real estate agencies in Pakistan. Browse verified agency profiles and their active property listings.";
} elseif (strpos($relative_path, 'agencies/') === 0) {
    $slug = str_replace('agencies/', '', $relative_path);
    $stmt = $pdo->prepare("SELECT name FROM agencies WHERE slug = ?");
    $stmt->execute([$slug]);
    $agency = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($agency) {
        $meta_title = $agency['name'] . " - Real Estate Agency | Landsfy";
        $meta_desc = "View property listings and agent details for " . $agency['name'] . ". Trusted real estate services on Landsfy.";
    }
} elseif ($relative_path == 'agents') {
    $meta_title = "Expert Real Estate Agents in Pakistan | Landsfy";
    $meta_desc = "Find verified real estate agents to help you buy or sell property. Expert guidance for your next property investment.";
} elseif (strpos($relative_path, 'agents/') === 0) {
    $slug = str_replace('agents/', '', $relative_path);
    $stmt = $pdo->prepare("SELECT u.full_name FROM agents ag JOIN users u ON ag.user_id = u.id WHERE ag.slug = ?");
    $stmt->execute([$slug]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($agent) {
        $meta_title = $agent['full_name'] . " - Professional Real Estate Agent | Landsfy";
        $meta_desc = "Connect with " . $agent['full_name'] . " for expert property advice and verified listings in Pakistan.";
    }
} elseif ($relative_path == 'blog') {
    $meta_title = "Real Estate Blog & News Pakistan | Landsfy";
    $meta_desc = "Stay updated with the latest real estate trends, investment guides, and market news in Pakistan.";
} elseif (strpos($relative_path, 'blog/') === 0) {
    $slug = str_replace('blog/', '', $relative_path);
    $stmt = $pdo->prepare("SELECT title FROM blogs WHERE slug = ?");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
    if ($post) {
        $meta_title = $post['title'] . " | Landsfy Blog";
        $meta_desc = mb_strimwidth(strip_tags($post['title']), 0, 160, "...");
    }
} elseif ($relative_path == 'about') {
    $meta_title = "About Us - Landsfy Real Estate Platform";
    $meta_desc = "Learn more about Landsfy, Pakistan's emerging real estate marketplace dedicated to transparency and trust.";
} elseif ($relative_path == 'contact') {
    $meta_title = "Contact Us | Landsfy Support";
    $meta_desc = "Have questions? Get in touch with the Landsfy team for assistance with property listings or account support.";
} elseif ($relative_path == 'login') {
    $meta_title = "Sign In to Your Account | Landsfy";
} elseif ($relative_path == 'register') {
    $meta_title = "Create a New Account | Landsfy";
}

// Canonical
$canonical_url = "https://www.landsfy.com/" . $relative_path;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $meta_title; ?></title>
    <meta name="description" content="<?php echo $meta_desc; ?>">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>includes/assets/images/favicon.png">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $meta_title; ?>">
    <meta property="og:description" content="<?php echo $meta_desc; ?>">
    <meta property="og:url" content="<?php echo $canonical_url; ?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?php echo $base_path; ?>includes/assets/images/logo.png">

    <!-- Design System & Fonts -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>includes/assets/css/premium-core.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        window.USER_ROLE = "<?php echo $_SESSION['role_name'] ?? ''; ?>";
        window.IS_LOGGED_IN = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        window.BASE_PATH = "<?php echo $base_path; ?>";
    </script>
    <script src="<?php echo $base_path; ?>includes/assets/js/utils.js"></script>
</head>
<body>

    <!-- Scroll To Top -->
    <div class="scroll-top" id="scrollTop">
        <i class="fa-solid fa-chevron-up"></i>
    </div>

    <!-- ===== TOP UTILITY BAR ===== -->
    <div class="top-utility-bar">
        <div class="container">
            <div class="tub-left">
                <a href="blog" class="tub-link"><i class="fa-solid fa-newspaper"></i> Blog</a>
                <a href="agencies" class="tub-link"><i class="fa-solid fa-building"></i> Agencies</a>
            </div>
            <div class="tub-right">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php 
                        $role_path = $_SESSION['role_name'] ?? 'buyer';
                        if ($role_path === 'agency_owner') $role_path = 'agency';
                    ?>
                    <span class="tub-link" style="font-weight: 700; color: #ffffff; margin-right: 10px;">
                        <i class="fa-solid fa-circle-user"></i> Hi, <?php echo explode(' ', $_SESSION['full_name'])[0]; ?>
                    </span>
                    <a href="<?php echo $role_path; ?>/" class="tub-link"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a>
                    <a href="logout" class="tub-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                <?php else: ?>
                    <a href="login" class="tub-link"><i class="fa-solid fa-circle-arrow-right"></i> Sign In</a>
                    <a href="register" class="tub-link tub-register"><i class="fa-solid fa-user-plus"></i> Register</a>
                <?php endif; ?>

                <?php 
                $show_add = true;
                $add_url = 'login';
                
                if (isset($_SESSION['user_id'])) {
                    $u_role = $_SESSION['role_name'] ?? '';
                    
                    if ($u_role === 'buyer') {
                        $show_add = false;
                    } elseif ($u_role === 'seller') {
                        $add_url = 'seller/add-listing';
                    } elseif ($u_role === 'agency_owner') {
                        $add_url = 'agency/add-property';
                    } elseif ($u_role === 'agent') {
                        $add_url = 'agent/add-property';
                    } elseif ($u_role === 'admin') {
                        $add_url = 'admin/add-property';
                    } else {
                        $add_url = 'index';
                    }
                }
                ?>
                <?php if ($show_add): ?>
                <a href="<?php echo $add_url; ?>" class="tub-add-btn">
                    <i class="fa-solid fa-plus"></i> Add Property
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===== MAIN HEADER ===== -->
    <header class="header" id="mainHeader">
        <div class="container">

            <!-- Logo -->
            <a href="<?php echo $base_path; ?>index" class="logo">
                <img src="<?php echo $base_path; ?>includes/assets/images/logo.png" alt="Landsfy"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span class="logo-text-fallback" style="display:none;">Landsfy</span>
            </a>

            <!-- Main Navigation -->
            <nav class="main-nav">

                <!-- All Properties -->
                <div class="nav-item">
                    <a href="<?php echo $base_path; ?>properties" class="nav-link">Properties</a>
                </div>

                <!-- BUY Dropdown -->
                <div class="nav-item has-dropdown">
                    <a href="<?php echo $base_path; ?>properties?purpose=sell" class="nav-link">
                        Buy <i class="fa-solid fa-caret-down nav-caret"></i>
                    </a>
                    <div class="nav-dropdown">
                        <a href="<?php echo $base_path; ?>properties?purpose=sell&type=home" class="dropdown-link">
                            <i class="fa-solid fa-house-chimney"></i>
                            <div>
                                <span class="dl-title">Homes</span>
                                <span class="dl-sub">Houses, Flats, Bungalows</span>
                            </div>
                        </a>
                        <a href="<?php echo $base_path; ?>properties?purpose=sell&type=plot" class="dropdown-link">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <div>
                                <span class="dl-title">Plots</span>
                                <span class="dl-sub">Residential & Commercial</span>
                            </div>
                        </a>
                        <a href="<?php echo $base_path; ?>properties?purpose=sell&type=commercial" class="dropdown-link">
                            <i class="fa-solid fa-building"></i>
                            <div>
                                <span class="dl-title">Commercial</span>
                                <span class="dl-sub">Offices, Shops, Warehouses</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- RENT Dropdown -->
                <div class="nav-item has-dropdown">
                    <a href="<?php echo $base_path; ?>properties?purpose=rent" class="nav-link">
                        Rent <i class="fa-solid fa-caret-down nav-caret"></i>
                    </a>
                    <div class="nav-dropdown">
                        <a href="<?php echo $base_path; ?>properties?purpose=rent&type=home" class="dropdown-link">
                            <i class="fa-solid fa-house-chimney"></i>
                            <div>
                                <span class="dl-title">Homes for Rent</span>
                                <span class="dl-sub">Houses & Apartments</span>
                            </div>
                        </a>
                        <a href="<?php echo $base_path; ?>properties?purpose=rent&type=commercial" class="dropdown-link">
                            <i class="fa-solid fa-building"></i>
                            <div>
                                <span class="dl-title">Commercial Rent</span>
                                <span class="dl-sub">Offices & Shops</span>
                            </div>
                        </a>
                    </div>
                </div>

                <a href="<?php echo $base_path; ?>agents" class="nav-link">Agents</a>
                <a href="<?php echo $base_path; ?>about" class="nav-link">About Us</a>
                <a href="<?php echo $base_path; ?>contact" class="nav-link">Contact</a>

            </nav>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fa-solid fa-bars"></i>
            </button>

        </div>
    </header>

    <!-- Mobile Nav Overlay -->
    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-header">
            <span style="font-weight:800; color:var(--primary);">Landsfy</span>
            <button class="mobile-nav-close" id="mobileNavClose"><i class="fa-solid fa-xmarkmark"></i></button>
        </div>
        <nav class="mobile-nav-links">
            <a href="<?php echo $base_path; ?>index">Home</a>
            <a href="<?php echo $base_path; ?>properties?purpose=sale">Buy Property</a>
            <a href="<?php echo $base_path; ?>properties?purpose=rent">Rent Property</a>
            <a href="<?php echo $base_path; ?>agents">Agents</a>
            <a href="<?php echo $base_path; ?>agencies">Agencies</a>
            <a href="<?php echo $base_path; ?>blog">Blog</a>
            <a href="<?php echo $base_path; ?>about">About Us</a>
            <a href="<?php echo $base_path; ?>contact">Contact</a>
        </nav>
        <div class="mobile-nav-footer">
            <a href="<?php echo $base_path; ?>login" class="btn btn-outline-sm">Sign In</a>
            <a href="<?php echo $base_path; ?>seller/add-listing" class="btn btn-primary-sm">+ Add Property</a>
        </div>
    </div>
