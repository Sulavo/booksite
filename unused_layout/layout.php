<?php 
function base_layout($content,$header)
{?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo $header ?>

</head>

<body>
    <?php
    include '../includes/admin_navbar.php';
     echo $content ?>
</body>

</html>
<?php
}