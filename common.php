<?php
require_once 'Medoo.php';
require_once 'config.php';
use Medoo\Medoo;
function okResult($data) {
	return json_encode(array(
		'code' => 0,
		'result' => $data
	));
}
function errResult($code, $msg) {
	return json_encode(array(
		'code' => $code,
		'msg' => $msg,
	));
}
function requireLogin() {
	if(!isset($_SESSION['userid']) or $_SESSION['userid'] == '') {
		echo errResult(103, 'login required');
		exit;
	}
}
$request = file_get_contents('php://input');
if ($request === '') {
	errResult(101, 'request content must be a json');
	exit;
}
try {
	$_REQUEST = json_decode($request, true);
} catch {
	errResult(101, 'request content must be a json');
	exit;
}

if (!isset($_SERVER['PATH_INFO']) or $_SERVER['PATH_INFO'] === '') {
	$_SERVER['PATH_INFO'] = '/';
}
define('RPATH', $_SERVER['PATH_INFO']);
session_start();
$_SERVER['db'] = new Medoo(array(
	'database_type' => 'mysql',
	'database_name' => MYSQL_DATABASE,
	'server' => MYSQL_SERVER,
	'port' => MYSQL_PORT,
	'username' => MYSQL_USER,
	'password' => MYSQL_PASS,
	'charset' => 'utf8'
));