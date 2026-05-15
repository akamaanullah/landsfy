<?php 
include 'header.php'; 
$property_id = $_GET['id'] ?? null;
$page_title = $property_id ? "Edit Property" : "Add New Property";
?>

<script>
    const EDIT_PROPERTY_ID = <?php echo json_encode($property_id); ?>;
</script>

<style>
    /* Suggestion List Styling */
    .suggestions-wrapper { position: relative; width: 100%; }
    .suggestions-list {
        position: absolute;
        top: calc(100% + 5px);
        left: 0;
        right: 0;
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        z-index: 1000;
        max-height: 250px;
        overflow-y: auto;
        box-shadow: var(--glass-shadow);
        display: none;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    .suggestion-item {
        padding: 12px 16px;
        color: var(--text-primary);
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s;
        border-bottom: 1px solid var(--glass-border);
    }
    .suggestion-item:last-child { border-bottom: none; }
    .suggestion-item:hover, .suggestion-item.active {
        background: var(--primary);
        color: #fff;
    }
    .suggestion-item i { margin-right: 8px; color: var(--text-secondary); opacity: 0.7; }
</style>

<!-- Main Content Area -->
<main class="main-content">

    <!-- Top Header -->
    <header class="header glass">
        <div class="page-title"><?php echo $page_title; ?></div>

        <div class="header-actions">
            <button class="icon-btn" id="themeToggle" title="Toggle Light/Dark Mode">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
            </button>
            <button class="icon-btn">
                <i class="fa-solid fa-bell"></i>
            </button>
        </div>
    </header>

    <!-- Form Content container -->
    <form id="addPropertyForm" class="property-form-container">
        <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">

        <div class="property-form-full">
            <div class="form-main-col">

                <!-- Section 1: Property Purpose & Type -->
                <div class="card-panel glass form-section">
                    <div class="section-badge"><i class="fa-solid fa-target"></i></div>
                    <h3 class="section-title">Property Purpose & Type</h3>

                    <div class="form-group" style="margin-top: 24px;">
                        <label class="form-label">Purpose</label>
                        <div class="pill-group" id="purposeGroup">
                            <button type="button" class="pill-btn active" data-value="sell">Sell</button>
                            <button type="button" class="pill-btn" data-value="rent">Rent</button>
                        </div>
                        <input type="hidden" name="property_purpose" id="propertyPurpose" value="sell">
                    </div>

                    <div class="form-group">
                        <label class="form-label mb-1">Select Property Type</label>
                        <div class="type-tabs" id="categoryTabs">
                            <!-- Categories Rendered Here -->
                            <div class="shimmer" style="height: 40px; border-radius: 10px;"></div>
                        </div>
                        <input type="hidden" name="category_id" id="categoryId">
                    </div>

                    <!-- Sub Type selection -->
                    <div class="form-group" id="subTypeContainer">
                        <div class="chip-group wrap-chips" id="subTypeList">
                            <!-- Subtypes Rendered Here -->
                        </div>
                        <input type="hidden" name="subtype_id" id="subtypeId">
                    </div>
                </div>

                <!-- Section 2: Location and Area -->
                <div class="card-panel glass form-section">
                    <div class="section-badge"><i class="fa-solid fa-map-pin"></i></div>
                    <h3 class="section-title">Location Details</h3>

                    <div class="form-row">
                        <div class="form-group half">
                            <label class="form-label">City</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-building"></i>
                                <select name="city_id" id="citySelect" class="glass-input custom-select">
                                    <option value="" disabled selected>Select City</option>
                                    <!-- Cities Rendered Here -->
                                </select>
                            </div>
                        </div>
                        <div class="form-group half">
                            <label class="form-label">Location / Society</label>
                            <div class="suggestions-wrapper">
                                <div class="input-wrapper">
                                    <i class="fa-solid fa-map-trifold"></i>
                                    <input type="text" class="glass-input" id="locationInput" name="location_name" autocomplete="off" placeholder="e.g. DHA Phase 8">
                                </div>
                                <div class="suggestions-list" id="locationSuggestions"></div>
                                <input type="hidden" name="location_id" id="locationId">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Price and Details -->
                <div class="card-panel glass form-section">
                    <div class="section-badge"><i class="fa-solid fa-currency-circle-dollar"></i></div>
                    <h3 class="section-title">Price & Area Specifications</h3>

                    <div class="form-row">
                        <div class="form-group half">
                            <label class="form-label">Land Area Size</label>
                            <div class="input-group">
                                <input type="number" name="area_size" class="glass-input input-group-text" placeholder="Size" required>
                                <select name="area_unit" class="glass-input input-group-append">
                                    <option value="marla">Marla</option>
                                    <option value="sqyrd">Sq. Yd.</option>
                                    <option value="sqft">Sq. Ft.</option>
                                    <option value="kanal">Kanal</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group half">
                            <label class="form-label">Total Price (PKR)</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-money"></i>
                                <input type="number" name="price" id="propertyPriceInput" class="glass-input" placeholder="e.g. 15000000" required>
                            </div>
                            <div id="priceInWords" class="price-in-words"></div>
                        </div>
                        </div>
                    </div>

                    <div class="form-row" style="margin-top: 16px;">
                        <div class="form-group flex-row half">
                            <div class="toggle-text">
                                <div class="toggle-title">Installment Available</div>
                                <div class="toggle-desc">Can this be paid in installments?</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_installment_available" value="1">
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="form-group flex-row half">
                            <div class="toggle-text">
                                <div class="toggle-title">Ready for Possession</div>
                                <div class="toggle-desc">Is the property ready to move in?</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_ready_for_possession" value="1" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Features & Amenities -->
                <div class="card-panel glass form-section" id="amenitiesSection">
                    <div class="section-badge"><i class="fa-solid fa-list-star"></i></div>
                    <h3 class="section-title">Features & Amenities</h3>

                    <!-- Dynamic Field Container (e.g. Beds/Baths chips will be rendered here if applicable) -->
                    <div id="dynamicMainFields"></div>

                    <div class="form-group" style="margin-top: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <label class="form-label" style="margin-bottom: 0;">Additional Amenities</label>
                            <button type="button" class="btn-primary" id="openAmenitiesModalBtn" onclick="openAmenitiesModal()"
                                style="padding: 8px 16px; font-size: 13px; border-radius: 20px;">
                                <i class="fa-solid fa-plus"></i> Add Amenities
                            </button>
                        </div>

                        <div class="chip-group" id="selectedAmenitiesDisplay">
                            <div class="empty-state-text" style="color: var(--text-secondary); font-size: 14px; font-style: italic;">No additional amenities selected.</div>
                        </div>
                        <input type="hidden" name="property_amenities" id="propertyAmenitiesInput">
                    </div>

                    <div class="quality-tip">
                        <i class="fa-solid fa-circle-check tip-icon"></i>
                        <div class="tip-content">
                            <div class="tip-title">Quality Tip</div>
                            <p class="tip-desc">Add at least 5 amenities to help your listing stand out.</p>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Ad Description -->
                <div class="card-panel glass form-section">
                    <div class="section-badge"><i class="fa-solid fa-text-aa"></i></div>
                    <h3 class="section-title">Ad Description</h3>

                    <div class="form-group">
                        <label class="form-label">Property Title</label>
                        <input type="text" name="title" class="glass-input" placeholder="e.g. Beautiful 5 Marla House" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="glass-input" rows="6" placeholder="Write a detailed description..." required></textarea>
                    </div>
                </div>

                </div>

                <!-- Section 6: Property Media -->
                <div class="card-panel glass form-section upload-section">
                    <div class="section-badge"><i class="fa-solid fa-images"></i></div>
                    <h3 class="section-title">Property Media</h3>
                    <p class="section-desc" style="margin-top: -12px; margin-bottom: 24px; opacity: 0.7;">High-quality images get 5x more leads.</p>

                    <div class="dropzone" id="imageDropzone" onclick="document.getElementById('fileInput').click()">
                        <div class="dropzone-content">
                            <i class="fa-solid fa-upload upload-icon"></i>
                            <h4>Upload Images</h4>
                            <p>Drag & Drop here or select files</p>
                        </div>
                        <input type="file" id="fileInput" name="property_images[]" multiple accept="image/*" hidden>
                    </div>

                    <!-- Image Preview Container -->
                    <div id="imagePreviewContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; margin-top: 20px;"></div>
                </div>

                <!-- Section 7: Contact Information -->
                <div class="card-panel glass form-section contact-section" style="margin-bottom: 24px;">
                    <div class="section-badge"><i class="fa-solid fa-address-book"></i></div>
                    <h3 class="section-title" style="margin-bottom: 24px;">Contact Information</h3>

                    <div class="contact-fields-container">
                        <div class="contact-field-row">
                            <div class="field-icon"><i class="fa-solid fa-envelope"></i></div>
                            <div class="field-content">
                                <label class="form-label">Email</label>
                                <input type="email" name="contact_email" class="glass-input" value="<?php echo $_SESSION['email'] ?? ''; ?>">
                            </div>
                        </div>

                        <div class="contact-field-row">
                            <div class="field-icon"><i class="fa-solid fa-device-mobile"></i></div>
                            <div class="field-content">
                                <label class="form-label">Mobile</label>
                                <div class="phone-input-group">
                                    <div class="country-prefix"><img src="https://flagcdn.com/w20/pk.png" alt="PK"></div>
                                    <input type="tel" name="phone_number" class="glass-input" placeholder="03XXXXXXXXX" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top: 24px; justify-content: center; padding: 18px 40px; font-size: 18px; font-weight: 800; width: auto; min-width: 300px; margin-left: auto; display: flex;">
                        <i class="fa-solid fa-paper-plane"></i> Submit Property
                    </button>
                </div>
            </div>
        </div>
    </form>
</main>

<!-- Dynamic Amenities Modal -->
<div class="modal-overlay" id="amenitiesModal">
    <div class="modal-content glass" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="modal-title">Feature and Amenities</h3>
            <button type="button" class="close-modal-btn" onclick="closeAmenitiesModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div id="amenityGroupsNav" class="modal-tabs">
                <!-- Groups Rendered Here -->
            </div>
            <div id="amenityTabContent" class="modal-tab-content" style="padding: 20px;">
                <!-- Fields Rendered Here -->
                <div class="amenities-grid" id="amenityFieldsGrid"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeAmenitiesModal()">Cancel</button>
            <button type="button" class="btn-primary" onclick="applyAmenities()">Save Selection</button>
        </div>
    </div>
</div>

<script src="../includes/assets/js/script.js"></script>
<script src="../includes/assets/js/admin/add-property.js"></script>
</body>
</html>
