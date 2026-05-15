<?php include 'header.php'; ?>

<!-- Main Content -->
<main class="main-content">
    <header class="header glass">
        <div class="header-left">
            <div class="page-title">Property Detail</div>
            <div class="breadcrumb">Admin / Listings / <span id="breadcrumbTitle">Loading...</span></div>
        </div>
        
        <div class="header-actions">
            <button class="icon-btn" id="themeToggle" title="Toggle Light/Dark Mode">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
            </button>
            <a href="add-property.php" class="btn-primary" style="padding: 10px 20px; font-size: 14px;">
                <i class="fa-solid fa-plus"></i> Add New
            </a>
        </div>
    </header>

    <div class="view-container">
        <div class="detail-grid">
            
            <!-- Left Column: Gallery & Content -->
            <div class="detail-main">
                
                <!-- Gallery Slider -->
                <div class="gallery-container glass">
                    <div class="main-slider" id="mainSliderContainer">
                        <button class="slide-nav prev" id="prevSlide"><i class="fa-solid fa-chevron-left"></i></button>
                        <div id="sliderWrapper" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                             <img src="../includes/assets/images/placeholder.png" id="mainSlide" class="active-image">
                        </div>
                        <button class="slide-nav next" id="nextSlide"><i class="fa-solid fa-chevron-right"></i></button>
                        <div class="image-counter" id="imageCounter">0 / 0</div>
                    </div>
                    <div class="thumb-nav-container">
                        <button class="thumb-nav-btn prev" id="prevThumb"><i class="fa-solid fa-chevron-left"></i></button>
                        <div class="thumb-strip" id="thumbStrip">
                            <!-- Thumbnails injected here -->
                        </div>
                        <button class="thumb-nav-btn next" id="nextThumb"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                </div>

                <!-- Content Overview -->
                <div class="content-section glass">
                    <div class="price-header">
                        <h1 class="property-price" id="propertyPrice">PKR 0</h1>
                        <div class="badge-row" id="statusBadges">
                            <!-- Badges injected here -->
                        </div>
                    </div>
                    <h2 class="property-title" id="propertyTitle">Loading Title...</h2>
                    <p class="property-location"><i class="fa-solid fa-location-dot"></i> <span id="propertyLocation">Loading Location...</span></p>

                    <div class="feature-strip" id="mainFeatures">
                        <!-- Main Features (Beds, Baths, Area, Type) Injected here -->
                    </div>
                </div>

                <!-- Full Description -->
                <div class="content-section glass">
                    <h3 class="section-title">Description</h3>
                    <div class="description-text" id="propertyDescription">
                        <p>Loading summary...</p>
                    </div>
                </div>

                <!-- All Amenities -->
                <div class="content-section glass">
                    <h3 class="section-title">Amenities & Features</h3>
                    <div class="amenities-preview-grid" id="amenitiesGrid">
                        <!-- Amenities Injected here -->
                    </div>
                </div>

            </div>

            <!-- Right Column: Stats & Contact Info -->
            <div class="detail-sidebar">
                
                <!-- Listing Status Card -->
                <div class="info-sidebar-card glass">
                    <h3 class="side-title">Listing Insights</h3>
                    <div class="insight-row">
                        <div class="insight-item">
                            <i class="fa-solid fa-eye"></i>
                            <span class="insight-label">Views</span>
                            <span class="insight-val" id="totalViews">0</span>
                        </div>
                        <div class="insight-item">
                            <i class="fa-solid fa-phone"></i>
                            <span class="insight-label">Inquiries</span>
                            <span class="insight-val" id="totalLeads">0</span>
                        </div>
                    </div>
                    <div class="insight-row">
                        <div class="insight-item">
                            <i class="fa-solid fa-calendar-days"></i>
                            <span class="insight-label">Listed on</span>
                            <span class="insight-val" id="listedDate">0000-00-00</span>
                        </div>
                        <div class="insight-item">
                            <i class="fa-solid fa-clock"></i>
                            <span class="insight-label">Purpose</span>
                            <span class="insight-val" style="text-transform: capitalize;" id="propPurpose">-</span>
                        </div>
                    </div>
                </div>

                <!-- Property Management Actions -->
                <div class="info-sidebar-card glass management-card">
                    <h3 class="side-title">Administrative Actions</h3>
                    <div class="manage-btns" id="adminActionsContainer">
                        <button class="btn-primary" id="editPropBtn" style="width: 100%; margin-bottom: 12px;">
                            <i class="fa-solid fa-pencil"></i> Edit Property
                        </button>
                        
                        <!-- Approval Buttons (Shown if pending) -->
                        <div id="approvalButtons" style="display: none; gap: 10px; flex-direction: column;">
                            <button class="btn-primary" id="approveBtn" style="width: 100%; background: var(--success);">
                                <i class="fa-solid fa-circle-check"></i> Approve Listing
                            </button>
                            <button class="btn-secondary" id="rejectBtn" style="width: 100%; background: var(--danger); color: white;">
                                <i class="fa-solid fa-circle-xmark"></i> Reject Listing
                            </button>
                        </div>

                        <button class="btn-secondary" id="deletePropBtn" style="width: 100%; margin-top: 12px; border-color: var(--danger); color: var(--danger);">
                            <i class="fa-solid fa-trash-can"></i> Delete Permanent
                        </button>
                    </div>
                </div>

                <!-- Owner/Agent Card -->
                <div class="info-sidebar-card glass">
                    <h3 class="side-title">Listing Author</h3>
                    <div class="contact-profile">
                        <img src="https://i.pravatar.cc/150?img=12" id="authorAvatar" alt="Owner" class="contact-avatar">
                        <div class="contact-details">
                            <div class="contact-name" id="authorName">Loading...</div>
                            <div class="contact-type" id="authorRole">Author</div>
                        </div>
                    </div>
                    <div class="contact-actions" id="contactList">
                        <!-- Contacts injected here -->
                    </div>
                </div>

                <!-- Platform Branding -->
                <div class="info-sidebar-card glass platform-promotion">
                    <div class="platform-header">
                        <img src="../includes/assets/images/favicon.png" alt="Landsfy" style="width: 28px; height: 28px; object-fit: contain;">
                        <span>System Metadata</span>
                    </div>
                    <p style="font-size: 13px; opacity: 0.8;">Property UID: <strong id="propertyIDDisplay">#0</strong></p>
                    <p style="font-size: 13px; opacity: 0.8; margin-top: 5px;">Slug: <span id="propertySlugDisplay" style="word-break: break-all; opacity: 0.6;">-</span></p>
                </div>

            </div>

        </div>
    </div>
</main>

<!-- External JS -->
<script src="../includes/assets/js/admin/property-detail.js"></script>
<script src="../includes/assets/js/script.js"></script>
</body>
</html>

