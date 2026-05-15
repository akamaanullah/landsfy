<?php
include("header.php");
?>
 <style>
        .detail-header {
            display: flex;
            align-items: center;
            gap: 30px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .user-profile-large {
            width: 120px;
            height: 120px;
            border-radius: 24px;
            object-fit: cover;
            border: 4px solid var(--primary-light);
            box-shadow: 0 8px 32px rgba(108, 93, 211, 0.2);
        }

        .user-main-info {
            flex: 1;
        }

        .user-name-large {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .role-badge {
            font-size: 13px;
            padding: 4px 12px;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 600;
        }

        .info-value {
            font-size: 15px;
            color: var(--text-primary);
            font-weight: 500;
        }

        .activity-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        /* Capsule Tab Overrides */
        .tabs-container {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            padding: 6px;
            border-radius: 14px;
            display: inline-flex;
            gap: 5px;
            margin-bottom: 30px;
        }

        .user-tab {
            padding: 10px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            background: transparent;
            white-space: nowrap;
        }

        .user-tab:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.05);
        }

        .user-tab.active {
            color: white;
            background: var(--primary);
            box-shadow: 0 4px 15px rgba(108, 93, 211, 0.3);
        }

        .user-tab.active::after {
            display: none; /* Disable the underline from global CSS */
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
        <main class="main-content">
            <header class="header glass" style="margin-bottom: 30px;">
                <div class="header-left">
                    <div class="breadcrumb" style="font-size: 13px; color: var(--text-secondary); margin-bottom: 5px;">
                        User Management / User Details
                    </div>
                    <div class="page-title" id="headerUserName">User Profile</div>
                </div>
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle" style="margin-right: 12px;"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
                    <button class="btn-preview" id="editProfileBtn"><i class="fa-solid fa-pencil"></i> Edit Profile</button>
                    <button class="btn-primary" id="suspendUserBtn" style="background: var(--danger);"><i class="fa-solid fa-prohibit"></i> Suspend User</button>
                </div>
            </header>

            <div class="view-container">
                <!-- User Detail Header Card -->
                <div class="card-panel glass detail-header">
                    <img src="https://i.pravatar.cc/150?img=1" alt="Profile" class="user-profile-large" id="profileImage"
                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=User&background=6c5dd3&color=fff&bold=true'">

                    <div class="user-main-info">
                        <div class="user-name-large">
                            <span id="profileName">Loading...</span>
                            <span class="role-badge" id="profileRoleBadge" style="background: rgba(108, 93, 211, 0.1); color: var(--primary);">Agent</span>
                        </div>
                        <div style="display: flex; gap: 20px; color: var(--text-secondary); font-size: 14px;">
                            <span><i class="fa-solid fa-location-dot"></i> <span id="profileLocation">--</span></span>
                            <span><i class="fa-solid fa-envelope"></i> <span id="profileEmail">--</span></span>
                            <span><i class="fa-solid fa-phone"></i> <span id="profilePhone">--</span></span>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Agency</span>
                                <span class="info-value" id="profileAgency">--</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Member Since</span>
                                <span class="info-value" id="profileJoined">--</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Last Login</span>
                                <span class="info-value" id="profileLastLogin">Recently</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status</span>
                                <span class="info-value" id="profileStatus">--</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tabs-container">
                    <button class="user-tab active" data-target="activity">Activity Log</button>
                    <button class="user-tab" data-target="properties" id="propertiesTabTitle">Properties (0)</button>
                    <button class="user-tab" data-target="reviews" id="reviewsTabTitle" style="display: none;">Reviews (0)</button>
                    <button class="user-tab" data-target="wallet" id="walletTabTitle" style="display: none;">Wallet & Quota</button>
                </div>

                <!-- Tab Panes -->
                <div id="activity" class="tab-content active">
                    <div class="card-panel glass" style="padding: 0;" id="activityLogContainer">
                        <!-- Activity items go here -->
                         <div style="padding: 40px; text-align: center; color: var(--text-secondary);">Loading activity logs...</div>
                    </div>
                </div>

                <div id="properties" class="tab-content">
                    <div class="property-grid" id="userPropertyGrid">
                        <!-- Property items go here -->
                         <div style="padding: 40px; text-align: center; color: var(--text-secondary);">Loading properties...</div>
                    </div>
                </div>

                <div id="reviews" class="tab-content">
                    <div id="userReviewsContainer">
                        <!-- Review items go here -->
                         <div style="padding: 40px; text-align: center; color: var(--text-secondary);">Loading reviews...</div>
                    </div>
                </div>

                <div id="wallet" class="tab-content">
                    <div class="card-panel glass" style="padding: 32px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                            <h3 style="margin: 0; font-weight: 700;">Manual Quota Management</h3>
                            <button type="button" class="btn-primary" id="openQuotaModalBtn">
                                <i class="fa-solid fa-circle-plus"></i> Add Quota Balance
                            </button>
                        </div>
                        
                        <div class="info-grid" style="margin-bottom: 10px;">
                            <div class="quota-display-card" style="background: rgba(16, 185, 129, 0.05); padding: 20px; border-radius: 16px; border: 1px solid rgba(16, 185, 129, 0.1);">
                                <span class="info-label" style="color: var(--success);">Platinum Quota</span>
                                <div style="display: flex; align-items: baseline; gap: 10px; margin-top: 8px;">
                                    <span id="platinumTotalSpan" style="font-size: 28px; font-weight: 800;">0</span>
                                    <span style="font-size: 13px; color: var(--text-secondary);">Available</span>
                                </div>
                                <div style="font-size: 12px; margin-top: 8px; opacity: 0.7;">Used: <span id="platinumUsedSpan">0</span></div>
                            </div>

                            <div class="quota-display-card" style="background: rgba(59, 130, 246, 0.05); padding: 20px; border-radius: 16px; border: 1px solid rgba(59, 130, 246, 0.1);">
                                <span class="info-label" style="color: var(--info);">Diamond Quota</span>
                                <div style="display: flex; align-items: baseline; gap: 10px; margin-top: 8px;">
                                    <span id="diamondTotalSpan" style="font-size: 28px; font-weight: 800;">0</span>
                                    <span style="font-size: 13px; color: var(--text-secondary);">Available</span>
                                </div>
                                <div style="font-size: 12px; margin-top: 8px; opacity: 0.7;">Used: <span id="diamondUsedSpan">0</span></div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/admin/user-detail.js"></script>
    <script>
        document.querySelectorAll('.user-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.user-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const target = tab.getAttribute('data-target');
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(target).classList.add('active');
            });
        });
    </script>
</body>
</html>
