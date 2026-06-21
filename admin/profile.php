<?php 
// Fix paths for InfinityFree
require_once __DIR__ . '/auth.php'; 
require_once dirname(__DIR__) . '/config/database.php';

$user_id = $_SESSION['user_id'];
$success = ""; 
$error = "";

try {
    $stmt = db()->prepare("SELECT username, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    die("Database error reading user.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $new_username = trim($_POST['username']);
        if (!empty($_POST['password'])) {
            db()->prepare("UPDATE users SET username=?, password=? WHERE id=?")
                ->execute([$new_username, password_hash($_POST['password'], PASSWORD_BCRYPT), $user_id]);
        } else {
            db()->prepare("UPDATE users SET username=? WHERE id=?")
                ->execute([$new_username, $user_id]);
        }
        $success = "Security parameters updated.";
        $user['username'] = $new_username;
    } catch (PDOException $e) { 
        $error = "Update failed. ID may be in use."; 
    }
}
?>
<?php include __DIR__ . '/includes/head.php'; ?>

<div class="flex min-h-screen bg-transparent">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- mt-16 clears the fixed mobile header, md:mt-0 removes it for desktop -->
    <main class="flex-1 flex flex-col mt-16 md:mt-0 md:ml-[280px] w-full min-w-0">
        
        <!-- Plain Header -->
        <header class="px-6 md:px-12 py-6 md:py-8 flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-gray-100 md:border-none">
            <div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 mb-1 block">Account</span>
                <h1 class="text-xl md:text-2xl font-black uppercase tracking-widest text-black">Identity Manager</h1>
            </div>
            <button type="submit" form="profileForm" class="w-full md:w-auto text-center px-10 py-4 bg-black text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-red-600 transition-all">
                Save Changes
            </button>
        </header>

        <div class="flex-1 p-6 md:p-12 pt-0 flex flex-col items-start">
            
            <!-- Plain Messages -->
            <?php if($success): ?>
                <div class="mb-10 text-[10px] font-black uppercase text-green-600 tracking-widest">[ ✓ <?php echo $success; ?> ]</div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="mb-10 text-[10px] font-black uppercase text-red-600 tracking-widest">[ ⚠ <?php echo $error; ?> ]</div>
            <?php endif; ?>

            <form method="POST" id="profileForm" class="w-full max-w-xl space-y-16">
                
                <section>
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-red-600 mb-10 block text-left">Credential Configuration</span>
                    
                    <div class="space-y-10">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Primary Username</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" 
                                   class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-bold text-black transition-all" required>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Secret Key (Password)</label>
                            <input type="password" name="password" placeholder="LEAVE BLANK TO RETAIN CURRENT" 
                                   class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-bold text-black transition-all">
                        </div>
                    </div>
                </section>

                <section class="pt-10">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 mb-6 block">Access Metadata</span>
                    <div class="space-y-3 text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                        <p>User Group: <span class="text-black">Master_Admin</span></p>
                        <p>Session Node: <span class="text-black">Active</span></p>
                        <p>Auth Date: <span class="text-black"><?php echo !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : '---'; ?></span></p>
                    </div>
                </section>

            </form>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>