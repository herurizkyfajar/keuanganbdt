// assets/js/main.js
document.addEventListener('DOMContentLoaded', function () {
  // Auto hide alerts after 5 seconds
  setTimeout(function () {
    const alert = document.querySelector('.alert');
    if (alert) {
      alert.style.transition = 'opacity 0.4s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 400);
    }
  }, 5000);

  // Confirm delete helper
  window.confirmDelete = function (message) {
    return confirm(message || 'Yakin ingin menghapus data ini?');
  };

  // Currency formatter (IDR)
  window.formatCurrency = function (val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
  };

  // Dynamic table row add/remove for invoice lines
  document.querySelectorAll('.btn-add-line').forEach(btn => {
    btn.addEventListener('click', function () {
      const tbody = this.closest('table').querySelector('tbody');
      const firstRow = tbody.querySelector('tr');
      const clone = firstRow.cloneNode(true);
      clone.querySelectorAll('input,select').forEach(el => el.value = '');
      tbody.appendChild(clone);
    });
  });

  document.querySelectorAll('.btn-remove-line').forEach(btn => {
    btn.addEventListener('click', function () {
      const tbody = this.closest('tbody');
      if (tbody.children.length > 1) {
        this.closest('tr').remove();
      }
    });
  });

  // =============== Mobile Sidebar Toggle ===============
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  const menuToggle = document.getElementById('mobileMenuToggle');

  if (sidebar && backdrop && menuToggle) {
    function openSidebar() {
      sidebar.classList.add('show');
      backdrop.classList.add('show');
      document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
      sidebar.classList.remove('show');
      backdrop.classList.remove('show');
      document.body.style.overflow = '';
    }

    menuToggle.addEventListener('click', openSidebar);
    backdrop.addEventListener('click', closeSidebar);

    // Tutup sidebar saat klik link navigasi (mobile)
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
      link.addEventListener('click', closeSidebar);
    });

    // Tutup sidebar saat tombol Keluar diklik
    const logoutLink = document.querySelector('.sidebar-logout');
    if (logoutLink) {
      logoutLink.addEventListener('click', closeSidebar);
    }
  }

  // =============== User Dropdown ===============
  const userDropdownBtn = document.getElementById('userDropdown');
  const userDropdownMenu = document.getElementById('userDropdownMenu');
  if (userDropdownBtn && userDropdownMenu) {
    userDropdownBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      userDropdownMenu.classList.toggle('show');
    });
    document.addEventListener('click', function () {
      userDropdownMenu.classList.remove('show');
    });
  }
});
