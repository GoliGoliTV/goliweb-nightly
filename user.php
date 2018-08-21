<?php
require_once 'common.php';

header('Content-Type: application/json; charset=utf-8');
switch (RPATH) {
	case '/':
		requireLogin();
		$userInfo = $_SERVER['db']->get('user', '*', ['id' => $_SESSION['userid']]);
		echo okResult([
			'id' => $userInfo['id'],
			'name' => $userInfo['name'],
			'email' => $userInfo['email'],
			'username' => $userInfo['username'],
			'group' => $userInfo['group'],
			'blanklisted' => $userInfo['blanklisted']?true:false,
			'status' => $userInfo['status'],
			'avatar' => $userInfo['avatar'],
			'reg_time' => $userInfo['reg_time'],
			'last_login' => $userInfo['lastlogin_time']
		]);
		break;
	case '/login':
		$_SESSION['login_token'] = uniqid();
		echo okResult(['token' => $_SESSION['login_token']]);
		break;
	case '/auth':
		if (!isset($_SESSION['login_token']) or $_SESSION['login_token'] === '') {
			echo errResult(102, 'session experied');
		} else {
			if (isset($_REQUEST['user']) and isset($_REQUEST['password'])) {
				$user = $_SERVER['db']->get('user', ['id', 'password', 'login_hash'], ['username' => $_REQUEST['user']]);
				if (!$user) {
					echo errResult(104, 'login failed');
				} elseif (sha1($user['password'].$_SESSION['login_token']) !== $_REQUEST['password']) {
					echo errResult(104, 'login failed');
				} else {
					$_SESSION['userid'] = $user['id'];
					if ($_REQUEST['remember']) {
						if ($user['login_hash'] === '') {
							$user['login_hash'] = sha1('loginHash'.$user['password'].uniqid().$_REQUEST['user']);
							$_SERVER['db']->update('user', ['login_hash' => $user['login_hash']], ['id' => $user['id']]);
						}
						setcookie('id', $user['id'], time()+2592000);
						setcookie('login', $user['login_hash'], time()+2592000);
					}
					echo okResult(['userid' => $user['id']])
				}
			} else echo errResult(103, 'bad request');
		}
		unset($_SESSION['login_token']);
		break;
	case '/autologin':
		if ($_SESSION['userid']) {
			echo okResult(['userid' => $_SESSION['userid']]);
			break;
		}
		if (isset($_COOKIE['id']) and isset($_COOKIE['login'])) {
			$user = $_SERVER['db']->get('user', 'id', ['AND' => ['id' => $_COOKIE['id'], 'login_hash' => $_COOKIE['login']]]);
			if (!user) {
				echo errResult(105, 'auto login failed');
				break;
			}
			$_SESSION['userid'] = $user['id'];
			echo okResult(['userid' => $user['id']]);
		}
		break;
	case '/regist':
		if ($_SESSION['userid']) {
			echo errResult(106, 'user must logout first');
			break;
		}
		if (isset($_REQUEST['username']) and isset($_REQUEST['password']) and isset($_REQUEST['email'])) {
			if (!preg_match('^[0-9a-zA-Z]{3,20}$', $_REQUEST['username'])) {
				echo errResult(107, 'invalid username');
				break;
			} elseif (!preg_match('^[0-9a-f]{40}$',$_REQUEST['password'])) {
				echo errResult(108, 'password must be 40 lowercase hex string');
				break;
			} elseif (!preg_match('^\w{1,20}@[0-9a-z\-_\.]{1,32}\.\w{1,16}$', $_REQUEST['email'])) {
				echo errResult(109, 'invalid email address');
				break;
			} elseif ($_SERVER['db']->has('user', ['username' => $_REQUEST['username']])) {
				echo errResult(110, 'username already exists');
				break;
			} elseif ($_SERVER['db']->has('user', ['email' => $_REQUEST['email']])) {
				echo errResult(111, 'user email already exists');
				break;
			} else {
				$_SERVER['db']->insert('user', [
					'email' => $_REQUEST['email'],
					'username' => $_REQUEST['username'],
					'password' => $_REQUEST['password'],
					'group' => 1,
					]);
				echo okResult(['registed' => true, 'need_confirm' => false]);
			}
		} else {
			$_SESSION['regist_token'] = uniqid();
			echo okResult(['token' => $_SESSION['regist_token']]);
		}
		break;
	case '/query':
		if (isset($_REQUEST['ids']) and is_array($_REQUEST['ids'])) {
			$ids = array();
			foreach ($_REQUEST['ids'] as $v) {
				if($v === '') {
					echo errResult(113, 'users id can not be empty');
					exit;
				} elseif (($v = intval($v)) < 1) {
					echo errResult(114, 'users id can not less than 0');
					exit;
				}
				$ids[]=$v;
			}
			if (count($ids) < 1) {
				echo errResult(115, 'no user queried');
				break;
			}
			sort($ids);
			$users = $_SERVER['db']->select('user', ['id', 'name', 'group', 'blanklisted', 'avatar'], ['id' => $ids]);
			$rets = array();
			foreach($users as $v) {
				$rets[]=[
					'id' => intval($v['id']),
					'name' => $v['name'],
					'group' => intval($v['group']),
					'blacklisted' => $v['blacklisted']?true:false,
					'avatar' => $v['avatar']
				];
			}
			echo okResult($rets);
			break;
		} else {
			echo errResult(116, 'ids must be an array');
			break;
		}
}