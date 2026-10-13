<footer class="main-footer">
    <strong>&copy; <?= date('Y') ?> SATRACO Construction</strong>
    <span class="d-none d-sm-inline">— <em class="footer-slogan">Excellence is our passion</em></span>
    <div class="float-right d-none d-sm-inline-block">
        Plateforme du concours d'architecture · <b>v1.0</b>
    </div>
</footer>

</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="<?= base_url('assets/v2/adminlte/plugins/') ?>jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?= base_url('assets/v2/adminlte/plugins/') ?>bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- overlayScrollbars -->
<script src="<?= base_url('assets/v2/adminlte/plugins/') ?>overlayScrollbars/js/jquery.overlayScrollbars.min.js">
</script>
<!-- ChartJS (statistiques du tableau de bord) -->
<script src="<?= base_url('assets/v2/adminlte/plugins/') ?>chart.js/Chart.min.js"></script>
<!-- AdminLTE App -->
<script src="<?= base_url('assets/v2/adminlte/dist/') ?>js/adminlte.min.js"></script>

<?php
/*
 * Scripts propres à une page : depuis le contrôleur,
 *   $data['scripts'] = ['assets/js/candidats.js'];
 */
if (!empty($scripts)):
    foreach ((array) $scripts as $script): ?>
        <script src="<?= base_url($script) ?>"></script>
<?php endforeach;
endif; ?>
</body>

</html>