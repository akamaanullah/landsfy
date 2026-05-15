/**
 * Landsfy Blog Detail Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    let slug = urlParams.get('slug');

    // SEO Friendly URL support
    if (!slug) {
        const pathParts = window.location.pathname.split('/');
        if (pathParts.includes('blog')) {
            slug = pathParts[pathParts.indexOf('blog') + 1];
        }
    }

    if (slug) {
        fetchBlogDetail(slug);
    } else {
        window.location.href = window.BASE_PATH + 'blog';
    }
});

async function fetchBlogDetail(slug) {
    try {
        const response = await fetch(`${window.BASE_PATH}includes/api/website/blog_detail_data.php?slug=${slug}`);
        const result = await response.json();

        if (result.success) {
            renderBlogPost(result.data.post);
            renderRelatedPosts(result.data.related);
        } else {
            document.getElementById('postTitle').textContent = "Article not found.";
        }
    } catch (error) {
        console.error("Error fetching blog detail", error);
    }
}

function renderBlogPost(post) {
    const date = new Date(post.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    
    document.getElementById('postCategory').textContent = post.category;
    document.getElementById('postTitle').textContent = post.title;
    document.getElementById('postDate').textContent = date;
    document.getElementById('postReadTime').textContent = post.read_time;
    
    if (post.author_name) document.getElementById('authorName').textContent = post.author_name;
    if (post.author_img) document.getElementById('authorImg').src = post.author_img;

    if (post.image_url) {
        document.getElementById('postMainImg').src = post.image_url;
        document.getElementById('postMainImgContainer').style.display = 'block';
    }

    document.getElementById('postContent').innerHTML = post.content;
}

function renderRelatedPosts(related) {
    const container = document.getElementById('relatedPostsList');
    if (related.length === 0) {
        container.innerHTML = '<p>No related articles.</p>';
        return;
    }

    let html = '';
    related.forEach(r => {
        const date = new Date(r.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        html += `
            <a href="blog/${r.slug}" class="related-post-item">
                <div class="related-img">
                    <img src="${r.image_url}" alt="${r.title}">
                </div>
                <div class="related-info">
                    <h4>${r.title}</h4>
                    <span class="related-date">${date}</span>
                </div>
            </a>
        `;
    });
    container.innerHTML = html;
}

function shareOnSocial(platform) {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent(document.getElementById('postTitle').textContent);
    
    let shareUrl = '';
    if (platform === 'facebook') shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
    if (platform === 'twitter') shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
    if (platform === 'whatsapp') shareUrl = `https://api.whatsapp.com/send?text=${text}%20${url}`;
    
    window.open(shareUrl, '_blank', 'width=600,height=400');
}

function shareLink() {
    navigator.clipboard.writeText(window.location.href);
    alert("Article link copied to clipboard!");
}
