<?php
$whileList = [
    'http://127.0.0.1:5500',
    'http://localhost:5500'
];
if (!empty($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $whileList)) {
    header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
}
header('Access-Control-Allow-Methods: *');  // GET, POST, PUT, PATCH, DELETE
header('Access-Control-Allow-Headers: *');  // Content-Type, Authorization