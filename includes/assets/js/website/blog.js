/**
 * Landsfy Blog Logic
 */

let currentBlogPage = 1;
let selectedBlogCategory = 'All Articles';

document.addEventListener('DOMContentLoaded', () => {
    initBlog();
});

async function initBlog() {
    fetchBlogPosts();

    // Category Filter Click
    document.getElementById('blogCategories').addEventListener('click', (e) => {
        if (e.target.classList.contains('cat-chip')) {
            document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
            e.target.classList.add('active');
            selectedBlogCategory = e.target.textContent;
            currentBlogPage = 1;
            fetchBlogPosts();
        }
    });

    // Newsletter Form
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.onsubmit = (e) => {
            e.preventDefault();
            const email = document.getElementById('subscriberEmail').value;
            alert(`Thank you for subscribing with ${email}!`);
            newsletterForm.reset();
        };
    }
}

async function fetchBlogPosts() {
    const grid = document.getElementById('blogGrid');
    const featuredSection = document.getElementById('featuredPostSection');

    try {
        const response = await fetch(`${window.BASE_PATH}includes/api/website/blog_data.php?category=${selectedBlogCategory}&page=${currentBlogPage}`);
        const result = await response.json();

        if (result.success) {
            const posts = result.data.posts;
            
            // Render Featured Post (Only on Page 1 and All Articles)
            if (currentBlogPage === 1 && selectedBlogCategory === 'All Articles' && posts.length > 0) {
                renderFeaturedPost(posts[0]);
                featuredSection.style.display = 'block';
                renderBlogCards(posts.slice(1)); // Rest of the posts
            } else {
                featuredSection.style.display = 'none';
                renderBlogCards(posts);
            }

            renderBlogPagination(result.meta);
            renderBlogCategories(result.data.categories);
        }
    } catch (error) {
        grid.innerHTML = '<p class="error-msg">Failed to load articles. Please try again.</p>';
    }
}

function renderFeaturedPost(post) {
    const container = document.getElementById('featuredPostCard');
    const date = new Date(post.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    
    container.innerHTML = `
        <div class="featured-img-box">
            <img src="${post.image_url}" alt="${post.title}">
        </div>
        <div class="featured-content">
            <span class="blog-badge">${post.category}</span>
            <h1 class="featured-title">${post.title}</h1>
            <p class="featured-excerpt">${post.excerpt}</p>
            
            <div class="blog-meta">
                <div class="meta-item"><i class="fa-solid fa-circle-user"></i> Admin</div>
                <div class="meta-item"><i class="fa-solid fa-calendar-days"></i> ${date}</div>
                <div class="meta-item"><i class="fa-solid fa-clock"></i> ${post.read_time} min read</div>
            </div>

            <a href="blog/${post.slug}" class="btn-read-featured">Read Full Story <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    `;
}

function renderBlogCards(posts) {
    const grid = document.getElementById('blogGrid');
    if (posts.length === 0) {
        grid.innerHTML = `
            <div class="no-results-full">
                <i class="fa-solid fa-newspaper"></i>
                <h3>No Articles Found</h3>
                <p>We haven't posted any articles in this category yet. Check back soon for the latest real estate news and guides!</p>
                <button class="btn btn-outline-sm mt-3" onclick="location.reload()">Refresh Feed</button>
            </div>
        `;
        return;
    }

    let html = '';
    posts.forEach(p => {
        const date = new Date(p.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        html += `
            <article class="blog-card">
                <div class="blog-card-img">
                    <a href="blog/${p.slug}">
                        <img src="${p.image_url}" alt="${p.title}">
                    </a>
                    <span class="card-category">${p.category}</span>
                </div>
                <div class="blog-card-body">
                    <a href="blog/${p.slug}" style="text-decoration: none;"><h3 class="blog-card-title">${p.title}</h3></a>
                    <p class="blog-card-excerpt">${p.excerpt}</p>
                    <div class="blog-card-footer">
                        <span class="blog-date"><i class="fa-solid fa-calendar-days"></i> ${date}</span>
                        <a href="blog/${p.slug}" class="btn-read-more">Read More <i class="fa-solid fa-chevron-right"></i></a>
                    </div>
                </div>
            </article>
        `;
    });
    grid.innerHTML = html;
}

function renderBlogCategories(categories) {
    const container = document.getElementById('blogCategories');
    // Keep 'All Articles' and append others if they don't exist
    const existing = Array.from(container.querySelectorAll('.cat-chip')).map(c => c.textContent);
    
    categories.forEach(cat => {
        if (!existing.includes(cat.category)) {
            const chip = document.createElement('div');
            chip.className = 'cat-chip';
            chip.textContent = cat.category;
            container.appendChild(chip);
        }
    });
}

function renderBlogPagination(meta) {
    const container = document.getElementById('blogPagination');
    if (meta.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '<div class="premium-pagination">';
    for (let i = 1; i <= meta.total_pages; i++) {
        html += `<a href="javascript:void(0)" class="page-link ${i === meta.page ? 'active' : ''}" onclick="changeBlogPage(${i})">${i}</a>`;
    }
    html += '</div>';
    container.innerHTML = html;
}

function changeBlogPage(page) {
    currentBlogPage = page;
    fetchBlogPosts();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
