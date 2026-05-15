<?php
include 'header.php';

// Fetch groups for the modal dropdown
$groups_stmt = $pdo->query("SELECT id, name FROM amenity_groups ORDER BY sort_order ASC");
$amenity_groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

        <main class="main-content">
            <header class="header glass" style="margin-bottom: 30px;">
                <div class="header-left">
                    <div class="breadcrumb" style="font-size: 13px; color: var(--text-secondary); margin-bottom: 5px;">
                        Settings / Property Configuration
                    </div>
                    <div class="page-title">Property Configuration</div>
                </div>
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle" style="margin-right: 12px;"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
                    <button class="btn-primary" onclick="Swal.fire('Saved!', 'Configuration has been updated.', 'success')"><i class="fa-solid fa-cloud-arrow-up"></i> Save Configuration</button>
                </div>
            </header>

            <div class="view-container">
                <div style="display: grid; grid-template-columns: 280px 1fr; gap: 20px; align-items: start;">
                    <!-- Left Sidebar Configuration -->
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <!-- Purpose Selection -->
                        <div class="card-panel glass" style="padding: 20px;">
                            <h3 class="section-title" style="font-size: 16px; margin-bottom: 15px;">1. Purpose</h3>
                            <div style="display: flex; gap: 8px;" id="purposeToggle">
                                <button class="btn-primary" id="btnPurposeSell"
                                    style="flex: 1; justify-content: center; padding: 10px; font-size: 13px;" onclick="switchPurpose('sell')">Sell</button>
                                <button class="btn-preview" id="btnPurposeRent"
                                    style="flex: 1; justify-content: center; border: 1px solid var(--border-color); padding: 10px; font-size: 13px;" onclick="switchPurpose('rent')">Rent</button>
                            </div>
                        </div>

                        <div class="card-panel glass" style="padding: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3 class="section-title" style="font-size: 16px; margin-bottom: 0;">2. Main Categories</h3>
                                <button class="btn-primary" style="padding: 6px 10px; font-size: 11px;" onclick="openCategoryModal()">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 8px;" id="mainCategoryList">
                                <!-- Populated by JS -->
                                <div style="text-align: center; padding: 20px; opacity: 0.5;">
                                    <i class="fa-solid fa-circle-notch fa-spin fa-spin"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Main Area -->
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <!-- Sub-categories Management -->
                        <div class="card-panel glass" style="padding: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h3 class="section-title" id="subTypesTitle" style="font-size: 16px; margin-bottom: 0;">3. Types</h3>
                                <button class="btn-primary" style="padding: 8px 16px; font-size: 12px;" onclick="openTypeModal()"><i class="fa-solid fa-plus"></i> Add Sub-type</button>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px;" id="subTypeContainer">
                                <!-- Populated by JS -->
                            </div>
                        </div>

                        <!-- Amenities Configuration -->
                        <div class="card-panel glass" style="padding: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h3 class="section-title" id="amenitiesTitle" style="font-size: 16px; margin-bottom: 0;">4. Amenities</h3>
                                <div style="display: flex; gap: 12px;">
                                    <button class="btn-preview" style="padding: 8px 16px; font-size: 13px;" onclick="viewJsonConfig()"><i class="fa-solid fa-code"></i> View JSON</button>
                                    <button class="btn-primary" style="padding: 8px 16px; font-size: 13px;" onclick="openAmenityModal()"><i class="fa-solid fa-plus"></i> Add Amenity Field</button>
                                </div>
                            </div>

                            <!-- Amenity Configuration Table -->
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: separate; border-spacing: 0 12px;">
                                    <thead>
                                        <tr style="text-align: left; color: var(--text-secondary); font-size: 13px;">
                                            <th style="padding: 0 15px;">Field Label</th>
                                            <th style="padding: 0 15px;">Context</th>
                                            <th style="padding: 0 15px;">Type</th>
                                            <th style="padding: 0 15px;">Req.</th>
                                            <th style="padding: 0 15px; text-align: right;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="amenitiesTableBody">
                                        <!-- Populated by JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Category Modal -->
    <div class="modal" id="categoryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; display: flex; opacity: 0; pointer-events: none; transition: all 0.3s ease;">
        <div class="glass" style="width: 400px; padding: 24px; border-radius: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 class="section-title" id="catModalTitle" style="margin-bottom: 0;">Add New Category</h3>
                <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 20px;" onclick="closeModal('categoryModal')"></i>
            </div>
            <input type="hidden" id="categoryIdField">
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Category Name</label>
                <input type="text" id="categoryName" class="glass-input" placeholder="e.g. Industrial">
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="fa-solid fa-house-chimney form-label">Icon (Font Awesome Name)</label>
                <input type="text" id="categoryIcon" class="glass-input" placeholder="e.g. fa-solid fa-industry" value="fa-house">
            </div>
            <button class="btn-primary w-100" style="justify-content: center; padding: 12px;" onclick="saveCategory()">Apply Category</button>
        </div>
    </div>

    <!-- Add Type Modal -->
    <div class="modal" id="typeModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; display: flex; opacity: 0; pointer-events: none; transition: all 0.3s ease;">
        <div class="glass" style="width: 400px; padding: 24px; border-radius: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 class="section-title" id="typeModalTitle" style="margin-bottom: 0;">Add New Sub-type</h3>
                <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 20px;" onclick="closeModal('typeModal')"></i>
            </div>
            <input type="hidden" id="subtypeIdField">
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Type Name</label>
                <input type="text" id="subTypeName" class="glass-input" placeholder="e.g. Duplex">
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="fa-solid fa-house-chimney form-label">Icon (Font Awesome Name)</label>
                <input type="text" id="subTypeIcon" class="glass-input" placeholder="e.g. fa-solid fa-house-chimney" value="fa-house-chimney">
            </div>
            <button class="btn-primary w-100" style="justify-content: center; padding: 12px;" onclick="saveSubType()">Apply Sub-type</button>
        </div>
    </div>

    <!-- Add Amenity Modal -->
    <div class="modal" id="amenityModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; display: flex; opacity: 0; pointer-events: none; transition: all 0.3s ease;">
        <div class="glass" style="width: 500px; padding: 24px; border-radius: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 class="section-title" style="margin-bottom: 0;">Add New Amenity Field</h3>
                <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 20px;" onclick="closeModal('amenityModal')"></i>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Label</label>
                    <input type="text" id="amenityLabel" class="glass-input" placeholder="e.g. Furnished">
                </div>
                <div class="form-group">
                    <label class="form-label">Field Type</label>
                    <select id="amenityFieldType" class="glass-input" onchange="toggleOptionsVisibility()">
                        <option value="switch">Switch (Toggle)</option>
                        <option value="number">Number Input</option>
                        <option value="dropdown">Dropdown</option>
                        <option value="text_input">Text Input</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">UI Group</label>
                    <select id="amenityGroup" class="glass-input">
                        <?php foreach($amenity_groups as $g): ?>
                            <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Icon (Font Awesome)</label>
                    <input type="text" id="amenityIcon" class="glass-input" placeholder="fa-circle" value="fa-circle">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Show In (Category Context)</label>
                <div style="display: flex; gap: 15px; background: rgba(0,0,0,0.03); padding: 12px; border-radius: 12px; border: 1px solid var(--border-color);">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" class="ctx-check" value="homes" checked> Homes
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" class="ctx-check" value="plots" checked> Plots
                    </label>
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                        <input type="checkbox" class="ctx-check" value="commercial" checked> Com.
                    </label>
                </div>
            </div>

            <div class="form-group" id="optionsGroup" style="margin-bottom: 16px; display: none;">
                <label class="form-label">Options (Comma separated)</label>
                <input type="text" id="amenityOptions" class="glass-input" placeholder="e.g. Yes, No, Semi-furnished">
            </div>

            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 24px;">
                <input type="checkbox" id="reqCheck">
                <label for="reqCheck" style="font-size: 14px; cursor: pointer;">This field is required</label>
            </div>
            <button class="btn-primary w-100" style="justify-content: center; padding: 12px;" onclick="saveAmenity()">Create Field</button>
        </div>
    </div>

    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/admin/property-config.js"></script>
</body>
</html>
