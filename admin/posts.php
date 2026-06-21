<?php 
// Fix paths for InfinityFree
require_once __DIR__ . '/auth.php'; 
require_once dirname(__DIR__) . '/config/database.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    db()->prepare("DELETE FROM posts WHERE id = ?")->execute([(int)$_GET['delete']]);
    header("Location: posts.php?msg=deleted"); exit;
}

$limit = 12; 
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;
$search = $_GET['q'] ?? '';
$cat_filter = $_GET['category'] ?? '';

$params = []; 
$where_clauses = [];

if ($search) { 
    $where_clauses[] = "(title LIKE :q1 OR excerpt LIKE :q2)"; 
    $params['q1'] = "%$search%"; 
    $params['q2'] = "%$search%"; 
}

if ($cat_filter) { 
    $where_clauses[] = "category = :category"; 
    $params['category'] = $cat_filter; 
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

try {
    $count_stmt = db()->prepare("SELECT COUNT(*) FROM posts $where_sql");
    $count_stmt->execute($params);
    $total_posts = $count_stmt->fetchColumn();
    $total_pages = ceil($total_posts / $limit);

    $stmt = db()->prepare("SELECT id, title, slug, category, tag_color, created_at, views FROM posts $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
    
    $categories = db()->query("SELECT DISTINCT category FROM posts ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $posts = []; $categories = []; $total_pages = 1;
}

function buildQueryString($new_params) { 
    return http_build_query(array_merge($_GET, $new_params)); 
}
?>
<?php include __DIR__ . '/includes/head.php'; ?>

<div class="flex min-h-screen bg-transparent">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <main class="flex-1 flex flex-col mt-16 md:mt-0 md:ml-[280px] w-full min-w-0">
        
        <!-- Header -->
        <header class="px-6 md:px-12 py-6 md:py-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 mb-1 block">Inventory</span>
                <h1 class="text-xl md:text-2xl font-black uppercase tracking-widest text-black">Article Library</h1>
            </div>
            <a href="/admin/compose.php" class="w-full md:w-auto text-center px-10 py-4 bg-black text-white text-[10px] font-black uppercase tracking-[0.3em] hover:bg-red-600 transition-all">
                New Article
            </a>
        </header>

        <!-- Filters -->
        <div class="px-6 md:px-12 py-4">
            <form method="GET" class="flex flex-col md:flex-row gap-6">
                <div class="flex-1 relative">
                    <i class="bi bi-search absolute left-0 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search database..." class="w-full pl-8 py-3 bg-transparent border-b border-gray-200 outline-none text-sm font-bold placeholder-gray-300 focus:border-black transition-all">
                </div>
                <div class="flex gap-4">
                    <select name="category" onchange="this.form.submit()" class="bg-transparent border-none text-[10px] font-black uppercase tracking-widest outline-none cursor-pointer">
                        <option value="">All Topics</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($cat_filter == $cat) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <!-- Data List -->
        <div class="flex-1 px-6 md:px-12 py-8">
            <div class="w-full">
                
                <!-- Desktop Only Header Labels -->
                <div class="hidden md:grid grid-cols-12 pb-6 border-b border-gray-100 mb-4">
                    <div class="col-span-6 text-[9px] font-black uppercase tracking-widest text-gray-300">Article Details</div>
                    <div class="col-span-2 text-[9px] font-black uppercase tracking-widest text-gray-300">Category</div>
                    <div class="col-span-2 text-[9px] font-black uppercase tracking-widest text-gray-300">Published</div>
                    <div class="col-span-1 text-[9px] font-black uppercase tracking-widest text-gray-300 text-center">Reads</div>
                    <div class="col-span-1 text-[9px] font-black uppercase tracking-widest text-gray-300 text-right">Actions</div>
                </div>

                <!-- Dynamic Rows -->
                <div class="divide-y divide-gray-50 md:divide-none">
                    <?php if(empty($posts)): ?>
                        <div class="py-20 text-center text-[10px] font-black uppercase text-gray-300 tracking-widest">No matching articles.</div>
                    <?php endif; ?>

                    <?php foreach($posts as $post): ?>
                    <div class="flex flex-col md:grid md:grid-cols-12 py-6 items-start md:items-center gap-4 md:gap-0 md:border-b md:border-gray-50 hover:border-black transition-colors group">
                        
                        <!-- Title & Slug (Always prominent) -->
                        <div class="w-full md:col-span-6 md:pr-10">
                            <h3 class="text-[15px] font-black text-black group-hover:text-red-600 transition-colors uppercase leading-tight mb-1">
                                <a href="/admin/edit.php?id=<?php echo urlencode($post['id']); ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h3>
                            <p class="text-[10px] font-mono text-gray-400">/<?php echo htmlspecialchars($post['slug']); ?></p>
                        </div>

                        <!-- Meta Row for Mobile (Horizontal flex on small, Desktop grid items) -->
                        <div class="w-full flex items-center flex-wrap gap-x-4 gap-y-2 md:contents">
                            
                            <!-- Category -->
                            <div class="md:col-span-2">
                                <span class="text-[9px] font-black uppercase tracking-widest text-red-600 whitespace-nowrap">
                                    <?php echo htmlspecialchars($post['category']); ?>
                                </span>
                            </div>

                            <!-- Date -->
                            <div class="md:col-span-2">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">
                                    <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                </span>
                            </div>

                            <!-- Views -->
                            <div class="md:col-span-1 md:text-center">
                                <span class="text-[11px] font-black text-black whitespace-nowrap">
                                    <?php echo number_format($post['views']); ?> <span class="md:hidden text-[9px] text-gray-300 font-bold ml-1">READS</span>
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                    <div class="md:col-span-1 flex justify-end gap-6 items-center w-full md:w-auto pt-4 md:pt-0 border-t border-gray-50 md:border-none">
                        <a href="/admin/edit.php?id=<?php echo urlencode($post['id']); ?>" class="flex items-center gap-2 text-gray-300 hover:text-black transition-colors">
                            <span class="md:hidden text-[9px] font-black uppercase tracking-widest">Edit</span>
                            <i class="bi bi-pencil-fill text-xs"></i>
                        </a>
                        <a href="/admin/posts.php?delete=<?php echo $post['id']; ?>" onclick="return confirm('Confirm permanent deletion?')" class="flex items-center gap-2 text-gray-300 hover:text-red-600 transition-colors">
                            <span class="md:hidden text-[9px] font-black uppercase tracking-widest">Delete</span>
                            <i class="bi bi-trash-fill text-xs"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="mt-12 py-10 flex items-center justify-between border-t border-gray-50">
                <span class="text-[9px] font-black uppercase tracking-widest text-gray-300">Page <?php echo $page; ?> / <?php echo $total_pages; ?></span>
                <div class="flex gap-8">
                    <?php if($page > 1): ?>
                        <a href="?<?php echo buildQueryString(['page' => $page - 1]); ?>" class="text-[9px] font-black uppercase tracking-widest text-black hover:text-red-600 transition-all">Prev</a>
                    <?php endif; ?>
                    <?php if($page < $total_pages): ?>
                        <a href="?<?php echo buildQueryString(['page' => $page + 1]); ?>" class="text-[9px] font-black uppercase tracking-widest text-black hover:text-red-600 transition-all">Next</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>