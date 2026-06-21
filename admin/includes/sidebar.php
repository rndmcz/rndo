<?php 
// Get the current URL path to determine active state
$request_uri = $_SERVER['REQUEST_URI'];
?>

<!-- Mobile Topbar (Fixed, No Border, Plain) -->
<header class="md:hidden fixed top-0 left-0 right-0 h-16 z-40 bg-white flex items-center justify-between px-6">
    <a href="/admin/index.php" class="flex items-center gap-1 group">
        <span class="text-[20px] font-[900] tracking-tighter text-black uppercase">CMS</span>
        <span class="inline-block h-2 w-2 bg-[#FF0033]"></span>
    </a>
    <button id="mobile-menu-btn" class="text-gray-800 p-2 focus:outline-none" aria-label="Open Menu">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" />
        </svg>
    </button>
</header>

<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/20 z-40 hidden opacity-0 transition-opacity duration-300 backdrop-blur-sm"></div>

<!-- Sidebar (Strictly Plain, No border, No shading) -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-[280px] bg-white z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col border-r border-gray-50 md:border-none">
    
    <!-- Brand Logo -->
    <div class="px-10 h-24 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-1">
            <span class="text-[20px] font-[900] tracking-tighter text-black uppercase">CMS</span>
            <span class="inline-block h-2 w-2 bg-[#FF0033]"></span>
        </div>
        <button id="close-sidebar-btn" class="md:hidden p-2 text-gray-400 hover:text-red-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation (Absolute Links, No .php) -->
    <div class="flex-1 overflow-y-auto custom-scrollbar px-6 py-4 space-y-12">
        
        <section>
            <h4 class="px-4 text-[10px] font-black uppercase tracking-[0.3em] text-gray-300 mb-6">Database</h4>
            <nav class="space-y-3">
                <!-- Dashboard -->
                <a href="/admin/index.php" class="flex items-center gap-4 px-4 py-2 text-[13px] font-black uppercase tracking-widest transition-all <?php echo (strpos($request_uri, 'index') !== false) ? 'text-[#FF0033]' : 'text-gray-400 hover:text-black'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard
                </a>
                <!-- Library -->
                <a href="/admin/posts.php" class="flex items-center gap-4 px-4 py-2 text-[13px] font-black uppercase tracking-widest transition-all <?php echo (strpos($request_uri, 'posts') !== false || strpos($request_uri, 'edit') !== false) ? 'text-[#FF0033]' : 'text-gray-400 hover:text-black'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Library
                </a>
                <!-- Compose -->
                <a href="/admin/editor.php" class="flex items-center gap-4 px-4 py-2 text-[13px] font-black uppercase tracking-widest transition-all <?php echo (strpos($request_uri, 'compose') !== false) ? 'text-[#FF0033]' : 'text-gray-400 hover:text-black'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Compose
                </a>
                <!-- Topics -->
                <a href="/admin/categories.php" class="flex items-center gap-4 px-4 py-2 text-[13px] font-black uppercase tracking-widest transition-all <?php echo (strpos($request_uri, 'categories') !== false) ? 'text-[#FF0033]' : 'text-gray-400 hover:text-black'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Topics
                </a>
                
            </nav>
        </section>

        <section>
            <h4 class="px-4 text-[10px] font-black uppercase tracking-[0.3em] text-gray-300 mb-6">System</h4>
            <nav class="space-y-3">
                <!-- Settings -->
                <a href="/admin/settings.php" class="flex items-center gap-4 px-4 py-2 text-[13px] font-black uppercase tracking-widest transition-all <?php echo (strpos($request_uri, 'settings') !== false) ? 'text-[#FF0033]' : 'text-gray-400 hover:text-black'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Config
                </a>
                <a href="/admin/ai-config.php" class="flex items-center gap-4 px-4 py-2 text-[13px] font-black uppercase tracking-widest transition-all <?php echo (strpos($request_uri, 'ai-config') !== false) ? 'text-[#FF0033]' : 'text-gray-400 hover:text-black'; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
    </svg>
    AI_Brain
</a>
                <!-- Live Site -->
                <a target="_blank" href="/" class="flex items-center gap-4 px-4 py-2 text-[13px] font-black uppercase tracking-widest text-gray-400 hover:text-black transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Live Site
                </a>
            </nav>
        </section>
    </div>

    <!-- User Profile (Plain, SVG Power Icon) -->
    <div class="p-8 shrink-0">
        <div class="flex items-center justify-between group">
            <a href="/admin/profile.php" class="flex flex-col">
                <span class="text-[12px] font-black text-black uppercase tracking-widest group-hover:text-[#FF0033] transition-colors">Admin_Node</span>
                <span class="text-[9px] text-gray-300 font-bold uppercase tracking-widest mt-0.5">Status: Active</span>
            </a>
            <a href="/admin/logout.php" class="text-gray-300 hover:text-[#FF0033] transition-colors p-2" title="Terminate Session">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 1112.728 0M12 3v9" />
                </svg>
            </a>
        </div>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('mobile-menu-btn');
    const closeBtn = document.getElementById('close-sidebar-btn');
    const overlay = document.getElementById('sidebar-overlay');
    const sidebar = document.getElementById('sidebar');

    function toggle() {
        if (!sidebar) return;
        const isHidden = sidebar.classList.contains('-translate-x-full');
        sidebar.classList.toggle('-translate-x-full', !isHidden);
        if (overlay) {
            overlay.classList.toggle('hidden', !isHidden);
            setTimeout(() => overlay.classList.toggle('opacity-100', isHidden), 10);
        }
        document.body.style.overflow = isHidden ? 'hidden' : '';
    }

    if(btn) btn.addEventListener('click', toggle);
    if(closeBtn) closeBtn.addEventListener('click', toggle);
    if(overlay) overlay.addEventListener('click', toggle);
});
</script>