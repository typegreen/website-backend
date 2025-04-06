<?php
header('Content-Type: text/plain');
echo "PHP is working!";
echo "\nServer IP: " . $_SERVER['SERVER_ADDR'];
echo "\nRequest Time: " . date('Y-m-d H:i:s');