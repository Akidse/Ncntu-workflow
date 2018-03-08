<?php
$scriptManager->add("module/departmentSelect");
$scriptManager->add("module/dropzone");

if(isset($_FILES) && $_FILES != null)print_r($_FILES);
if(isset($_POST) && $_POST != null)print_r($_POST);
$documentTypes = Database::query("SELECT * FROM `message_types`", [], Database::FETCH_ALL);

$firstLevelDep = Database::query("SELECT * FROM `departments` WHERE `subdepartment_id` IS NULL");