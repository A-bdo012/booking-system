<?php
// includes/footer.php
?>
<footer>
    <div class="container">
        <p>© <?= date('Y') ?> نظام إدارة الحجوزات الذكي (Booking System). جميع الحقوق محفوظة.</p>
    </div>
</footer>

<script src="assets/js/main.js"></script>
<?php if (isset($extraScripts)): ?>
    <?php foreach ($extraScripts as $script): ?>
        <script src="<?= $script ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
