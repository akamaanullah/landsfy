<?php include 'header.php'; ?>

<main class="blog-page">
    <!-- Blog Hero Section (Featured Post) -->
    <section class="blog-hero-section" id="featuredPostSection" style="display:none;">
        <div class="container">
            <div class="featured-post-card" id="featuredPostCard">
                <!-- Dynamic Featured Post -->
            </div>
        </div>
    </section>

    <!-- Category Tabs -->
    <section class="blog-nav-bar">
        <div class="container">
            <div class="blog-categories" id="blogCategories">
                <div class="cat-chip active" data-category="All Articles">All Articles</div>
                <!-- Dynamic Categories -->
            </div>
        </div>
    </section>

    <!-- Blog Grid -->
    <section class="blog-grid-section">
        <div class="container">
            <div class="blog-grid" id="blogGrid">
                <!-- Dynamic Blog Cards -->
                <div class="loading-state">
                    <div class="spinner"></div>
                    <p>Fetching latest articles...</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper" id="blogPagination">
                <!-- Dynamic Pagination -->
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-box">
                <h2>Get the Latest Market Updates</h2>
                <p>Subscribe to our newsletter and never miss an investment opportunity or legal update.</p>
                <form class="newsletter-form" id="newsletterForm">
                    <input type="email" id="subscriberEmail" placeholder="Your Email Address" required>
                    <button type="submit" class="btn-subscribe">Subscribe</button>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
<script src="includes/assets/js/website/blog.js"></script>
