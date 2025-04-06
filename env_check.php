<?php
header('Content-Type: text/plain');
echo "REACHED SERVER\n";
echo "PORT: ".getenv('PORT')."\n";
echo "DB_HOST: ".getenv('DB_HOST')."\n";