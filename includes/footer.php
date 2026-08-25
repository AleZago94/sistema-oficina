<!--begin::Footer-->
<footer class="app-footer">
  <div class="d-flex justify-content-between align-items-center w-100">
    <div>
      <strong>© 2026 Pacheco's Moto Service</strong>
      <span class="ms-2">Desenvolvido por NXT LVL TECH</span>
    </div>

    <div>
      <span>v1.0 Piloto</span>
    </div>
  </div>
</footer>
<!--end::Footer-->

</div>
<!--end::App Wrapper-->


<!-- OverlayScrollbars -->
<script
  src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
  crossorigin="anonymous">
</script>


<!-- Popper -->
<script
  src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
  crossorigin="anonymous">
</script>


<!-- Bootstrap -->
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
  crossorigin="anonymous">
</script>


<!-- AdminLTE -->
<script src="./adminlte/dist/js/adminlte.js"></script>


<!-- Configuração da scrollbar da sidebar -->
<script>
  const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';

  const Default = {
    scrollbarTheme: 'os-theme-light',
    scrollbarAutoHide: 'leave',
    scrollbarClickScroll: true,
  };

  document.addEventListener('DOMContentLoaded', function() {

    const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

    const isMobile = window.innerWidth <= 992;

    if (
      sidebarWrapper &&
      OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
      !isMobile
    ) {
      OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
        scrollbars: {
          theme: Default.scrollbarTheme,
          autoHide: Default.scrollbarAutoHide,
          clickScroll: Default.scrollbarClickScroll,
        },
      });
    }
  });
</script>

</body>

</html>