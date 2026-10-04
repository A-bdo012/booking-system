<?php
// admin/logout.php
require_once __DIR__ . '/../config/db.php';

// إلغاء جلسة المدير فقط دون المساس بالعميل
unset($_SESSION['admin_user']);

header('Location: login.php');
exit;
