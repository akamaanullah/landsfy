<?php
include 'header.php';

// Fetch Cities for the list
$cities_stmt = $pdo->query("SELECT * FROM cities ORDER BY sort_order ASC, name ASC");
$cities = $cities_stmt->fetchAll();
?>

        <main class="main-content">
            <header class="header glass" style="margin-bottom: 30px;">
                <div class="header-left">
                    <div class="breadcrumb" style="font-size: 13px; color: var(--text-secondary); margin-bottom: 5px;">
                        Administration / Geography
                    </div>
                    <div class="page-title">Location Management</div>
                </div>
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle" style="margin-right: 12px;"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
                    <button class="btn-primary" onclick="openCityModal()"><i class="fa-solid fa-plus"></i> Add New City</button>
                </div>
            </header>

            <div class="view-container">
                <div style="display: grid; grid-template-columns: 320px 1fr; gap: 24px; align-items: start;">
                    
                    <!-- Cities Sidebar -->
                    <div class="card-panel glass" style="padding: 24px;">
                        <h3 class="section-title" style="font-size: 16px; margin-bottom: 20px;">1. Cities</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;" id="citiesList">
                            <?php foreach($cities as $city): ?>
                                <div class="glass city-item" 
                                     data-id="<?php echo $city->id; ?>"
                                     style="padding: 12px 16px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--border-color); position: relative;"
                                     onclick="selectCity(<?php echo $city->id; ?>, '<?php echo htmlspecialchars($city->name); ?>', this)">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fa-solid fa-building" style="color: var(--primary);"></i>
                                        <span style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($city->name); ?></span>
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <i class="fa-solid fa-pencil" style="font-size: 14px; opacity: 0.5;" onclick="event.stopPropagation(); editCity(<?php echo htmlspecialchars(json_encode($city)); ?>)"></i>
                                        <i class="fa-solid fa-trash-can" style="font-size: 14px; color: #ff4757; opacity: 0.5;" onclick="event.stopPropagation(); deleteCity(<?php echo $city->id; ?>)"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Locations Grid -->
                    <div class="card-panel glass" style="padding: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                            <h3 class="section-title" style="font-size: 16px; margin-bottom: 0;" id="locationsTitle">2. Locations in City</h3>
                            <button class="btn-primary" id="addLocationBtn" style="display: none;" onclick="openLocationModal()">
                                <i class="fa-solid fa-plus"></i> Add Area / Sector
                            </button>
                        </div>
                        
                        <div id="locationsPlaceholder" style="text-align: center; padding: 60px; opacity: 0.5;">
                            <i class="fa-solid fa-location-dot" style="font-size: 48px; margin-bottom: 16px;"></i>
                            <p>Select a city from the left to manage its locations.</p>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; display: none;" id="locationsGrid">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- City Modal -->
    <div class="modal" id="cityModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; display: flex; opacity: 0; pointer-events: none; transition: all 0.3s ease;">
        <div class="glass" style="width: 400px; padding: 24px; border-radius: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 class="section-title" id="cityModalTitle" style="margin-bottom: 0;">Add New City</h3>
                <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 20px;" onclick="closeModal('cityModal')"></i>
            </div>
            <input type="hidden" id="cityId">
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">City Name</label>
                <input type="text" id="cityName" class="glass-input" placeholder="e.g. Lahore">
            </div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 24px;">
                <input type="checkbox" id="cityPopular">
                <label for="cityPopular" style="font-size: 14px; cursor: pointer;">Mark as Popular City</label>
            </div>
            <button class="btn-primary w-100" style="justify-content: center; padding: 12px;" onclick="saveCity()">Apply City</button>
        </div>
    </div>

    <!-- Location Modal -->
    <div class="modal" id="locationModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(8px); z-index: 1000; align-items: center; justify-content: center; display: flex; opacity: 0; pointer-events: none; transition: all 0.3s ease;">
        <div class="glass" style="width: 400px; padding: 24px; border-radius: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 class="section-title" id="locModalTitle" style="margin-bottom: 0;">Add Area / Sector</h3>
                <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 20px;" onclick="closeModal('locationModal')"></i>
            </div>
            <input type="hidden" id="locId">
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label">Location / Society Name</label>
                <input type="text" id="locName" class="glass-input" placeholder="e.g. DHA Phase 6">
            </div>
            <button class="btn-primary w-100" style="justify-content: center; padding: 12px;" onclick="saveLocation()">Apply Location</button>
        </div>
    </div>

    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/admin/locations.js"></script>
</body>
</html>
