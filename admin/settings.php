<?php 
require_once __DIR__ . '/auth.php'; 
require_once dirname(__DIR__) . '/config/database.php';

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = db()->prepare("UPDATE settings SET openrouter_key=?, site_url=?, site_title=?, site_description=?, author_name=?, google_analytics_id=?, site_favicon=? WHERE id=1");
        $stmt->execute([
            $_POST['openrouter_key'], 
            $_POST['site_url'], 
            $_POST['site_title'], 
            $_POST['site_description'], 
            $_POST['author_name'], 
            $_POST['google_analytics_id'], 
            $_POST['site_favicon']
        ]);
        $success = "Configuration updated successfully.";
    } catch (PDOException $e) {
        $error = "Error updating settings: " . htmlspecialchars($e->getMessage());
    }
}

try {
    $settings = db()->query("SELECT * FROM settings WHERE id=1")->fetch();
} catch (PDOException $e) {
    $settings = [];
}
?>
<?php include __DIR__ . '/includes/head.php'; ?>

<div class="flex min-h-screen bg-transparent">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- mt-16 clears the fixed mobile header, md:mt-0 removes it for desktop -->
    <main class="flex-1 flex flex-col mt-16 md:mt-0 md:ml-[280px] w-full min-w-0">
        
        <!-- Plain Header: No background, no border -->
        <header class="px-6 md:px-12 py-6 md:py-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 mb-1 block">Configuration</span>
                <h1 class="text-xl md:text-2xl font-black uppercase tracking-widest text-black">Global Settings</h1>
            </div>
            <button type="submit" form="settingsForm" class="w-full md:w-auto px-10 py-4 bg-black text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-red-600 transition-all">
                Save Changes
            </button>
        </header>

        <div class="flex-1 p-6 md:p-12 pt-0 overflow-y-auto">
            
            <!-- Plain Messages -->
            <?php if($success): ?>
                <div class="mb-10 text-[10px] font-black uppercase text-green-600 tracking-widest">
                    [ ✓ <?php echo $success; ?> ]
                </div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="mb-10 text-[10px] font-black uppercase text-red-600 tracking-widest">
                    [ ⚠ <?php echo $error; ?> ]
                </div>
            <?php endif; ?>

            <form method="POST" id="settingsForm" class="grid grid-cols-1 lg:grid-cols-2 gap-16 md:gap-24 max-w-7xl">
                
                <div class="space-y-16">
                    <!-- Brand Identity Section: No Background, No Border -->
                    <section>
                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-red-600 mb-10 block">Brand Identity</span>
                        <div class="space-y-8">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Global Site Title</label>
                                <input type="text" name="site_title" value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>" class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-bold text-black transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Default Author Name</label>
                                <input type="text" name="author_name" value="<?php echo htmlspecialchars($settings['author_name'] ?? ''); ?>" class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-bold text-black transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Base Site URL</label>
                                <input type="url" name="site_url" value="<?php echo htmlspecialchars($settings['site_url'] ?? ''); ?>" class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-mono text-gray-500 transition-all">
                            </div>
                        </div>
                    </section>

                    <!-- SEO Engine Section: No Background, No Border -->
                    <section>
                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-red-600 mb-10 block">SEO Engine</span>
                        <div class="space-y-8">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Global Meta Description</label>
                                <textarea name="site_description" rows="3" class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-medium text-gray-600 leading-relaxed transition-all"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Favicon URL</label>
                                <input type="text" name="site_favicon" value="<?php echo htmlspecialchars($settings['site_favicon'] ?? ''); ?>" class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-mono text-gray-400 transition-all">
                            </div>
                        </div>
                    </section>
                </div>

                <div class="space-y-16">
                    <!-- Integrations Section: No Background, No Border -->
                    <section>
                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-red-600 mb-10 block">System Integrations</span>
                        <div class="space-y-8">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">OpenRouter API Key (AI)</label>
                                <input type="password" name="openrouter_key" value="<?php echo htmlspecialchars($settings['openrouter_key'] ?? ''); ?>" class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-mono text-black transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Google Analytics ID</label>
                                <input type="text" name="google_analytics_id" value="<?php echo htmlspecialchars($settings['google_analytics_id'] ?? ''); ?>" placeholder="G-XXXXXXXXXX" class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-bold text-black transition-all">
                            </div>
                        </div>
                    </section>

                    <!-- Plain Telemetry: No background, No shadow -->
                    <section class="pt-10">
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">Telemetry Data</h4>
                        <div class="space-y-3 text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                            <p>Environment: <span class="text-black">Production</span></p>
                            <p>Server Software: <span class="text-black"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span></p>
                            <p>DB Driver: <span class="text-black"><?php echo strtoupper(dbDriver()); ?></span></p>
                        </div>
                        <div class="mt-10">
                            <p class="text-[9px] font-black text-red-600 uppercase tracking-widest italic">Note: All configuration changes are persistent and update the live environment immediately.</p>
                        </div>
                    </section>
                </div>
            </form>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>