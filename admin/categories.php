<?php 
// Fix paths for InfinityFree
require_once __DIR__ . '/auth.php'; 
require_once dirname(__DIR__) . '/config/database.php';

$success = "";
$error = "";

// 1. Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    db()->prepare("DELETE FROM categories WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: categories?msg=deleted"); exit;
}

// 2. Handle Edit/Create Logic
$edit_id = $_GET['edit'] ?? null;
$edit_data = ['name' => '', 'description' => ''];

if ($edit_id) {
    $stmt = db()->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_data = $stmt->fetch() ?: $edit_data;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    
    try {
        if ($edit_id) {
            // Update existing
            $old_name = $edit_data['name'];
            db()->beginTransaction();
            
            $stmt = db()->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $desc, $edit_id]);
            
            // Sync posts table if the name changed
            $stmt = db()->prepare("UPDATE posts SET category = ? WHERE category = ?");
            $stmt->execute([$name, $old_name]);
            
            db()->commit();
            header("Location: categories?msg=updated"); exit;
        } else {
            // Create new
            $stmt = db()->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $desc]);
            header("Location: categories?msg=created"); exit;
        }
    } catch (PDOException $e) {
        $error = "Error: Topic node name collision.";
    }
}

// 3. Fetch list
$categories = db()->query("SELECT c.*, (SELECT COUNT(*) FROM posts p WHERE p.category = c.name) as post_count FROM categories c ORDER BY c.name ASC")->fetchAll();

$msg = $_GET['msg'] ?? '';
if ($msg == 'created') $success = "Topic node initialized.";
if ($msg == 'updated') $success = "Topic parameters synchronized.";
if ($msg == 'deleted') $success = "Topic node terminated.";
?>
<?php include __DIR__ . '/includes/head.php'; ?>

<div class="flex min-h-screen bg-transparent">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- mt-16 clears fixed header on mobile -->
    <main class="flex-1 flex flex-col mt-16 md:mt-0 md:ml-[280px] w-full min-w-0">
        
        <header class="px-6 md:px-12 py-6 md:py-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 mb-1 block">Taxonomy</span>
                <h1 class="text-xl md:text-2xl font-black uppercase tracking-widest text-black">Topics & Metadata</h1>
            </div>
            <?php if($edit_id): ?>
                <a href="categories" class="text-[10px] font-black uppercase tracking-[0.2em] text-red-600 hover:text-black transition-all underline">Cancel Editing</a>
            <?php endif; ?>
        </header>

        <div class="flex-1 px-6 md:px-12 py-4 overflow-y-auto">
            
            <?php if($success): ?>
                <div class="mb-10 text-[10px] font-black uppercase text-green-600 tracking-widest">[ ✓ <?php echo $success; ?> ]</div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="mb-10 text-[10px] font-black uppercase text-red-600 tracking-widest">[ ⚠ <?php echo $error; ?> ]</div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 md:gap-24 max-w-7xl">
                
                <!-- Left: Form -->
                <div class="lg:col-span-4">
                    <section>
                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-red-600 mb-10 block"><?php echo $edit_id ? 'Update Node' : 'Initialize Topic'; ?></span>
                        <form method="POST" class="space-y-12">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Category Name</label>
                                <input type="text" name="name" required value="<?php echo htmlspecialchars($edit_data['name']); ?>" 
                                       class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-bold text-black transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-2">Internal Context / Description</label>
                                <textarea name="description" rows="5" 
                                          class="w-full py-3 bg-transparent border-b border-gray-200 focus:border-black outline-none text-sm font-medium text-gray-600 leading-relaxed transition-all"><?php echo htmlspecialchars($edit_data['description']); ?></textarea>
                            </div>
                            <button type="submit" class="w-full py-4 bg-black text-white text-[10px] font-black uppercase tracking-[0.4em] hover:bg-red-600 transition-all active:scale-[0.98]">
                                <?php echo $edit_id ? 'Sync Parameters' : 'Commit Topic'; ?>
                            </button>
                        </form>
                    </section>
                </div>

                <!-- Right: List -->
                <div class="lg:col-span-8">
                    <section>
                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-red-600 mb-10 block">Existing Taxonomy</span>
                        
                        <div class="space-y-4">
                            <?php if(empty($categories)): ?>
                                <p class="text-[10px] text-gray-300 font-black uppercase tracking-widest italic">Database Empty</p>
                            <?php endif; ?>

                            <?php foreach($categories as $cat): ?>
                            <div class="group py-8 border-b border-gray-50 flex flex-col md:flex-row md:items-start justify-between gap-8 hover:border-black transition-all">
                                <div class="flex-1">
                                    <div class="flex items-center gap-4 mb-3">
                                        <h3 class="text-[16px] font-black text-black uppercase tracking-tight"><?php echo htmlspecialchars($cat['name']); ?></h3>
                                        <span class="text-[9px] font-black bg-gray-50 px-2 py-1 text-gray-400 uppercase tracking-widest"><?php echo $cat['post_count']; ?> Articles</span>
                                    </div>
                                    <p class="text-[13px] font-medium text-gray-400 leading-relaxed max-w-xl italic">
                                        <?php echo !empty($cat['description']) ? htmlspecialchars($cat['description']) : 'Description not defined for this node.'; ?>
                                    </p>
                                </div>
                                
                                <div class="flex items-center gap-8 shrink-0">
                                    <!-- Edit Link -->
                                    <a href="categories?edit=<?php echo $cat['id']; ?>" class="text-gray-300 hover:text-black transition-colors" title="Edit Meta">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <!-- Explore (Link to posts.php) -->
                                    <a href="posts?category=<?php echo urlencode($cat['name']); ?>" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-black hover:text-red-600 transition-all">
                                        Explore
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                    <!-- Delete -->
                                    <a href="categories?delete=<?php echo $cat['id']; ?>" onclick="return confirm('Terminate this topic node?')" class="text-gray-200 hover:text-red-600 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>