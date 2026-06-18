<?php
// Run this file once to create necessary folders
$folders = ['assets/uploads', 'assets/images', 'assets/css'];
foreach($folders as $folder) {
    if(!is_dir($folder)) {
        mkdir($folder, 0777, true);
        echo "Created folder: $folder<br>";
    }
}
echo "Setup complete!";
?>
