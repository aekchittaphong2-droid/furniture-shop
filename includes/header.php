<?php
// ເລີ່ມ session ຖ້າຍັງບໍ່ໄດ້ເລີ່ມ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'ຮ້ານເຟີນິເຈີອອນໄລນ໌'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Lao Font -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <?php
    $css_path = '/assets/css/style.css';
    if(isset($isAdmin) && $isAdmin) {
        $css_path = '../assets/css/style.css';
    } elseif(strpos($_SERVER['REQUEST_URI'], '/user/') !== false) {
        $css_path = '../assets/css/style.css';
    }
    ?>
    <link rel="stylesheet" href="<?php echo $css_path; ?>">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>