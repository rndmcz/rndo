<?php 
// Fix paths for InfinityFree
require_once dirname(__DIR__) . '/config/database.php';

// Guard session start to prevent errors on multiple includes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = db()->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$_POST['username']]);
        $user = $stmt->fetch();

        if ($user && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php"); exit;
        } else { 
            $error = "Access Denied: Invalid Credentials"; 
        }
    } catch (PDOException $e) {
        $error = "System Offline: Check Database.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - randomous CMS</title>
    <!-- Preconnect to external resources for faster loading -->
    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Tailwind CSS - needed before render, no defer -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts - async to avoid blocking -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap"></noscript>
    <script>
        tailwind.config = { 
            theme: { 
                extend: { 
                    fontFamily: { sans: ['Inter', 'sans-serif'] }, 
                    colors: { youtube: { red: '#FF0033' } } 
                } 
            } 
        }
    </script>
    <style>
        input:focus { border-color: #000 !important; }
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center p-0 md:p-6 text-gray-900 selection:bg-youtube-red selection:text-white">

    <main class="w-full max-w-[1000px] flex flex-col md:flex-row border-none shadow-none">
        
        <!-- Decoration side (Plain Black) -->
        <div class="hidden md:flex flex-1 bg-black p-12 flex-col justify-between relative overflow-hidden text-white">
            <div class="z-10">
                <div class="flex items-center gap-1 mb-2">
                    <span class="text-4xl font-[900] tracking-tighter uppercase">CMS</span>
                    <span class="w-2 h-2 bg-youtube-red"></span>
                </div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-500">Admin Panel</p>
            </div>
            <div class="z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-600">Restricted Environment</p>
            </div>
        </div>

        <!-- Form side (Plain White) -->
        <div class="flex-1 p-8 md:p-16 flex flex-col justify-center relative bg-white">
            
            <div class="md:hidden flex items-center gap-1 mb-12">
                <span class="text-4xl font-[900] tracking-tighter uppercase text-black">CMS</span>
                <span class="w-2 h-2 bg-youtube-red"></span>
            </div>

            <div class="mb-12">
                <h1 class="text-2xl font-black tracking-tight text-black uppercase leading-none">Authentication</h1>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-300 mt-2">Identity verification required</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="mb-10 text-[10px] font-black uppercase tracking-widest text-red-600">
                    [ ! ] <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-10">
                <div>
                    <label class="block text-[9px] font-black uppercase tracking-[0.3em] text-gray-400 mb-2">Member Code</label>
                    <input type="text" name="username" 
                           class="w-full px-0 py-3 bg-transparent border-b-2 border-gray-100 outline-none text-sm font-bold text-black transition-all" 
                           required autofocus>
                </div>

                <div>
                    <label class="block text-[9px] font-black uppercase tracking-[0.3em] text-gray-400 mb-2">Password</label>
                    <input type="password" name="password" 
                           class="w-full px-0 py-3 bg-transparent border-b-2 border-gray-100 outline-none text-sm font-bold text-black transition-all" 
                           required>
                </div>

                <div class="pt-4 flex flex-col gap-6">
                    <button type="submit" 
                            class="w-full py-4 bg-black text-white text-[10px] font-black uppercase tracking-[0.4em] hover:bg-youtube-red transition-all active:scale-[0.98]">
                        Verify
                    </button>
                    
                    <a href="../" class="text-center text-[9px] font-black uppercase tracking-widest text-gray-300 hover:text-black transition-colors">
                        Return to Public Page
                    </a>
                </div>
            </form>
        </div>
    </main>

</body>
</html>