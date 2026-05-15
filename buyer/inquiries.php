<?php 
$page_title = "My Inquiries";
include 'header.php'; 
?>
        <!-- Main Content -->
        <main class="main-content">
            <header class="header glass">
                <div class="header-left">
                    <div class="page-title">My Inquiries</div>
                    <div class="breadcrumb">Track your property communications and engagement history</div>
                </div>
                
                <div class="header-actions">
                    <button class="icon-btn" id="themeToggle">
                        <i class="fa-solid <?php echo $user_theme == 'light' ? 'fa-moon' : 'fa-sun'; ?>" id="themeIcon"></i>
                    </button>
                    <div class="notification-wrapper" style="position: relative;">
                        <button class="icon-btn" id="notifBell">
                            <i class="fa-solid fa-bell"></i>
                            <span class="pulse-dot" style="position: absolute; top: 8px; right: 8px; width: 8px; height: 8px; background: var(--danger); border-radius: 50%; border: 2px solid var(--glass-bg); display: none;"></span>
                        </button>
                        <div class="dropdown-menu glass" id="notifDropdown" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 15px; width: 320px; padding: 20px; z-index: 1000; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h4 style="font-size: 16px; font-weight: 800; margin: 0;">Notifications</h4>
                                <span style="font-size: 11px; font-weight: 700; color: var(--primary); cursor: pointer;">Mark all as read</span>
                            </div>
                            <div id="notifContainer">
                                <div style="text-align: center; padding: 32px 0;">
                                    <i class="fa-solid fa-bell-slash" style="font-size: 24px; opacity: 0.2; margin-bottom: 12px; display: block;"></i>
                                    <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">No notifications yet.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="view-container">
                <!-- Data Table Shelf -->
                <div class="card-panel glass" style="padding: 0; overflow: hidden;">
                    <div class="table-responsive">
                        <table class="data-table" style="width: 100%;">
                            <thead style="background: rgba(0,0,0,0.02); border-bottom: 1px solid var(--border-color);">
                                <tr style="text-align: left; font-size: 13px; color: var(--text-secondary);">
                                    <th style="padding: 16px 24px;">Property Details</th>
                                    <th style="padding: 16px 24px;">Agent / Author</th>
                                    <th style="padding: 16px 24px; text-align: center;">Type</th>
                                    <th style="padding: 16px 24px; text-align: center;">Date</th>
                                    <th style="padding: 16px 24px; text-align: center;">Status</th>
                                    <th style="padding: 16px 24px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 100px 0;">
                                        <i class="fa-solid fa-circle-notch fa-spin" style="font-size: 32px; color: var(--primary);"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- External JS -->
    <script src="../includes/assets/js/script.js"></script>
    <script src="../includes/assets/js/buyer/inquiries.js"></script>
    <script src="../includes/assets/js/buyer/notif-checker.js"></script>
</body>
</html>
