<?php
$directories = [
    'uploads/certificates',
    'uploads/qrcodes',
    'uploads/documents',
    'uploads/signatures'
];

foreach ($directories as $dir) {
    if (!file_exists(__DIR__ . '/' . $dir)) {
        mkdir(__DIR__ . '/' . $dir, 0777, true);
    }
}
echo "Directories setup complete.\n";
