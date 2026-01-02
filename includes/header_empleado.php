<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Portal del Empleado' ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../style/global.css">
    
    <?php if (isset($extra_css)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($extra_css) ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" href="../img/dremhlogoicon.png" type="image/png">
</head>
<body>
    
