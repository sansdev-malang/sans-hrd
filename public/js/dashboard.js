/**
 * SANS Malang School Information System
 * Dashboard Animations & Interactivity
 * Using Anime.js
 */

document.addEventListener('DOMContentLoaded', () => {
    // Stat Counter Animations (Lightweight for dashboard counters if present)
    const animateCounters = () => {
        const counters = document.querySelectorAll('.stat-counter');
        counters.forEach(counter => {
            const targetVal = parseInt(counter.getAttribute('data-target') || '0', 10);
            counter.innerHTML = targetVal.toLocaleString('id-ID');
        });
    };

    // Theme Toggle Switch (Dark / Light Mode)
    const setupThemeToggle = () => {
        const themeToggleBtn = document.getElementById('theme-toggle');
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                }
            });
        }
    };

    // 7. Sidebar Toggle for Mobile & Desktop (Burger & Close Arrow)
    const setupSidebarToggle = () => {
        const toggleBtn = document.getElementById('sidebar-toggle');
        const closeBtn = document.getElementById('sidebar-close');
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        
        if (sidebar) {
            const isDesktop = () => window.innerWidth >= 768;

            const openSidebarMobile = () => {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                document.body.classList.add('sidebar-open');
                if (backdrop) {
                    backdrop.classList.remove('hidden');
                    setTimeout(() => {
                        backdrop.classList.remove('opacity-0');
                        backdrop.classList.add('opacity-100');
                    }, 20);
                }
            };
            
            const closeSidebarMobile = () => {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                document.body.classList.remove('sidebar-open');
                if (backdrop) {
                    backdrop.classList.remove('opacity-100');
                    backdrop.classList.add('opacity-0');
                    setTimeout(() => {
                        backdrop.classList.add('hidden');
                    }, 300);
                }
            };

            const toggleSidebar = () => {
                if (isDesktop()) {
                    document.body.classList.toggle('sidebar-collapsed');
                } else {
                    if (sidebar.classList.contains('-translate-x-full')) {
                        openSidebarMobile();
                    } else {
                        closeSidebarMobile();
                    }
                }
            };

            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleSidebar);
            }

            // Tambahan: tombol "Menu" mobile (data-sidebar-toggle)
            document.querySelectorAll('[data-sidebar-toggle]').forEach(btn => {
                btn.addEventListener('click', toggleSidebar);
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    if (isDesktop()) {
                        document.body.classList.add('sidebar-collapsed');
                    } else {
                        closeSidebarMobile();
                    }
                });
            }

            if (backdrop) {
                backdrop.addEventListener('click', closeSidebarMobile);
            }

            window.addEventListener('resize', () => {
                if (isDesktop()) {
                    document.body.classList.remove('sidebar-open');
                    if (backdrop) {
                        backdrop.classList.add('hidden');
                        backdrop.classList.remove('opacity-100');
                        backdrop.classList.add('opacity-0');
                    }
                    sidebar.classList.remove('translate-x-0');
                    sidebar.classList.add('-translate-x-full');
                }
            });
        }
    };

    // 8. Sidebar Scroll Preservation & Active Item Auto-Scroll
    const setupSidebarScroll = () => {
        const sidebarScroll = document.getElementById('sidebar-nav-container') || document.querySelector('#sidebar .overflow-y-auto');
        if (!sidebarScroll) return;

        // Save scroll position on scroll and link clicks
        let scrollTimeout;
        sidebarScroll.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                sessionStorage.setItem('sidebar_scroll_top', sidebarScroll.scrollTop);
            }, 100);
        }, { passive: true });

        sidebarScroll.addEventListener('click', (e) => {
            if (e.target.closest('a')) {
                sessionStorage.setItem('sidebar_scroll_top', sidebarScroll.scrollTop);
            }
        });

        window.addEventListener('beforeunload', () => {
            sessionStorage.setItem('sidebar_scroll_top', sidebarScroll.scrollTop);
        });

        // Find active menu item
        const findActiveItem = () => {
            // 1. Direct class matching
            let active = sidebarScroll.querySelector(
                'a.bg-slate-100, a.text-indigo-600, a.bg-slate-50, .menu-item.bg-slate-100, a.font-semibold:not(h1):not(h2):not(h3):not(h4)'
            );
            if (active) return active;

            // 2. URL pathname matching fallback
            const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
            const links = Array.from(sidebarScroll.querySelectorAll('a[href]'));
            for (const link of links) {
                try {
                    const linkPath = new URL(link.href, window.location.origin).pathname.replace(/\/$/, '') || '/';
                    if (linkPath === currentPath && linkPath !== '') {
                        return link;
                    }
                } catch (err) {}
            }
            return null;
        };

        const activeItem = findActiveItem();
        const savedScroll = sessionStorage.getItem('sidebar_scroll_top');
        let hasRestored = false;

        // Restore saved scroll position immediately if available
        if (savedScroll !== null) {
            const scrollVal = parseInt(savedScroll, 10);
            if (!isNaN(scrollVal)) {
                sidebarScroll.scrollTop = scrollVal;
                hasRestored = true;
            }
        }

        // Ensure active item is within comfortable visible range
        const scrollActiveIntoView = () => {
            if (!activeItem) return;
            const containerRect = sidebarScroll.getBoundingClientRect();
            const itemRect = activeItem.getBoundingClientRect();

            // Check if item is above or below the container's visible bounds
            const isAbove = itemRect.top < containerRect.top + 20;
            const isBelow = itemRect.bottom > containerRect.bottom - 20;

            if (isAbove || isBelow || !hasRestored) {
                activeItem.scrollIntoView({
                    block: 'nearest',
                    behavior: hasRestored ? 'instant' : 'auto'
                });
            }
        };

        // Run immediately
        scrollActiveIntoView();

        // Run after potential Alpine.js accordion collapse/expand animation completes
        setTimeout(scrollActiveIntoView, 150);
        setTimeout(scrollActiveIntoView, 350);
    };

    // Execute initial setup
    animateCounters();
    setupThemeToggle();
    setupSidebarToggle();
    setupSidebarScroll();
});
