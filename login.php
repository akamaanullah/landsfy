<?php
session_start();
require_once 'includes/database/db.php';

if (isset($_SESSION['user_id'])) {
    $redirect = 'index';
    if ($_SESSION['role_name'] == 'admin') {
        $redirect = 'admin/';
    } elseif ($_SESSION['role_name'] == 'agency_owner') {
        $redirect = 'agency/';
    } elseif ($_SESSION['role_name'] == 'agent') {
        $redirect = 'agent/';
    } elseif ($_SESSION['role_name'] == 'seller') {
        $redirect = 'seller/';
    } elseif ($_SESSION['role_name'] == 'buyer') {
        $redirect = 'buyer/';
    }
    
    if (strpos($_SERVER['PHP_SELF'], $redirect) === false) {
        header("Location: " . $redirect);
        exit;
    }
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $is_ajax = isset($_POST['ajax']);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Username and Password are required.";
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT u.id, u.username, u.full_name, u.password_hash, u.avatar_url, r.role_name, 
                       a.agency_id 
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                LEFT JOIN agents a ON u.id = a.user_id
                WHERE u.username = :uname OR u.email = :email
            ");
            $stmt->execute(['uname' => $username, 'email' => $username]);
            $user = $stmt->fetch();

            if ($user) {
                if (password_verify($password, $user->password_hash)) {
                    // Success
                    $role = $user->role_name ?? 'agent';
                    
                    // --- Agency Check for Agents ---
                    if ($role === 'agent' && empty($user->agency_id)) {
                        $error = "Access Denied: You are not currently associated with any real estate agency. Please contact your administrator.";
                    } else {
                        $_SESSION['user_id'] = $user->id;
                        $_SESSION['username'] = $user->username;
                        $_SESSION['full_name'] = $user->full_name;
                        $_SESSION['role_name'] = $role;
                        $_SESSION['avatar_url'] = $user->avatar_url;
                        $_SESSION['agency_id'] = $user->agency_id; // Store for agents
                        $_SESSION['email'] = $username;

                        // Role-based Redirection
                        $redirect = 'index';
                        if ($_SESSION['role_name'] == 'admin') {
                            $redirect = 'admin/';
                        } elseif ($_SESSION['role_name'] == 'agency_owner') {
                            $redirect = 'agency/';
                        } elseif ($_SESSION['role_name'] == 'agent') {
                            $redirect = 'agent/';
                        } elseif ($_SESSION['role_name'] == 'seller') {
                            $redirect = 'seller/';
                        } elseif ($_SESSION['role_name'] == 'buyer') {
                            $redirect = 'buyer/';
                        }
                        
                        if ($is_ajax) {
                            echo json_encode(['success' => true, 'redirect' => $redirect]);
                            exit;
                        } else {
                            header("Location: " . $redirect);
                            exit;
                        }
                    }
                } else {
                    $error = "Invalid password. Please try again.";
                }
            } else {
                $error = "User not found with that email or username.";
            }
        } catch (PDOException $e) {
            $error = "An unexpected error occurred: " . $e->getMessage();
        }
    }

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
    }
}
?>
<?php include 'header.php'; ?>

<main class="auth-wrapper">
    <!-- Abstract Background -->
    <div class="auth-bg-shapes">
        <div class="auth-shape auth-shape-1"></div>
        <div class="auth-shape auth-shape-2"></div>
    </div>
    
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">
                <i class="fa-solid fa-lock-key"></i>
            </div>
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Login to your Landsfy account to manage your listings and inquiries.</p>
        </div>
        
        <div id="ajaxResponse">
            <?php if (!empty($error)): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: #EF4444; border-radius: 12px; padding: 15px; margin-bottom: 25px; font-size: 14px; text-align: center; font-weight: 600;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <form id="loginForm" action="login" method="POST">
            <!-- Username -->
            <div class="form-group">
                <label class="form-label">Username or Email</label>
                <div class="input-icon-wrapper">
                    <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
                    <i class="fa-solid fa-user input-icon"></i>
                </div>
            </div>
            
            <!-- Password -->
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-icon-wrapper">
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="••••••••" required>
                    <i class="fa-solid fa-lock-key input-icon"></i>
                    <i class="fa-solid fa-eye input-action-icon" onclick="togglePass('passwordInput', this)" title="Show Password"></i>
                </div>
            </div>
            
            <div class="auth-options">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="remember">
                    <span class="checkbox-label">Keep me logged in</span>
                </label>
                <a href="#" class="forgot-link">Forgot?</a>
            </div>
            
            <button type="submit" class="btn-auth" id="submitBtn">
                <span class="btn-text">Sign In to Account</span> <i class="fa-solid fa-arrow-right"></i>
            </button>
            
            <div class="auth-divider">OR CONTINUE WITH</div>
            
            <a href="google-login" class="btn-social" style="text-decoration: none;">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google">
                Google Account
            </a>
        </form>
        
        <div class="auth-footer-text">
            New to Landsfy? <a href="register">Create Elite Account</a>
        </div>
    </div>
</main>

<script>
    // Password toggler
    function togglePass(inputId, icon) {
        const input = document.getElementById(inputId);
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        if (type === 'text') {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            icon.style.color = 'var(--primary)';
        } else {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            icon.style.color = '#94a3b8';
        }
    }

    // AJAX Login Submission
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const submitBtn = document.getElementById('submitBtn');
        const responseDiv = document.getElementById('ajaxResponse');
        
        submitBtn.classList.add('btn-loading');
        responseDiv.innerHTML = '';
        
        const formData = new FormData(form);
        formData.append('ajax', '1');
        
        fetch('login', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                submitBtn.classList.remove('btn-loading');
                responseDiv.innerHTML = `<div style="background: rgba(239, 68, 68, 0.1); color: #EF4444; border-radius: 12px; padding: 15px; margin-bottom: 25px; font-size: 14px; text-align: center; font-weight: 600; animation: fadeIn 0.3s ease;"><i class="fa-solid fa-circle-exclamation"></i> ${data.message}</div>`;
            }
        })
        .catch(err => {
            submitBtn.classList.remove('btn-loading');
            responseDiv.innerHTML = `<div style="background: rgba(239, 68, 68, 0.1); color: #EF4444; border-radius: 12px; padding: 15px; margin-bottom: 25px; font-size: 14px; text-align: center; font-weight: 600;"><i class="fa-solid fa-circle-exclamation"></i> A network error occurred.</div>`;
        });
    });
</script>

<?php include 'footer.php'; ?>
