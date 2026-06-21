<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fix paths for InfinityFree
require_once __DIR__ . '/auth.php'; 
require_once dirname(__DIR__) . '/config/database.php';

// 1. Initialize safe default values
$total_posts = 0; 
$total_views = 0; 
$concurrent_users = 0; 
$top_posts = [];
$weekly_labels = []; 
$weekly_counts = [];
$dashboard_error = null;

// 2. Safely attempt to fetch data
try {
    $q1 = db()->query("SELECT COUNT(*) FROM posts");
    if ($q1) $total_posts = (int) $q1->fetchColumn();

    $q2 = db()->query("SELECT COUNT(*) FROM analytics");
    if ($q2) $total_views = (int) $q2->fetchColumn();

    $q3 = db()->query("SELECT COUNT(DISTINCT ip_address) FROM analytics WHERE created_at > " . dbDateExpression('5_minutes_ago'));
    if ($q3) $concurrent_users = (int) $q3->fetchColumn();

    $q4 = db()->query("SELECT title, views, category FROM posts ORDER BY views DESC LIMIT 5");
    if ($q4) $top_posts = $q4->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    $q5 = db()->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM analytics WHERE created_at > " . dbDateExpression('7_days_ago') . " GROUP BY DATE(created_at) ORDER BY date ASC");
    if ($q5) {
        $weekly_data = $q5->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach($weekly_data as $row) {
            $weekly_labels[] = date('D', strtotime($row['date']));
            $weekly_counts[] = $row['count'];
        }
    }
} catch (Throwable $e) {
    $dashboard_error = $e->getMessage();
}

$php_version = phpversion();
$start_time = $_SERVER["REQUEST_TIME_FLOAT"] ?? microtime(true);
$load_time = microtime(true) - $start_time;
?>
<?php include __DIR__ . '/includes/head.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="flex min-h-screen bg-transparent">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- mt-16 clears the fixed mobile header, md:mt-0 removes it for desktop -->
    <main class="flex-1 flex flex-col mt-16 md:mt-0 md:ml-[280px] w-full min-w-0">
        
        <!-- Plain Header: No background, no border -->
        <header class="px-6 md:px-12 py-6 md:py-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 mb-1 block">Dashboard</span>
                <div class="flex items-center gap-3">
                   <h1 class="text-xl md:text-2xl font-black uppercase tracking-widest text-black">Intelligence</h1>
                   <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2"><span class="animate-ping absolute h-full w-full rounded-none bg-red-400 opacity-75"></span><span class="relative rounded-none h-2 w-2 bg-red-500"></span></span>
                        <span class="text-[9px] font-black uppercase tracking-widest text-red-600">Live Telemetry</span>
                   </div>
                </div>
            </div>
            <div class="hidden md:block text-right">
                <p class="text-[9px] font-black uppercase text-gray-400">PHP Node</p>
                <p class="text-xs font-bold text-black"><?php echo $php_version; ?></p>
            </div>
        </header>

        <!-- Plain Error Display -->
        <?php if ($dashboard_error): ?>
        <div class="mx-6 md:mx-12 mb-8 text-red-600 text-[11px] font-bold uppercase tracking-widest">
            System Error: <?php echo htmlspecialchars($dashboard_error); ?>
        </div>
        <?php endif; ?>

        <div class="p-6 md:p-12 pt-0 space-y-12">
            
            <!-- Metric Rows: No background, No border, No radius -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-y-10 gap-x-6">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 block mb-2">Visitors</span>
                    <h3 class="text-4xl md:text-5xl font-black text-red-600 leading-none"><?php echo number_format($concurrent_users); ?></h3>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 block mb-2">Articles</span>
                    <h3 class="text-4xl md:text-5xl font-black text-black leading-none"><?php echo number_format($total_posts); ?></h3>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 block mb-2">Impressions</span>
                    <h3 class="text-4xl md:text-5xl font-black text-black leading-none"><?php echo number_format($total_views); ?></h3>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 block mb-2">Latency</span>
                    <h3 class="text-4xl md:text-5xl font-black text-black leading-none"><?php echo round($load_time * 1000, 1); ?><span class="text-lg text-gray-300">ms</span></h3>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
                <!-- Chart Area: No background, No border -->
                <div class="lg:col-span-2">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-8 block">Traffic Velocity</span>
                    <div class="w-full h-[300px]">
                        <canvas id="velocityChart"></canvas>
                    </div>
                </div>

                <!-- Popular Content: No background, No border -->
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-8 block">Top Content</span>
                    <div class="space-y-8">
                        <?php if (empty($top_posts)): ?>
                            <p class="text-[10px] text-gray-300 font-black uppercase tracking-widest">Database Empty</p>
                        <?php else: ?>
                            <?php foreach($top_posts as $post): ?>
                            <div class="flex justify-between items-start gap-4">
                                <div class="min-w-0">
                                    <h4 class="text-[13px] font-black text-black leading-tight uppercase truncate mb-1"><?php echo htmlspecialchars($post['title']); ?></h4>
                                    <span class="text-[9px] font-black text-red-600 uppercase tracking-widest"><?php echo htmlspecialchars($post['category']); ?></span>
                                </div>
                                <span class="text-[11px] font-black text-gray-400 shrink-0"><?php echo number_format($post['views']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    const ctx = document.getElementById('velocityChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($weekly_labels); ?>,
            datasets: [{
                label: 'Views',
                data: <?php echo json_encode($weekly_counts); ?>,
                borderColor: '#000000',
                backgroundColor: 'transparent',
                borderWidth: 4,
                pointBackgroundColor: '#FF0033',
                pointBorderColor: '#000',
                pointBorderWidth: 0,
                pointRadius: 0,
                pointHoverRadius: 6,
                tension: 0, // Set to 0 for a sharp, raw look
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false, beginAtZero: true },
                x: { 
                    grid: { display: false }, 
                    border: { display: false }, 
                    ticks: { font: { size: 9, weight: '900' }, color: '#9ca3af' } 
                }
            },
            interaction: { intersect: false, mode: 'index' }
        }
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>