<?php
Template::setTitle(_("User panel"));
Database::setNextLimit(0, 3);
$lastDocuments = Database::query("SELECT `document_id`, `name`, `user_id` FROM `departments_documents` WHERE `department_id` = ? AND `is_archived` = 0 AND `is_private` = 0 ORDER BY `document_id` DESC",
	[$profile->get('department_id')]);
Database::setNextLimit(0, 3);
$lastDecrees = Database::query("SELECT `decree_id`, `name` FROM `decrees` ORDER BY `decree_id` DESC");
Database::setNextLimit(0, 3);
$lastPrescriptions = Database::query("SELECT `prescription_id`, `name` FROM `prescriptions` ORDER BY `prescription_id` DESC");