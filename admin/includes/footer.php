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
});
</script>
</body>
</html>