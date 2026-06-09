<?php
$contents = file_get_contents('admin/organizations.php');

$start = strpos($contents, '        if ($org->create()) {');
if ($start !== false) {
    $end = strpos($contents, '    header("Location: organizations.php");', $start);
    if ($end !== false) {
        $contents = substr($contents, 0, $start) . substr($contents, $end);
        file_put_contents('admin/organizations.php', $contents);
    }
}
