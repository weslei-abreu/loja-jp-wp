<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Content-Type: text/plain; charset=UTF-8');
http_response_code(200);
echo "OK\n";
