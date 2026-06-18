<?php
require_once('includes/config.php');

if (isset($pdo)) {
    echo "PDO connection successful!";
} else {
    echo "PDO connection failed!";
}
?>