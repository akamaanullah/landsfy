<?php 
include 'header.php';
require_once '../includes/helpers/quota_helper.php';

// Handle Actions
if (isset($_POST['action'])) {
    $requestId = $_POST['request_id'];
    $action = $_POST['action'];
    $adminNote = $_POST['admin_note'] ?? '';

    if ($action === 'approve') {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("SELECT user_id, request_type FROM premium_requests WHERE id = ?");
            $stmt->execute([$requestId]);
            $req = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($req) {
                $u_stmt = $pdo->prepare("UPDATE premium_requests SET status = 'approved', admin_note = ? WHERE id = ?");
                $u_stmt->execute([$adminNote, $requestId]);
                
                $type = str_replace('_credit', '', $req['request_type']);
                addAgentQuota($pdo, $req['user_id'], $type, 1);
                
                $pdo->commit();
                $successMsg = "Request approved and quota added!";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $errorMsg = "Failed to approve: " . $e->getMessage();
        }
    } elseif ($action === 'reject') {
        $u_stmt = $pdo->prepare("UPDATE premium_requests SET status = 'rejected', admin_note = ? WHERE id = ?");
        $u_stmt->execute([$adminNote, $requestId]);
        $successMsg = "Request rejected.";
    }
}
?>

<style>
    .premium-requests-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .premium-requests-table th {
        padding: 12px 20px;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        color: var(--text-secondary);
        font-weight: 800;
        letter-spacing: 0.5px;
    }
    .premium-requests-table td {
        padding: 20px;
        background: var(--glass-bg);
        vertical-align: middle;
    }
    .premium-requests-table tr td:first-child { border-radius: 16px 0 0 16px; border-left: 1px solid var(--glass-border); }
    .premium-requests-table tr td:last-child { border-radius: 0 16px 16px 0; border-right: 1px solid var(--glass-border); }
    .premium-requests-table tr td { border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); }

    .proof-link {
        background: var(--primary-gradient);
        color: white;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .proof-link:hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(107, 0, 182, 0.2); }

    .action-group {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .admin-note-input {
        background: rgba(0,0,0,0.05);
        border: 1px solid var(--glass-border);
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 13px;
        width: 180px;
        color: var(--text-primary);
    }
    .btn-approve { background: #10B981; color: white; border: none; padding: 8px 16px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s; }
    .btn-reject { background: #EF4444; color: white; border: none; padding: 8px 16px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s; }
    .btn-approve:hover, .btn-reject:hover { filter: brightness(1.1); transform: translateY(-1px); }
</style>

<main class="main-content">
    <header class="header glass">
        <div class="header-left">
            <div class="page-title">Premium Requests</div>
            <div class="breadcrumb">Admin / Manual Payment Verification</div>
        </div>
        <div class="header-actions">
            <button class="icon-btn" id="themeToggle"><i class="fa-solid fa-moon"></i></button>
            <button class="icon-btn"><i class="fa-solid fa-bell"></i></button>
        </div>
    </header>

    <div class="view-container">
        <?php if(isset($successMsg)): ?>
            <div class="alert glass" style="background: rgba(16, 185, 129, 0.1); color: #10B981; padding: 16px; border-radius: 12px; margin-bottom: 24px; border: 1px solid rgba(16, 185, 129, 0.2);">
                <i class="fa-solid fa-circle-check"></i> <?php echo $successMsg; ?>
            </div>
        <?php endif; ?>

        <div class="section-header">
            <h2 class="section-title">Pending Verifications</h2>
        </div>

        <div style="overflow-x: auto;">
            <table class="premium-requests-table">
                <thead>
                    <tr>
                        <th>Agent Information</th>
                        <th>Tier Type</th>
                        <th>Payment Proof</th>
                        <th>Submitted Date</th>
                        <th>Verification Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT pr.*, u.username, u.email FROM premium_requests pr JOIN users u ON pr.user_id = u.id WHERE pr.status = 'pending' ORDER BY pr.created_at ASC");
                    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($requests) > 0):
                        foreach ($requests as $req):
                    ?>
                        <tr>
                            <td>
                                <div style="font-weight: 800; color: var(--text-primary);"><?php echo htmlspecialchars($req['username']); ?></div>
                                <div style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($req['email']); ?></div>
                            </td>
                            <td>
                                <span style="font-weight: 900; letter-spacing: 1px; color: <?php echo $req['request_type'] == 'diamond_credit' ? '#3B82F6' : '#10B981'; ?>;">
                                    <?php echo strtoupper(str_replace('_credit', '', $req['request_type'])); ?>
                                </span>
                            </td>
                            <td>
                                <a href="../<?php echo htmlspecialchars($req['payment_screenshot']); ?>" target="_blank" class="proof-link">
                                    <i class="fa-solid fa-file-image"></i> View Receipt
                                </a>
                            </td>
                            <td style="color: var(--text-secondary); font-size: 13px;">
                                <?php echo date('M d, Y', strtotime($req['created_at'])); ?><br>
                                <span style="font-size: 11px; opacity: 0.7;"><?php echo date('H:i', strtotime($req['created_at'])); ?></span>
                            </td>
                            <td>
                                <form method="POST" class="action-group">
                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                    <input type="text" name="admin_note" placeholder="Note (optional)" class="admin-note-input">
                                    <button name="action" value="approve" class="btn-approve">Approve</button>
                                    <button name="action" value="reject" class="btn-reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    else:
                    ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 60px; color: var(--text-secondary); background: transparent; border: none;">
                                <i class="fa-solid fa-tray" style="font-size: 48px; opacity: 0.2; display: block; margin-bottom: 16px;"></i>
                                No pending premium requests.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

    <script src="../includes/assets/js/script.js"></script>
</body>
</html>
