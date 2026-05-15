<?php
header("Content-Type: application/xml; charset=utf-8");
require_once 'includes/database/db.php';

$base_url = "https://www.landsfy.com/";

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Static Pages
$static_pages = ['', 'properties', 'agencies', 'agents', 'blog', 'about', 'contact', 'login', 'register', 'privacy-policy', 'terms-conditions'];
foreach ($static_pages as $page) {
    echo '<url>';
    echo '<loc>' . $base_url . $page . '</loc>';
    echo '<lastmod>' . date('Y-m-d') . '</lastmod>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

// Dynamic Properties
$stmt = $pdo->query("SELECT slug, updated_at FROM properties WHERE status = 'active' ORDER BY updated_at DESC");
while ($row = $stmt->fetch()) {
    echo '<url>';
    echo '<loc>' . $base_url . 'properties/' . $row->slug . '</loc>';
    echo '<lastmod>' . date('Y-m-d', strtotime($row->updated_at)) . '</lastmod>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>0.9</priority>';
    echo '</url>';
}

// Dynamic Agencies
$stmt = $pdo->query("SELECT slug FROM agencies WHERE status = 'active'");
while ($row = $stmt->fetch()) {
    echo '<url>';
    echo '<loc>' . $base_url . 'agencies/' . $row->slug . '</loc>';
    echo '<changefreq>monthly</changefreq>';
    echo '<priority>0.7</priority>';
    echo '</url>';
}

// Dynamic Blogs
$stmt = $pdo->query("SELECT slug, created_at FROM blogs ORDER BY created_at DESC");
while ($row = $stmt->fetch()) {
    echo '<url>';
    echo '<loc>' . $base_url . 'blog/' . $row->slug . '</loc>';
    echo '<lastmod>' . date('Y-m-d', strtotime($row->created_at)) . '</lastmod>';
    echo '<changefreq>monthly</changefreq>';
    echo '<priority>0.6</priority>';
    echo '</url>';
}

echo '</urlset>';
