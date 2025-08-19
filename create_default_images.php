<?php
// ໄຟລ໌ນີ້ໃຊ້ສຳລັບສ້າງ folder ແລະຮູບພາບ default

// ສ້າງ folder
$folders = [
    'assets/images/products',
    'assets/images'
];

foreach($folders as $folder) {
    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
        echo "Created folder: $folder<br>";
    }
}

// ສ້າງຮູບ default ດ້ວຍ GD library
$width = 300;
$height = 250;

// ສ້າງຮູບພາບ
$image = imagecreate($width, $height);

// ສີພື້ນຫຼັງ
$bg_color = imagecolorallocate($image, 224, 224, 224);

// ສີຂໍ້ຄວາມ
$text_color = imagecolorallocate($image, 153, 153, 153);

// ໃສ່ຂໍ້ຄວາມ
$text = "No Image";
$font_size = 5;
$text_width = imagefontwidth($font_size) * strlen($text);
$text_height = imagefontheight($font_size);
$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

imagestring($image, $font_size, $x, $y, $text, $text_color);

// ບັນທຶກຮູບພາບ
imagejpeg($image, 'assets/images/products/default.jpg', 80);
imagedestroy($image);

echo "Created default image: assets/images/products/default.jpg<br>";
echo "Setup completed!";
?>