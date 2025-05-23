<footer class="text-center mt-5 pb-5">
    <p class="text-secondary">&copy; <?= date("Y") ?> Ruang Cosplay - Bandar Lampung</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.21.2/dist/sweetalert2.all.min.js"></script>
<script type="text/javascript" src="/assets/js/bootstrap.js"></script>
<script type="text/javascript" src="/assets/js/script.js"></script>
<script src="/assets/js/particles.js"></script>
<script src="/assets/js/particles-app.js"></script>

<script type="text/javascript">
    <?php if (isset($_SESSION['error'])) { ?>
        Swal.fire({
            title: 'Wopps!',
            text: '<?php echo $_SESSION['error']; ?>',
            icon: 'error',
            confirmButtonText: 'OKE'
        })
    <?php
    }
    unset($_SESSION['error']);
    ?>

    <?php if (isset($_SESSION['info'])) { ?>
        Swal.fire({
            title: 'WoW',
            text: '<?php echo $_SESSION['info']; ?>',
            icon: 'info',
            confirmButtonText: 'OKE'
        })
    <?php
    }
    unset($_SESSION['info']);
    ?>

    <?php if (isset($_SESSION['success'])) { ?>
        Swal.fire({
            title: 'Successfully!',
            text: '<?php echo $_SESSION['success']; ?>',
            icon: 'success',
            confirmButtonText: 'OKE'
        })
    <?php
    }
    unset($_SESSION['success']);
    ?>
</script>
</body>

</html>