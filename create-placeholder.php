<?php
// Create a simple placeholder image
$im = imagecreatetruecolor(400, 300);
$bg = imagecolorallocate($im, 240, 240, 240);
$text_color = imagecolorallocate($im, 150, 150, 150);
imagefilledrectangle($im, 0, 0, 400, 300, $bg);
imagestring($im, 5, 150, 140, 'No Image', $text_color);
imagejpeg($im, 'uploads/placeholder.jpg', 90);
imagedestroy($im);
echo "✅ Placeholder image created successfully!<br>";
echo "📍 Location: uploads/placeholder.jpg<br>";
echo "📏 Size: 400x300 pixels<br>";
echo "<br><a href='index.php'>Go to Homepage</a>";
?>