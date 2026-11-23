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
});
