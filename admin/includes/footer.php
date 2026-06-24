</div><!-- end admin-content -->
</div><!-- end admin-main -->
</div><!-- end admin-wrapper -->

<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
  const menuToggle = document.getElementById('menuToggle');
  if (menuToggle) menuToggle.style.display = 'flex';

  // Auto-hide alerts
  setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
      a.style.transition = 'opacity 0.5s';
      a.style.opacity = '0';
      setTimeout(() => a.remove(), 500);
    });
  }, 4000);

  // Filter Persistence Logic
  const filterForm = document.querySelector('form[method="GET"]');
  if (filterForm) {
    const pagePath = window.location.pathname;
    const storageKey = 'admin_filter_' + pagePath;
    const urlParams = new URLSearchParams(window.location.search);
    
    let hasFilterParams = false;
    const inputs = filterForm.querySelectorAll('input[name], select[name]');
    inputs.forEach(input => {
      if (input.name !== 'action' && urlParams.has(input.name)) {
        hasFilterParams = true;
      }
    });

    if (hasFilterParams) {
      sessionStorage.setItem(storageKey, window.location.search);
    } else {
      const savedFilters = sessionStorage.getItem(storageKey);
      if (savedFilters && (!urlParams.has('action') || urlParams.get('action') === 'list')) {
        window.location.search = savedFilters;
      }
    }
  }
});
</script>
</body>
</html>