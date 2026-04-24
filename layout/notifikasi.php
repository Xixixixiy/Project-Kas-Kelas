<?php if (isset($_SESSION['error'])) : ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Waduh...',
            text: '<?= $_SESSION['error'] ?>',
            confirmButtonColor: '#d33',
        });
    </script>

    <?php unset($_SESSION['error']); ?>
<?php endif; ?>


<?php if (isset($_SESSION['success'])) : ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Mantap!',
            text: '<?= $_SESSION['success'] ?>',
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>