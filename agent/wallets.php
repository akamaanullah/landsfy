<?php 
include 'header.php';
require_once '../includes/database/db.php';

// Fetch current agent quota
$stmt = $pdo->prepare("SELECT platinum_quota, platinum_used, diamond_quota, diamond_used FROM agents WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$quota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quota) {
    $quota = ['platinum_quota' => 0, 'platinum_used' => 0, 'diamond_quota' => 0, 'diamond_used' => 0];
}
?>

<style>
    /* Premium Quota Cards Styling */
    .quota-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .quota-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    }
    
    .quota-icon-box {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        margin-bottom: 20px;
    }
    
    .platinum-accent { background: rgba(16, 185, 129, 0.1); color: #10B981; }
    .diamond-accent { background: rgba(59, 130, 246, 0.1); color: #3B82F6; }
    
    .quota-value-large {
        font-size: 42px;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 4px;
        color: var(--text-primary);
    }
    
    .quota-label-sub {
        font-size: 12px;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 700;
    }

    .quota-progress-container {
        height: 8px;
        background: rgba(0,0,0,0.05);
        border-radius: 10px;
        margin: 20px 0;
        overflow: hidden;
    }
    
    .progress-fill { height: 100%; border-radius: 10px; transition: width 1s ease; }
    .platinum-fill { background: linear-gradient(90deg, #10B981, #34D399); }
    .diamond-fill { background: linear-gradient(90deg, #3B82F6, #60A5FA); }

    .card-footer-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
    }

    .buy-quota-btn {
        padding: 10px 18px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 13px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .btn-platinum { background: #10B981; color: white; }
    .btn-diamond { background: #3B82F6; color: white; }
    .buy-quota-btn:hover { filter: brightness(1.1); transform: scale(1.02); }

    /* Table Adjustments */
    .table-container-glass {
        margin-top: 32px;
        border-radius: 24px;
        overflow: hidden;
    }
    
    .landsfy-table { width: 100%; border-collapse: collapse; }
    .landsfy-table th { padding: 18px 24px; text-align: left; font-size: 11px; text-transform: uppercase; color: var(--text-secondary); font-weight: 800; background: rgba(0,0,0,0.02); }
    .landsfy-table td { padding: 20px 24px; font-size: 14px; border-bottom: 1px solid var(--glass-border); }
    
    .status-pill {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .pill-pending { background: rgba(245, 158, 11, 0.1); color: #F59E0B; }
    .pill-approved { background: rgba(16, 185, 129, 0.1); color: #10B981; }
</style>

<main class="main-content">
    <header class="header glass">
        <div class="header-left">
            <div class="page-title">My Wallet</div>
            <div class="breadcrumb">Agent / Listing Quotas</div>
        </div>
        <div class="header-actions">
            <button class="icon-btn" id="themeToggle"><i class="fa-solid fa-moon"></i></button>
            <a href="notifications.php" class="icon-btn"><i class="fa-solid fa-bell"></i></a>
        </div>
    </header>

    <div class="view-container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Listing Quota</h2>
                <p style="color: var(--text-secondary); font-size: 14px;">Manage and monitor your premium visibility credits.</p>
            </div>
        </div>

        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));">
            <!-- Platinum Quota -->
            <div class="card-panel glass quota-card">
                <div class="quota-icon-box platinum-accent">
                    <i class="fa-solid fa-diamond"></i>
                </div>
                <div style="margin-bottom: 20px;">
                    <span class="quota-label-sub">Platinum Availability</span>
                    <div class="quota-value-large"><?php echo (int)$quota['platinum_quota'] - (int)$quota['platinum_used']; ?></div>
                </div>
                
                <div class="quota-progress-container">
                    <?php 
                    $pTotal = max(1, (int)$quota['platinum_quota']);
                    $pUsed = (int)$quota['platinum_used'];
                    $pPerc = min(100, ($pUsed / $pTotal) * 100);
                    ?>
                    <div class="progress-fill platinum-fill" style="width: <?php echo $pPerc; ?>%"></div>
                </div>

                <div class="card-footer-flex">
                    <span style="font-size: 13px; color: var(--text-secondary);">Used: <strong><?php echo $pUsed; ?></strong> / <?php echo (int)$quota['platinum_quota']; ?></span>
                    <button class="buy-quota-btn btn-platinum" onclick="showPaymentModal('platinum')">
                        <i class="fa-solid fa-circle-plus"></i> Buy Quota
                    </button>
                </div>
            </div>

            <!-- Diamond Quota -->
            <div class="card-panel glass quota-card">
                <div class="quota-icon-box diamond-accent">
                    <i class="fa-solid fa-gem"></i>
                </div>
                <div style="margin-bottom: 20px;">
                    <span class="quota-label-sub">Diamond Availability</span>
                    <div class="quota-value-large"><?php echo (int)$quota['diamond_quota'] - (int)$quota['diamond_used']; ?></div>
                </div>

                <div class="quota-progress-container">
                    <?php 
                    $dTotal = max(1, (int)$quota['diamond_quota']);
                    $dUsed = (int)$quota['diamond_used'];
                    $dPerc = min(100, ($dUsed / $dTotal) * 100);
                    ?>
                    <div class="progress-fill diamond-fill" style="width: <?php echo $dPerc; ?>%"></div>
                </div>

                <div class="card-footer-flex">
                    <span style="font-size: 13px; color: var(--text-secondary);">Used: <strong><?php echo $dUsed; ?></strong> / <?php echo (int)$quota['diamond_quota']; ?></span>
                    <button class="buy-quota-btn btn-diamond" onclick="showPaymentModal('diamond')">
                        <i class="fa-solid fa-circle-plus"></i> Buy Quota
                    </button>
                </div>
            </div>
        </div>

        <div class="table-container-glass card-panel glass">
            <div style="padding: 0 0 20px 0; border-bottom: 1px solid var(--glass-border); margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 700;">Recent Purchase Requests</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="landsfy-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Tier Type</th>
                            <th>Amount Paid</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM premium_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                        $stmt->execute([$_SESSION['user_id']]);
                        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($requests) > 0):
                            foreach ($requests as $req):
                        ?>
                            <tr>
                                <td style="font-weight: 600;">#REQ-<?php echo $req['id']; ?></td>
                                <td>
                                    <span style="font-weight: 700; color: <?php echo $req['request_type'] == 'diamond_credit' ? '#3B82F6' : '#10B981'; ?>;">
                                        <?php echo strtoupper(str_replace('_credit', '', $req['request_type'])); ?>
                                    </span>
                                </td>
                                <td style="font-weight: 700;">PKR <?php echo number_format($req['amount_paid']); ?></td>
                                <td style="color: var(--text-secondary);"><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                <td>
                                    <span class="status-pill pill-<?php echo $req['status']; ?>">
                                        <?php echo $req['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-secondary);">No recent purchase requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
    const copyToClipboard = (text, label) => {
        navigator.clipboard.writeText(text).then(() => {
            Swal.showValidationMessage(`${label} copied to clipboard!`);
            setTimeout(() => Swal.resetValidationMessage(), 2000);
        });
    };

    const showPaymentModal = (tier) => {
        const pricing = tier === 'platinum' ? '4,999' : '12,999';
        const rawAmount = tier === 'platinum' ? 4999 : 12999;
        const title = tier.charAt(0).toUpperCase() + tier.slice(1);
        
        Swal.fire({
            title: `<div style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 24px; color: var(--primary);">Purchase ${title} Quota</div>`,
            html: `
                <div class="payment-modal-content" style="text-align: left; font-family: 'Outfit', sans-serif;">
                    <p style="margin-bottom: 24px; color: #64748b; font-size: 14px; line-height: 1.6;">Transfer the amount to the bank below, then upload the screenshot to get your credit.</p>
                    
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 20px; margin-bottom: 24px;">
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 12px; color: #94a3b8;">Bank</span>
                                <span style="font-weight: 700; color: #1e293b;">UBL</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 12px; color: #94a3b8;">Account #</span>
                                <span style="font-weight: 800; color: #1e293b; cursor: pointer;" onclick="copyToClipboard('340804771', 'Account Number')">340804771 <i class="fa-solid fa-copy" style="font-size: 12px;"></i></span>
                            </div>
                             <div style="display: flex; justify-content: space-between;">
                                <span style="font-size: 12px; color: #94a3b8;">IBAN</span>
                                <span style="font-weight: 800; color: #1e293b; font-size: 12px; cursor: pointer;" onclick="copyToClipboard('PK91UNIL0109000340804771', 'IBAN')">PK91...4771 <i class="fa-solid fa-copy" style="font-size: 12px;"></i></span>
                            </div>
                             <div style="display: flex; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 10px;">
                                <span style="font-size: 12px; color: #94a3b8;">Amount</span>
                                <span style="font-weight: 900; color: var(--primary);">PKR ${pricing}/-</span>
                            </div>
                        </div>
                    </div>

                    <div class="upload-section" style="margin-bottom: 24px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">UPLOAD PAYMENT SCREENSHOT</label>
                        <div id="swalDropzone" style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.2s;">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size: 32px; color: #94a3b8;"></i>
                            <p id="swalFileLabel" style="font-size: 12px; color: #64748b; margin-top: 5px;">Click or drag receipt here</p>
                            <input type="file" id="swalFileInput" accept="image/*" style="display: none;">
                        </div>
                    </div>

                    <div style="background: #ecfdf5; border: 1px solid #10b98133; border-radius: 16px; padding: 12px; display: flex; align-items: center; gap: 12px;">
                        <i class="fa-brands fa-whatsapp" style="font-size: 24px; color: #10b981;"></i>
                        <div style="flex: 1;">
                            <div style="font-size: 10px; color: #065f46; font-weight: 700;">SUPPORT WHATSAPP</div>
                            <div style="font-weight: 800; color: #065f46; font-size: 14px;">0318 2923525</div>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Submit Proof',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#6b00b6',
            reverseButtons: true,
            didOpen: () => {
                const dz = document.getElementById('swalDropzone');
                const fi = document.getElementById('swalFileInput');
                const lb = document.getElementById('swalFileLabel');

                dz.onclick = () => fi.click();
                fi.onchange = () => {
                    if (fi.files[0]) {
                        lb.innerText = fi.files[0].name;
                        lb.style.color = 'var(--primary)';
                        dz.style.borderColor = 'var(--primary)';
                    }
                };
            },
            preConfirm: () => {
                const file = document.getElementById('swalFileInput').files[0];
                if (!file) {
                    Swal.showValidationMessage('Please upload the payment screenshot first');
                    return false;
                }
                return { file, tier, amount: rawAmount };
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('screenshot', result.value.file);
                formData.append('tier', result.value.tier);
                formData.append('amount', result.value.amount);

                Swal.fire({
                    title: 'Submitting...',
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const res = await fetch('../includes/api/agent/submit_premium_request.php', { method: 'POST', body: formData });
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire({ 
                            icon: 'success', 
                            title: '<span style="color: #10B981;">Request Received</span>', 
                            html: '<div style="color: #64748b; font-size: 14px;">Your payment proof has been submitted for verification. Your credits will be added once approved.</div>',
                            confirmButtonColor: '#6b00b6',
                            background: '#fff',
                            customClass: {
                                popup: 'premium-swal-popup'
                            }
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                    }
                } catch (err) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong. Please try again.' });
                }
            }
        });
    };
</script>

    <script src="../includes/assets/js/script.js"></script>
</body>
</html>
