<?php
require_once 'includes/google-config.php';

if (isset($_GET['code'])) {
    try {
        // Authenticate code
        $token = $google_client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (!isset($token['error'])) {
            $google_client->setAccessToken($token['access_token']);

            // Get profile info
            $google_oauth = new Google_Service_Oauth2($google_client);
            $google_account_info = $google_oauth->userinfo->get();

            $google_id = $google_account_info->id;
            $email = $google_account_info->email;
            $name = $google_account_info->name;
            $avatar = $google_account_info->picture;

            // Check if user already exists
            $stmt = $pdo->prepare("
                SELECT u.*, r.id as role_id, r.role_name 
                FROM users u 
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id 
                WHERE u.google_id = ? OR u.email = ?
            ");
            $stmt->execute([$google_id, $email]);
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            if ($user) {
                // User exists, update google_id if missing
                if (empty($user->google_id)) {
                    $update = $pdo->prepare("UPDATE users SET google_id = ?, oauth_provider = 'google', email_verified_at = NOW() WHERE id = ?");
                    $update->execute([$google_id, $user->id]);
                }

                // Login User
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['full_name'] = $user->full_name;
                $_SESSION['role_id'] = $user->role_id;
                $_SESSION['role_name'] = $user->role_name;
                $_SESSION['email'] = $user->email;
                $_SESSION['avatar_url'] = $user->avatar_url;
            } else {
                // User doesn't exist, create new account with requested role
                $username = strtolower(str_replace(' ', '', $name)) . rand(100, 999);
                
                // Role Mapping
                $requested_role = $_SESSION['google_reg_role'] ?? 'buyer';
                $role_map = ['buyer' => 5, 'seller' => 4, 'agency_owner' => 2];
                $target_role_id = $role_map[$requested_role] ?? 5;
                unset($_SESSION['google_reg_role']);

                $pdo->beginTransaction();
                try {
                    $insert = $pdo->prepare("
                        INSERT INTO users (google_id, oauth_provider, username, full_name, email, avatar_url, status, created_at, email_verified_at) 
                        VALUES (?, 'google', ?, ?, ?, ?, 'active', NOW(), NOW())
                    ");
                    $insert->execute([$google_id, $username, $name, $email, $avatar]);
                    $new_user_id = $pdo->lastInsertId();

                    // Assign Selected Role
                    $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$new_user_id, $target_role_id]);
                    
                    $pdo->commit();

                    // Login User
                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['full_name'] = $name;
                    $_SESSION['role_id'] = $target_role_id;
                    $_SESSION['role_name'] = $requested_role;
                    $_SESSION['email'] = $email;
                    $_SESSION['avatar_url'] = $avatar;
                } catch (Exception $ex) {
                    $pdo->rollBack();
                    throw $ex;
                }
            }

            // Redirect to index
            header("Location: index");
            exit();

        } else {
            header("Location: login?error=google_failed");
            exit();
        }
    } catch (Exception $e) {
        header("Location: login?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: login");
    exit();
}
?>
