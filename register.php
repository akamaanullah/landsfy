<?php
session_start();
require_once 'includes/database/db.php';

$error = '';
$success = '';

// Retrieve flash messages if PRG pattern was used (non-ajax fallback)
if (isset($_SESSION['register_error'])) {
    $error = $_SESSION['register_error'];
    unset($_SESSION['register_error']);
}
if (isset($_SESSION['register_success'])) {
    $success = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $is_ajax = isset($_POST['ajax']);
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;

    if (empty($username) || empty($full_name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $password_confirm) {
        $error = "Passwords do not match.";
    } elseif (!$terms) {
        $error = "You must agree to the Terms & Conditions.";
    } else {
        try {
            // Check uniqueness
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Username or Email is already taken.";
            } else {
                // Get Role ID
                $role_name = $_POST['role'] ?? 'seller';
                // Security: Only allow these roles for self-registration
                if (!in_array($role_name, ['seller', 'buyer', 'agency_owner'])) {
                    $role_name = 'seller';
                }

                // Insert User
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, password_hash) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $full_name, $email, $hash]);
                $user_id = $pdo->lastInsertId();

                // Assign role
                $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
                $stmt->execute([$role_name]);
                $role = $stmt->fetch();
                
                if ($role) {
                    $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                    $stmt->execute([$user_id, $role->id]);

                    // --- Agency Creation Logic ---
                    if ($role_name === 'agency_owner') {
                        $agency_name = trim($_POST['agency_name'] ?? '');
                        $agency_phone = trim($_POST['agency_phone'] ?? '');
                        $agency_address = trim($_POST['agency_address'] ?? '');

                        if (!empty($agency_name)) {
                            $stmt = $pdo->prepare("INSERT INTO agencies (owner_id, name, phone, address, status) VALUES (?, ?, ?, ?, 'under_review')");
                            $stmt->execute([$user_id, $agency_name, $agency_phone, $agency_address]);
                        }
                    }

                    $success = "Registration successful! You may now log in.";
                } else {
                    $error = "System configuration error: Role '$role_name' not found.";
                }
            }
        } catch (PDOException $e) {
            $error = "We're sorry, an unexpected system error occurred.";
        }
    }

    // Handle Response
    if ($is_ajax) {
        header('Content-Type: application/json');
        if (!empty($error)) {
            echo json_encode(['success' => false, 'message' => $error]);
        } else {
            echo json_encode(['success' => true, 'message' => $success]);
        }
        exit;
    } else {
        // Prevent form resubmission on page refresh (PRG Pattern)
        if (!empty($error)) {
            $_SESSION['register_error'] = $error;
        } else {
            $_SESSION['register_success'] = $success;
        }
        header("Location: register");
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
    
    <div class="auth-card" style="max-width: 600px;">
        <div class="auth-header">
            <div class="auth-icon">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1 class="auth-title">Create Elite Account</h1>
            <p class="auth-subtitle">Join Pakistan's premier real estate community today.</p>
        </div>
        
        <div id="ajaxResponse">
            <?php if (!empty($error)): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: #EF4444; border-radius: 12px; padding: 15px; margin-bottom: 25px; font-size: 14px; text-align: center; font-weight: 600;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="background: rgba(16, 185, 129, 0.1); color: #10B981; border-radius: 12px; padding: 15px; margin-bottom: 25px; font-size: 14px; text-align: center; font-weight: 600;">
                    <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <form id="registerForm" action="register" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="input-icon-wrapper">
                        <input type="text" name="username" class="form-control" placeholder="johndoe" required>
                        <i class="fa-solid fa-circle-user input-icon"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <div class="input-icon-wrapper">
                        <input type="text" name="full_name" class="form-control" placeholder="John Doe" required>
                        <i class="fa-solid fa-user input-icon"></i>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-icon-wrapper">
                    <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                    <i class="fa-solid fa-envelope input-icon"></i>
                </div>
            </div>
            
            <!-- Agency Specific Fields (Hidden by default) -->
            <div id="agencyFields" style="display: none; border: 1px dashed var(--primary-soft); padding: 20px; border-radius: 16px; margin-bottom: 25px; background: #f8fafc;">
                <h4 style="font-size: 14px; color: var(--primary); margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-buildings"></i> Agency Details
                </h4>
                <div class="form-group">
                    <label class="form-label">Agency Name</label>
                    <div class="input-icon-wrapper">
                        <input type="text" name="agency_name" id="agency_name" class="form-control" placeholder="e.g. Landsfy Realty">
                        <i class="fa-solid fa-briefcase input-icon"></i>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Agency Phone</label>
                        <div class="input-icon-wrapper">
                            <input type="text" name="agency_phone" id="agency_phone" class="form-control" placeholder="+92 ...">
                            <i class="fa-solid fa-phone input-icon"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Office Address</label>
                        <div class="input-icon-wrapper">
                            <input type="text" name="agency_address" id="agency_address" class="form-control" placeholder="City, Area...">
                            <i class="fa-solid fa-location-dot input-icon"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-icon-wrapper">
                        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="••••••••" required>
                        <i class="fa-solid fa-lock-key input-icon"></i>
                        <i class="fa-solid fa-eye input-action-icon" onclick="togglePass('passwordInput', this)"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div class="input-icon-wrapper">
                        <input type="password" name="password_confirm" id="confirmPasswordInput" class="form-control" placeholder="••••••••" required>
                        <i class="fa-solid fa-lock-key input-icon"></i>
                        <i class="fa-solid fa-eye input-action-icon" onclick="togglePass('confirmPasswordInput', this)"></i>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Join As</label>
                <div class="role-grid">
                    <label class="role-item">
                        <input type="radio" name="role" value="seller" checked onclick="toggleAgencyFields(false)">
                        <div class="role-box">
                            <i class="fa-solid fa-house-chimney"></i>
                            <span>Seller</span>
                        </div>
                    </label>
                    <label class="role-item">
                        <input type="radio" name="role" value="buyer" onclick="toggleAgencyFields(false)">
                        <div class="role-box">
                            <i class="fa-solid fa-shopping-cart"></i>
                            <span>Buyer</span>
                        </div>
                    </label>
                    <label class="role-item">
                        <input type="radio" name="role" value="agency_owner" onclick="toggleAgencyFields(true)">
                        <div class="role-box">
                            <i class="fa-solid fa-building"></i>
                            <span>Agency</span>
                        </div>
                    </label>
                </div>
            </div>
            
            <div class="auth-options">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="terms" required>
                    <span class="checkbox-label">I agree to the <a href="#" class="forgot-link">Terms</a> & <a href="#" class="forgot-link">Privacy</a></span>
                </label>
            </div>
            
            <button type="submit" class="btn-auth" id="submitBtn">
                <span class="btn-text">Create Account Now</span> <i class="fa-solid fa-circle-check"></i>
            </button>
            
            <div class="auth-divider">OR</div>
            
            <a href="google-login" id="googleSignupBtn" class="btn-social" style="text-decoration: none;" onclick="setGoogleRole(event)">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google">
                Sign up with Google
            </a>
        </form>
        
        <div class="auth-footer-text">
            Already have an account? <a href="login">Sign In</a>
        </div>
    </div>
</main>

<script>
    function setGoogleRole(event) {
        event.preventDefault();
        const selectedRole = document.querySelector('input[name="role"]:checked').value;
        window.location.href = `google-login?role=${selectedRole}`;
    }

    function toggleAgencyFields(show) {
        const agencySection = document.getElementById('agencyFields');
        const agencyName = document.getElementById('agency_name');
        
        if (show) {
            agencySection.style.display = 'block';
            agencyName.setAttribute('required', 'required');
            agencySection.style.animation = 'fadeInUp 0.4s ease forwards';
        } else {
            agencySection.style.display = 'none';
            agencyName.removeAttribute('required');
        }
    }

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

    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const submitBtn = document.getElementById('submitBtn');
        const responseDiv = document.getElementById('ajaxResponse');
        
        submitBtn.classList.add('btn-loading');
        responseDiv.innerHTML = '';
        
        const formData = new FormData(form);
        formData.append('ajax', '1');
        
        fetch('register', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.classList.remove('btn-loading');
            if (data.success) {
                responseDiv.innerHTML = `<div style="background: rgba(16, 185, 129, 0.1); color: #10B981; border-radius: 12px; padding: 15px; margin-bottom: 25px; font-size: 14px; text-align: center; font-weight: 600; animation: auth-spin 0.3s ease;"><i class="fa-solid fa-circle-check"></i> ${data.message}</div>`;
                form.reset();
            } else {
                responseDiv.innerHTML = `<div style="background: rgba(239, 68, 68, 0.1); color: #EF4444; border-radius: 12px; padding: 15px; margin-bottom: 25px; font-size: 14px; text-align: center; font-weight: 600; animation: auth-spin 0.3s ease;"><i class="fa-solid fa-circle-exclamation"></i> ${data.message}</div>`;
            }
        })
        .catch(err => {
            submitBtn.classList.remove('btn-loading');
            responseDiv.innerHTML = `<div style="background: rgba(239, 68, 68, 0.1); color: #EF4444; border-radius: 12px; padding: 15px; margin-bottom: 25px; font-size: 14px; text-align: center; font-weight: 600;"><i class="fa-solid fa-circle-exclamation"></i> A network error occurred.</div>`;
        });
    });
</script>

<?php include 'footer.php'; ?>
