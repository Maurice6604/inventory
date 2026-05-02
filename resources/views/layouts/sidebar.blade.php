<aside class="w-64 bg-[hsl(var(--sidebar-bg))] flex flex-col h-full shrink-0 relative z-20" style="box-shadow: 4px 0 24px rgba(0,0,0,0.15);">

    <!-- Brand -->
    <div class="h-16 flex items-center px-6 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-[hsl(var(--primary))] rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div>
                <span class="text-white font-bold text-sm tracking-tight">InventoryMS</span>
                <p class="text-[hsl(var(--sidebar-text))] text-xs">Management System</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-5 px-3 space-y-1">

        <p class="px-3 mb-2 text-[10px] font-bold text-white/30 uppercase tracking-widest">Main</p>

        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Dashboard
        </a>

        <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            Products
        </a>

        <a href="{{ route('categories.index') }}" class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
            Categories
        </a>

        <a href="{{ route('stock.history') }}" class="sidebar-link {{ request()->routeIs('stock.history') ? 'active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Stock History
        </a>

        @if(Auth::user()->isAdmin())
        <div class="pt-5 mt-4 border-t border-white/10">
            <p class="px-3 mb-2 text-[10px] font-bold text-white/30 uppercase tracking-widest">Analytics</p>
            <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Business Reports
            </a>
        </div>
        @endif

    </nav>

    <!-- User Footer -->
    <div class="p-4 border-t border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-[hsl(var(--primary))] rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm font-semibold truncate">{{ Auth::user()->name }}</p>
                <p class="text-[hsl(var(--sidebar-text))] text-xs truncate capitalize">{{ Auth::user()->role }}</p>
            </div>
        </div>
    </div>

</aside>
