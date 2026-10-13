<!-- jQuery -->
<script src="<?= base_url('assets/v2/adminlte/') ?>plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?= base_url('assets/v2/adminlte/') ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="<?= base_url('assets/v2/adminlte/') ?>dist/js/adminlte.min.js"></script>

<script>
    // Afficher / masquer le mot de passe
    $(function() {
        $('.toggle-password').on('click', function() {
            var $input = $('#password');
            var isHidden = $input.attr('type') === 'password';
            $input.attr('type', isHidden ? 'text' : 'password');
            $('#togglePasswordIcon').toggleClass('fa-eye fa-eye-slash');
        });
    });
</script>
</body>

</html>