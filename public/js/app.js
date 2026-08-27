document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.app-sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle-btn');
    const toggleIcon = sidebarToggle ? sidebarToggle.querySelector('.toggle-icon, i') : null;

    function updateToggleIcon(isCollapsed) {
        if (toggleIcon) {
            if (isCollapsed) {
                toggleIcon.className = 'bi bi-chevron-right toggle-icon';
                sidebarToggle.setAttribute('title', 'Mở rộng menu');
            } else {
                toggleIcon.className = 'bi bi-chevron-left toggle-icon';
                sidebarToggle.setAttribute('title', 'Thu gọn menu');
            }
        }
    }

    if (sidebarToggle && sidebar) {
        const savedState = localStorage.getItem('sidebar_collapsed');
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
            updateToggleIcon(true);
        } else {
            updateToggleIcon(false);
        }

        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebar_collapsed', isCollapsed);
            updateToggleIcon(isCollapsed);
        });
    }

    // Sidebar Accordion Folder Toggle
    document.querySelectorAll('.sidebar-folder-toggle').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const folder = this.closest('.sidebar-folder');
            if (folder) {
                if (sidebar && sidebar.classList.contains('collapsed')) {
                    sidebar.classList.remove('collapsed');
                    localStorage.setItem('sidebar_collapsed', 'false');
                    updateToggleIcon(false);
                    folder.classList.add('open');
                    return;
                }
                folder.classList.toggle('open');
            }
        });
    });

    // Auto-teleport all app-modals to document.body on DOM ready to prevent layout reflow flickering
    document.querySelectorAll('.app-modal').forEach(modal => {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });

    window.openModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            requestAnimationFrame(() => {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            });
        }
    };

    window.closeModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    let modalMouseDownTarget = null;
    document.addEventListener('mousedown', function (e) {
        modalMouseDownTarget = e.target;
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.modal-close-btn') || e.target.closest('.btn-modal-close')) {
            const modal = e.target.closest('.app-modal');
            if (modal) {
                closeModal(modal.id);
            }
        } else if (e.target.classList.contains('app-modal')) {
            if (modalMouseDownTarget === e.target) {
                closeModal(e.target.id);
            }
        }
    });

    const avatarInput = document.getElementById('avatar-file-input');
    const avatarPreview = document.getElementById('avatar-preview-target');
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    avatarPreview.src = e.target.result;
                    showToast('Đã tải ảnh lên để xem trước!', 'success');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    window.showToast = function (message, type = 'info') {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'toast-item';
        
        let icon = 'bi-info-circle text-primary';
        let borderColor = 'var(--primary)';
        if (type === 'success') {
            icon = 'bi-check-circle-fill text-success';
            borderColor = '#2e7d32';
        } else if (type === 'danger' || type === 'error') {
            icon = 'bi-x-circle-fill text-danger';
            borderColor = '#d32f2f';
        } else if (type === 'warning') {
            icon = 'bi-exclamation-triangle-fill text-warning';
            borderColor = '#ed6c02';
        }

        toast.style.borderLeftColor = borderColor;
        toast.innerHTML = `
            <i class="bi ${icon} fs-5"></i>
            <div style="flex: 1; font-size: 14px; font-weight: 500;">${message}</div>
            <button type="button" style="background: none; border: none; color: #94a3b8; cursor: pointer;" onclick="this.parentElement.remove()">
                <i class="bi bi-x fs-5"></i>
            </button>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(40px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    };

    window.initUserActions = function () {
        document.querySelectorAll('.btn-edit-user').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const username = this.dataset.username;
                const phone = this.dataset.phone;
                const email = this.dataset.email;
                const role = this.dataset.role;
                const status = this.dataset.status;

                const targetInput = document.getElementById('edit-user-id');
                if (targetInput) {
                    targetInput.value = id || '';
                    if (document.getElementById('edit-user-name')) document.getElementById('edit-user-name').value = name || '';
                    if (document.getElementById('edit-user-username')) document.getElementById('edit-user-username').value = username || '';
                    if (document.getElementById('edit-user-phone')) document.getElementById('edit-user-phone').value = phone || '';
                    if (document.getElementById('edit-user-email')) document.getElementById('edit-user-email').value = email || '';
                    if (document.getElementById('edit-user-role')) document.getElementById('edit-user-role').value = role || 'user';
                    if (document.getElementById('edit-user-status')) document.getElementById('edit-user-status').value = status || 'active';
                    openModal('modal-edit-user');
                }
            });
        });

        document.querySelectorAll('.btn-lock-user').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const currentStatus = this.dataset.status;
                const isLocked = currentStatus === 'locked';

                const targetInput = document.getElementById('lock-user-id');
                if (targetInput) {
                    targetInput.value = id || '';
                    if (document.getElementById('lock-user-name')) document.getElementById('lock-user-name').textContent = name || '';
                    
                    const titleEl = document.getElementById('lock-modal-title');
                    const actionTextEl = document.getElementById('lock-action-text');
                    const btnSubmitEl = document.getElementById('btn-confirm-lock');

                    if (titleEl && actionTextEl && btnSubmitEl) {
                        if (isLocked) {
                            titleEl.innerHTML = '<i class="bi bi-unlock text-success"></i> Mở khóa tài khoản';
                            actionTextEl.textContent = 'mở khóa hoạt động cho';
                            btnSubmitEl.textContent = 'Mở khóa ngay';
                            btnSubmitEl.className = 'btn btn-primary';
                        } else {
                            titleEl.innerHTML = '<i class="bi bi-lock text-warning"></i> Khóa tài khoản';
                            actionTextEl.textContent = 'tạm thời khóa';
                            btnSubmitEl.textContent = 'Xác nhận khóa';
                            btnSubmitEl.className = 'btn btn-warning';
                        }
                    }
                    openModal('modal-lock-user');
                }
            });
        });

        document.querySelectorAll('.btn-delete-user-legacy').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const targetInput = document.getElementById('delete-user-id');
                if (targetInput) {
                    targetInput.value = id || '';
                    if (document.getElementById('delete-user-name')) document.getElementById('delete-user-name').textContent = name || '';
                    openModal('modal-delete-user');
                }
            });
        });
    };

    initUserActions();
});
