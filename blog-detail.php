<?php 
if (isset($_GET['slug']) && !empty($_GET['slug'])) {
    // If it's a direct .php access with ?slug=, we redirect to clean /blog/slug
    // This handles the case where someone might still use the old blog-detail.php?slug= format
    if (strpos($_SERVER['REQUEST_URI'], 'blog-detail.php') !== false) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: blog/" . $_GET['slug']);
        exit;
    }
}
include 'header.php'; 
?>

<main class="blog-detail-page">
    <!-- Article Header -->
    <header class="blog-detail-header">
        <div class="container">
            <span class="blog-detail-category" id="postCategory">...</span>
            <h1 class="blog-detail-title" id="postTitle">Loading article...</h1>
            
            <div class="blog-detail-meta-wrap">
                <div class="author-box-small">
                    <img id="authorImg" src="includes/assets/images/website/placeholder-avatar.jpg" alt="Author">
                    <span id="authorName">Admin</span>
                </div>
                <div class="meta-item"><i class="fa-solid fa-calendar-days"></i> <span id="postDate">...</span></div>
                <div class="meta-item"><i class="fa-solid fa-clock"></i> <span id="postReadTime">0</span> min read</div>
            </div>
        </div>
    </header>

    <!-- Main Featured Image -->
    <div class="blog-main-img" id="postMainImgContainer" style="display:none;">
        <div class="container">
            <img id="postMainImg" src="" alt="Main Image" style="width:100%; border-radius:30px; object-fit:cover; height:500px;">
        </div>
    </div>

    <div class="container">
        <div class="blog-detail-layout">
            
            <!-- Left: Article Content -->
            <article class="blog-content-area">
                <div class="article-content" id="postContent">
                    <!-- Dynamic Content -->
                </div>

                <!-- Social Share -->
                <div class="social-share-box">
                    <span class="share-label">Share this article:</span>
                    <div class="share-btns">
                        <a href="javascript:void(0)" onclick="shareOnSocial('facebook')" class="share-btn fb"><i class="fa-solid fa-facebook-logo"></i></a>
                        <a href="javascript:void(0)" onclick="shareOnSocial('twitter')" class="share-btn tw"><i class="fa-solid fa-twitter-logo"></i></a>
                        <a href="javascript:void(0)" onclick="shareOnSocial('whatsapp')" class="share-btn wa"><i class="fa-brands fa-whatsapp"></i></a>
                        <a href="javascript:void(0)" onclick="shareLink()" class="share-btn li"><i class="fa-solid fa-link"></i></a>
                    </div>
                </div>
            </article>

            <!-- Right: Sidebar -->
            <aside class="blog-detail-sidebar">
                <!-- Related Posts Widget -->
                <div class="blog-sidebar-widget">
                    <h3 class="widget-title"><i class="fa-solid fa-article"></i> Related Posts</h3>
                    <div class="related-posts-list" id="relatedPostsList">
                        <!-- Dynamic Related Posts -->
                    </div>
                </div>

                <!-- Call to Action Widget -->
                <div class="blog-sidebar-widget" style="background: var(--grad-primary); color: white;">
                    <h3 class="widget-title" style="color: white;"><i class="fa-solid fa-house"></i> Want to Invest?</h3>
                    <p style="font-size: 14px; opacity: 0.9; margin-bottom: 20px;">Contact our top verified agents to get the best deals in these hubs.</p>
                    <a href="agents" class="btn btn-white" style="width: 100%; text-align: center; background: white; color: var(--primary); font-weight: 800; padding: 12px; border-radius: 12px; display: block;">Talk to an Agent</a>
                </div>
            </aside>

        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
<script src="<?php echo $base_path; ?>includes/assets/js/website/blog_detail.js"></script>
