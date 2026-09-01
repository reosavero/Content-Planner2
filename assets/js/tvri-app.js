







(function() {
    'use strict';

    const APP = {
        baseUrl: window.BASE_URL || '',
        csrfToken: window.CSRF_TOKEN || '',
        theme: localStorage.getItem('tvri-theme') || 'light',
    };

    


    function initApp() {
        initTheme();
        initSidebar();
        initSidebarCollapse();
        initSidebarSearch();
        initDropdowns();
        initSearch();
        initSearchShortcut();
        initNotifications();
        initForms();
        initTables();
        initCards();
        initQuickCreate();
        initFlashToasts();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initApp);
    } else {
        initApp();
    }

    


    function initTheme() {
        const html = document.documentElement;
        const saved = localStorage.getItem('tvri-theme') || 'light';
        html.setAttribute('data-theme', saved);
        APP.theme = saved;
        updateThemeIcon();
    }

    window.toggleTheme = function() {
        const html = document.documentElement;
        const current = html.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('tvri-theme', next);
        APP.theme = next;
        updateThemeIcon();
        
        if (window.dispatchEvent) {
            window.dispatchEvent(new CustomEvent('themeChange', { detail: { theme: next } }));
        }
    };

    function updateThemeIcon() {
        const icon = document.getElementById('themeIcon');
        if (!icon) return;
        icon.className = APP.theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
    }

    


    function initSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                toggleSidebar();
            }
        });
        
        
        document.querySelectorAll('.sidebar-item').forEach(function(item) {
            item.addEventListener('click', function() {
                if (window.innerWidth < 1024) {
                    toggleSidebar();
                }
            });
        });
    }

    window.toggleSidebar = function() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (!sidebar) return;
        
        const isOpen = sidebar.classList.contains('open');
        sidebar.classList.toggle('open');
        if (backdrop) {
            backdrop.classList.toggle('show');
        }
        document.body.style.overflow = isOpen ? '' : 'hidden';
    };
    
    
    function initSidebarCollapse() {
        document.querySelectorAll('.sidebar-section-toggle').forEach(function(btn) {
            var section = btn.closest('.sidebar-section');
            var sectionKey = section ? 'sidebar-section-' + (section.dataset.section || '0') : '';
            
            
            if (section && !section.querySelector('.sidebar-item.active')) {
                var savedState = localStorage.getItem(sectionKey);
                if (savedState === 'collapsed') {
                    btn.setAttribute('aria-expanded', 'false');
                }
            }
            
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var wasExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !wasExpanded);
                
                
                if (section && sectionKey) {
                    localStorage.setItem(sectionKey, !wasExpanded ? 'collapsed' : 'expanded');
                }
            });
        });
    }
    
    
    function initSidebarSearch() {
        var input = document.getElementById('sidebarSearch');
        if (!input) return;
        
        input.addEventListener('input', function() {
            var q = this.value.toLowerCase().trim();
            var items = document.querySelectorAll('.sidebar-item');
            var sections = document.querySelectorAll('.sidebar-section');
            
            items.forEach(function(item) {
                var label = item.querySelector('.sidebar-item-label');
                if (!label) return;
                var text = label.textContent.toLowerCase();
                var match = !q || text.includes(q);
                item.style.display = match ? '' : 'none';
            });
            
            
            sections.forEach(function(section) {
                var visibleItems = section.querySelectorAll('.sidebar-item[style*="display: none"]');
                var totalItems = section.querySelectorAll('.sidebar-item').length;
                if (visibleItems.length === totalItems && q) {
                    section.style.display = 'none';
                } else {
                    section.style.display = '';
                }
            });
        });
    }
    
    
    function initSearchShortcut() {
        document.addEventListener('keydown', function(e) {
            
            if (e.key === '/' && !e.ctrlKey && !e.metaKey) {
                var tag = e.target.tagName;
                if (tag !== 'INPUT' && tag !== 'TEXTAREA' && tag !== 'SELECT') {
                    e.preventDefault();
                    var searchInput = document.getElementById('globalSearch');
                    if (searchInput) {
                        searchInput.focus();
                    }
                }
            }
            
            if (e.key === 'Escape') {
                var searchInput = document.getElementById('globalSearch');
                if (searchInput && document.activeElement === searchInput) {
                    searchInput.blur();
                }
            }
        });
    }

    


    function initDropdowns() {
        document.addEventListener('click', function(e) {
            
            var dropdown = e.target.closest('.dropdown');
            var quickCreate = e.target.closest('.topbar-quick-create');
            var notifWrapper = e.target.closest('.topbar-notif-wrapper');
            
            
            if (quickCreate) {
                
                return;
            }
            
            
            if (notifWrapper) {
                return;
            }
            
            if (!dropdown) {
                document.querySelectorAll('.dropdown-menu.show').forEach(function(m) {
                    m.classList.remove('show');
                });
                document.querySelectorAll('.topbar-notif-dropdown.show').forEach(function(m) {
                    m.classList.remove('show');
                });
                return;
            }
            
            const menu = dropdown.querySelector('.dropdown-menu');
            if (!menu) return;
            
            const isOpen = menu.classList.contains('show');
            document.querySelectorAll('.dropdown-menu.show').forEach(function(m) {
                m.classList.remove('show');
            });
            document.querySelectorAll('.topbar-notif-dropdown.show').forEach(function(m) {
                m.classList.remove('show');
            });
            if (!isOpen) {
                menu.classList.add('show');
            }
        });
    }

    window.toggleDropdown = function(el) {
        var dropdown = el.closest('.dropdown');
        if (!dropdown) {
            
            var qcMenu = el;
            if (qcMenu && qcMenu.classList.contains('dropdown-menu')) {
                var isOpen = qcMenu.classList.contains('show');
                document.querySelectorAll('.dropdown-menu.show').forEach(function(m) {
                    m.classList.remove('show');
                });
                document.querySelectorAll('.topbar-notif-dropdown.show').forEach(function(m) {
                    m.classList.remove('show');
                });
                if (!isOpen) {
                    qcMenu.classList.add('show');
                }
            }
            return;
        }
        var menu = dropdown.querySelector('.dropdown-menu');
        if (!menu) return;
        
        var isOpen = menu.classList.contains('show');
        document.querySelectorAll('.dropdown-menu.show').forEach(function(m) {
            m.classList.remove('show');
        });
        document.querySelectorAll('.topbar-notif-dropdown.show').forEach(function(m) {
            m.classList.remove('show');
        });
        if (!isOpen) {
            menu.classList.add('show');
        }
    };
    
    
    window.openModal = function(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('show');
            var backdrop = modal.querySelector('.modal-backdrop');
            if (backdrop) backdrop.classList.add('show');
        }
    };
    window.closeModal = function(id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('show');
            var backdrop = modal.querySelector('.modal-backdrop');
            if (backdrop) backdrop.classList.remove('show');
        }
    };
    
    
    function initQuickCreate() {
        var qcBtn = document.querySelector('.topbar-quick-create');
        if (!qcBtn) return;
        
        qcBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var menu = this.nextElementSibling;
            if (!menu || !menu.classList.contains('dropdown-menu')) return;
            
            var isOpen = menu.classList.contains('show');
            document.querySelectorAll('.dropdown-menu.show, .topbar-notif-dropdown.show').forEach(function(m) {
                m.classList.remove('show');
            });
            if (!isOpen) {
                menu.classList.add('show');
                
                menu.style.right = '0';
            }
        });
    }
    
    
    window.toggleSidebarUserMenu = function(event) {
        if (event) event.stopPropagation();
        var menu = document.getElementById('sidebarUserMenu');
        if (!menu) return;
        menu.classList.toggle('show');
    };
    
    
    document.addEventListener('click', function(e) {
        var menu = document.getElementById('sidebarUserMenu');
        if (!menu) return;
        if (!e.target.closest('.sidebar-footer')) {
            menu.classList.remove('show');
        }
    });

    


    function initSearch() {
        const input = document.getElementById('globalSearch');
        if (!input) return;

        let debounceTimer;
        const resultsContainer = document.createElement('div');
        resultsContainer.className = 'dropdown-menu';
        resultsContainer.style.cssText = 'position:absolute;top:100%;left:0;right:0;display:none;z-index:1000;margin-top:4px;';
        input.parentElement.style.position = 'relative';
        input.parentElement.appendChild(resultsContainer);

        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const q = this.value.trim();
            if (q.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }
            debounceTimer = setTimeout(function() {
                fetch(APP.baseUrl + '/api/planning/search?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (!res.success || !res.data || !res.data.length) {
                            resultsContainer.innerHTML = '<div style="padding:12px;text-align:center;font-size:var(--text-xs);color:var(--text-tertiary);">Tidak ditemukan</div>';
                            resultsContainer.style.display = 'block';
                            return;
                        }
                        var html = '';
                        res.data.forEach(function(item) {
                            html += '<a href="' + APP.baseUrl + '/planning/' + item.id + '" class="dropdown-item" style="gap:8px;padding:8px 12px;">';
                            html += '   <div style="flex:1;min-width:0;">';
                            html += '       <div style="font-weight:500;font-size:var(--text-sm);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + escapeHtml(item.judul) + '</div>';
                            html += '       <div style="font-size:var(--text-xs);color:var(--text-tertiary);">' + (item.platform_name || '') + '</div>';
                            html += '   </div>';
                            html += '   <span class="badge status-' + item.status + '">' + item.status + '</span>';
                            html += '</a>';
                        });
                        resultsContainer.innerHTML = html;
                        resultsContainer.style.display = 'block';
                    })
                    .catch(function() {
                        resultsContainer.style.display = 'none';
                    });
            }, 300);
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.topbar-search')) {
                resultsContainer.style.display = 'none';
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                resultsContainer.style.display = 'none';
                this.blur();
            }
        });
    }

    



    
    var _notifData = [];
    var _notifUnread = 0;
    var _notifExpanded = {};  
    var _notifLoading = false;
    var _isPegawai = typeof window.IS_ADMIN !== 'undefined' ? !window.IS_ADMIN : false;

    function initNotifications() {
        loadNotifications();
        
        document.addEventListener('click', function(e) {
            var dd = document.getElementById('notifDropdown');
            var wrapper = e.target.closest('.topbar-notif-wrapper');
            if (dd && !dd.contains(e.target) && !wrapper) {
                dd.classList.remove('show');
            }
        });
    }


    function loadNotifications() {
        var endpoint = _isPegawai ? '/api/notifications/pegawai' : '/api/notifications';
        _notifLoading = true;
        
        fetch(APP.baseUrl + endpoint)
            .then(function(r) { return r.json(); })
            .then(function(res) {
                _notifLoading = false;
                if (!res.success) return;
                
                var list = res.data && res.data.data ? res.data.data : (res.data || []);
                var unreadCount = res.data && res.data.unreadCount !== undefined ? res.data.unreadCount : 0;
                
                _notifData = list;
                _notifUnread = unreadCount;
                renderNotifications(list, unreadCount);
            })
            .catch(function() { _notifLoading = false; });
    }

    function renderNotifications(notifs, unreadCount) {
        var list = document.getElementById('notifList');
        var dot = document.getElementById('notifDot');
        var label = document.getElementById('notifCountLabel');
        if (!list) return;
        
        updateBadge(unreadCount);
        if (label) {
            label.textContent = unreadCount;
            label.style.display = unreadCount > 0 ? 'inline-flex' : 'none';
        }
        
        if (!notifs || notifs.length === 0) {
            list.innerHTML = '<div class="empty-state py-4"><i class="bi bi-bell-slash" style="font-size:2rem;color:var(--text-tertiary);"></i><p style="font-size:var(--text-xs);color:var(--text-tertiary);margin-top:8px;">Tidak ada notifikasi</p></div>';
            return;
        }

        
        var groups = {};
        var ungrouped = [];
        notifs.forEach(function(n) {
            if (n.group_key && n.group_key !== 'intern_approval') {
                if (!groups[n.group_key]) {
                    groups[n.group_key] = {
                        key: n.group_key,
                        label: n.group_label || n.group_key,
                        items: [],
                        group_count: n.group_count || 0,
                        group_unread: n.group_unread || 0,
                    };
                }
                groups[n.group_key].items.push(n);
            } else {
                ungrouped.push(n);
            }
        });

        var html = '';
        
        
        Object.keys(groups).forEach(function(gk) {
            var g = groups[gk];
            var isExpanded = _notifExpanded[gk] !== false; 
            var groupUnread = g.group_unread || g.items.filter(function(i) { return !i.is_read; }).length;
            
            html += '<div class="notif-group">';
            html += '  <div class="notif-group-header" onclick="toggleNotifGroup(\'' + escapeJs(gk) + '\')">';
            html += '    <i class="bi bi-folder2-open" style="color:var(--tvri-blue);font-size:var(--text-sm);flex-shrink:0;"></i>';
            html += '    <span class="notif-group-label">' + escapeHtml(g.label) + '</span>';
            if (groupUnread > 0) {
                html += '    <span class="topbar-notif-count-badge" style="min-width:18px;height:18px;font-size:10px;">' + groupUnread + '</span>';
            }
            html += '    <span class="notif-group-count">' + g.items.length + '</span>';
            html += '    <i class="bi ' + (isExpanded ? 'bi-chevron-up' : 'bi-chevron-down') + '" style="font-size:var(--text-xs);color:var(--text-tertiary);transition:transform 0.2s;flex-shrink:0;"></i>';
            html += '    <button class="topbar-notif-markall" style="width:26px;height:26px;" onclick="event.stopPropagation();markGroupNotifRead(\'' + escapeJs(gk) + '\')" title="Tandai grup dibaca"><i class="bi bi-check-all" style="font-size:13px;"></i></button>';
            html += '  </div>';
            
            if (isExpanded) {
                html += '  <div class="notif-group-items">';
                g.items.forEach(function(n) {
                    html += renderNotifItem(n);
                });
                html += '  </div>';
            }
            html += '</div>';
        });
        
        
        ungrouped.forEach(function(n) {
            html += renderNotifItem(n);
        });
        
        list.innerHTML = html;
    }

    function renderNotifItem(n) {
        var icon = n.icon || (n.type === 'success' ? 'bi-check-circle' :
                   n.type === 'error' ? 'bi-exclamation-circle' :
                   n.type === 'warning' ? 'bi-exclamation-triangle' : 'bi-info-circle');
        var iconColor = n.type === 'success' ? 'var(--success)' :
                        n.type === 'error' ? 'var(--danger)' :
                        n.type === 'warning' ? 'var(--warning)' : 'var(--tvri-blue)';
        
        var href = n.link;
        if (href && href !== '#') {
            if (href.indexOf('http') !== 0 && href.indexOf('/') === 0) {
                href = APP.baseUrl + href;
            }
        } else {
            href = 'javascript:void(0)';
        }
        
        return '<a href="' + href + '" class="notif-item' + (!n.is_read ? ' is-unread' : '') + '" onclick="markNotifRead(' + n.id + ', event)">' +
               '  <div style="display:flex;align-items:flex-start;gap:10px;width:100%;">' +
               '    <span class="notif-item-icon type-' + (n.type || 'info') + '"><i class="bi ' + icon + '"></i></span>' +
               '    <div style="flex:1;min-width:0;">' +
               '      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">' +
               '        <span class="notif-item-title" style="flex:1;min-width:0;">' + escapeHtml(n.title) + '</span>' +
               '        <span class="notif-item-time">' + timeAgo(n.created_at) + '</span>' +
               '      </div>' +
               (n.message ? '      <div class="notif-item-message">' + escapeHtml(n.message) + '</div>' : '') +
               '    </div>' +
               '  </div>' +
               '</a>';
    }

    function updateBadge(count) {
        var dot = document.getElementById('notifDot');
        var label = document.getElementById('notifCountLabel');
        if (dot) {
            dot.style.display = count > 0 ? 'block' : 'none';
        }
        if (label) {
            label.textContent = count;
            label.style.display = count > 0 ? 'inline-flex' : 'none';
        }
    }

    

    window.markNotifRead = function(id, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        var href = event && event.currentTarget ? event.currentTarget.getAttribute('href') : null;

        var goTo = function() {
            if (href && href !== '#' && href !== 'javascript:void(0)') {
                window.location.href = href;
            }
        };

        fetch(APP.baseUrl + '/api/notifications/read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, _csrf_token: APP.csrfToken })
        }).then(function(r) { return r.json(); }).then(function(res) {
            if (res.success) {
                _notifUnread = Math.max(0, _notifUnread - 1);
                updateBadge(_notifUnread);
            }
            goTo();
        }).catch(function() {
            goTo();
        });
    };

    window.markAllNotifRead = function() {
        fetch(APP.baseUrl + '/api/notifications/mark-all-read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ _csrf_token: APP.csrfToken })
        }).then(function(r) { return r.json(); }).then(function(res) {
            if (res.success) {
                _notifUnread = 0;
                updateBadge(0);
                loadNotifications();
                if (typeof showToast === 'function') {
                    showToast('success', 'Semua notifikasi telah dibaca');
                }
            }
        }).catch(function() {});
    };

    window.toggleNotifGroup = function(groupKey) {
        _notifExpanded[groupKey] = _notifExpanded[groupKey] === false ? true : false;
        renderNotifications(_notifData, _notifUnread);
    };

    window.markGroupNotifRead = function(groupKey) {
        var ids = _notifData
            .filter(function(n) { return n.group_key === groupKey && !n.is_read; })
            .map(function(n) { return n.id; });
        
        if (ids.length === 0) return;
        
        fetch(APP.baseUrl + '/api/notifications/mark-multiple-read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: ids, _csrf_token: APP.csrfToken })
        }).then(function(r) { return r.json(); }).then(function(res) {
            if (res.success) {
                _notifUnread = Math.max(0, _notifUnread - ids.length);
                updateBadge(_notifUnread);
                loadNotifications();
            }
        }).catch(function() {});
    };

    
    function escapeJs(str) {
        if (!str) return '';
        return String(str).replace(/[\\']/g, '\\$&').replace(/"/g, '&quot;');
    }

    


    window.dismissSonnerToast = function(btn) {
        var toast = btn.closest('.sonner-toast, .toast');
        dismissSonnerToastEl(toast);
    };

    window.dismissSonnerToastEl = function(toast) {
        if (!toast || !toast.parentNode) return;
        toast.classList.add('toast-out');
        toast.style.transition = 'all 0.3s cubic-bezier(0.16, 1, 0.3, 1)';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-24px) scale(0.95)';
        setTimeout(function() {
            if (toast && toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    };

    window.showToast = function(type, title, message) {
        var container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            container.id = 'toastContainer';
            document.body.appendChild(container);
        }

        var normalizedType = type === 'danger' ? 'error' : (type || 'info');
        var icons = {
            success: 'check-circle-fill',
            error: 'x-circle-fill',
            warning: 'exclamation-triangle-fill',
            info: 'info-circle-fill'
        };
        var iconName = icons[normalizedType] || icons.info;

        var textContent = '';
        if (title && message && title !== message) {
            textContent = title + ': ' + message;
        } else {
            textContent = message || title || '';
        }

        var toast = document.createElement('div');
        toast.className = 'sonner-toast sonner-toast-' + normalizedType;
        toast.innerHTML =
            '<span class="sonner-icon">' +
                '<i class="bi bi-' + iconName + '"></i>' +
            '</span>' +
            '<div class="sonner-content">' +
                '<div class="sonner-message">' + escapeHtml(textContent) + '</div>' +
            '</div>';

        container.appendChild(toast);

        setTimeout(function() {
            dismissSonnerToastEl(toast);
        }, 3500);
    };

    function initFlashToasts() {
        var preRendered = document.querySelectorAll('#toastContainer .sonner-toast, #toastContainer .toast, .sonner-toast');
        preRendered.forEach(function(toast) {
            if (toast.dataset.dismissScheduled) return;
            toast.dataset.dismissScheduled = 'true';
            setTimeout(function() {
                dismissSonnerToastEl(toast);
            }, 3500);
        });
    }

    
    initFlashToasts();
    window.addEventListener('load', initFlashToasts);

    


    function initForms() {
        
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || !form.querySelector) return;
            var btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                setTimeout(function() {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner spinner-sm"></span> Memproses...';
                }, 10);
            }
        });
    }

    


    function initTables() {
        document.querySelectorAll('.table tbody tr').forEach(function(row) {
            row.addEventListener('mouseenter', function() {
                var actions = this.querySelector('.table-actions');
                if (actions) actions.style.opacity = '1';
            });
            row.addEventListener('mouseleave', function() {
                var actions = this.querySelector('.table-actions');
                if (actions) actions.style.opacity = '';
            });
        });
    }

    


    function initCards() {
        
        
        var cards = document.querySelectorAll('.row:not(.stagger) > .col-12, .row:not(.stagger) > .col-md-6, .row:not(.stagger) > .col-md-4, .row:not(.stagger) > .col-md-3');
        if (!cards.length) return;
        
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        cards.forEach(function(card, i) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.4s ease-out';
            card.style.transitionDelay = (i * 60) + 'ms';
            observer.observe(card);
        });
    }

    


    window.toggleModal = function(id, show) {
        var modal = document.getElementById(id);
        if (!modal) return;
        var backdrop = modal.querySelector('.modal-backdrop') || modal.previousElementSibling;
        
        if (show === undefined) {
            show = !modal.classList.contains('show');
        }
        
        if (show) {
            modal.classList.add('show');
            if (backdrop && backdrop.classList.contains('modal-backdrop')) {
                backdrop.classList.add('show');
            }
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.remove('show');
            if (backdrop && backdrop.classList.contains('modal-backdrop')) {
                backdrop.classList.remove('show');
            }
            document.body.style.overflow = '';
        }
    };

    
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            var modal = e.target.nextElementSibling;
            if (modal && modal.classList.contains('modal')) {
                toggleModal(modal.id, false);
            }
        }
    });

    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var modal = document.querySelector('.modal.show');
            if (modal) toggleModal(modal.id, false);
        }
    });

    



    
    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    
    function timeAgo(dateStr) {
        if (!dateStr) return '';
        var now = new Date();
        var date = new Date(dateStr);
        var diff = Math.floor((now - date) / 1000);
        
        if (diff < 60) return 'baru saja';
        if (diff < 3600) return Math.floor(diff / 60) + 'm';
        if (diff < 86400) return Math.floor(diff / 3600) + 'j';
        if (diff < 2592000) return Math.floor(diff / 86400) + 'h';
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    }

    


    
    
    window.startAutoPostPolling = function(interval) {
        interval = interval || 10000; 
        setInterval(function() {
            var indicator = document.querySelector('.auto-post-indicator');
            if (!indicator) return;
            
            fetch(BASE_URL + '/auto-post/status')
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success && res.data) {
                        var latest = res.data[0];
                        if (latest) {
                            if (latest.action === 'success') {
                                indicator.className = 'auto-post-indicator success';
                                indicator.innerHTML = '<i class="bi bi-check-circle"></i> Auto Post: OK';
                            } else if (latest.action === 'failed') {
                                indicator.className = 'auto-post-indicator error';
                                indicator.innerHTML = '<i class="bi bi-exclamation-circle"></i> Auto Post: Error';
                            } else if (latest.action === 'posting' || latest.action === 'retry') {
                                indicator.className = 'auto-post-indicator running';
                                indicator.innerHTML = '<span class="spinner spinner-sm"></span> Auto Post: Running...';
                            }
                        }
                    }
                })
                .catch(function() {});
        }, interval);
    };

    
    window.processAutoPost = function() {
        var btn = document.getElementById('processQueueBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner spinner-sm"></span> Processing...';
        }
        
        fetch(BASE_URL + '/auto-post/process', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_csrf_token=' + encodeURIComponent(CSRF_TOKEN)
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            showToast(res.success ? 'success' : 'info', 'Auto Post', res.message);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-play"></i> Proses Antrian';
            }
            if (res.success && res.processed > 0) {
                setTimeout(function() { location.reload(); }, 2000);
            }
        })
        .catch(function() {
            showToast('error', 'Error', 'Gagal memproses antrian');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-play"></i> Proses Antrian';
            }
        });
    };

    
    window.reinitUI = function() {
        initTables();
        initCards();
    };

    


    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.font.size = 11;
        Chart.defaults.color = 'var(--text-secondary)';
        Chart.defaults.plugins.tooltip.backgroundColor = 'var(--surface)';
        Chart.defaults.plugins.tooltip.titleColor = 'var(--text-primary)';
        Chart.defaults.plugins.tooltip.bodyColor = 'var(--text-secondary)';
        Chart.defaults.plugins.tooltip.borderColor = 'var(--border-light)';
        Chart.defaults.plugins.tooltip.borderWidth = 1;
        Chart.defaults.plugins.tooltip.padding = 12;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.plugins.tooltip.displayColors = true;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.padding = 16;
    }

})();
