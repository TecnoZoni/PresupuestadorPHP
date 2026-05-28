<?php

$scheme = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'];
$base   = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base   = rtrim($base, '/') . '/';

define('APP_URL', $scheme . '://' . $host . $base);

const APP_NAME = "PRESUPUESTADOR";
const APP_SESSION_NAME = "PRESUPUESTADOR";

date_default_timezone_set("America/Argentina/Buenos_Aires");
