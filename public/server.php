<?php
session_start();
  if(isset($_GET['action']) && $_GET['action'] == 'logout') {
	unset($_SESSION['username']);
	header('location:/login');
}
?>