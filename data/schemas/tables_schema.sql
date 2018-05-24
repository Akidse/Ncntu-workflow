SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `archive_requests` (
  `request_id` int(11) NOT NULL,
  `type` tinyint(1) DEFAULT NULL,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `decrees` (
  `decree_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `name` varchar(512) DEFAULT NULL,
  `description` varchar(4096) DEFAULT NULL,
  `file_id` int(11) NOT NULL,
  `additions_id` varchar(128) DEFAULT NULL,
  `public` tinyint(1) DEFAULT '0',
  `time_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `subdepartment_id` int(11) DEFAULT '0',
  `public` tinyint(1) NOT NULL,
  `main` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `departments_backup` (
  `department_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `subdepartment_id` int(11) DEFAULT '0',
  `public` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `departments_documents` (
  `document_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `file_id` int(11) NOT NULL,
  `addition_id` varchar(64) DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT '0',
  `is_private` tinyint(1) NOT NULL DEFAULT '0',
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `departments_documents_versions` (
  `version_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `file_id` int(11) NOT NULL,
  `addition_id` varchar(64) DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT NULL,
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_deprecated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `departments_permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `departments_permissions` (`permission_id`, `name`, `description`) VALUES
(1, 'allow_decree', 'Можливість створювати накази'),
(2, 'allow_access_maindep', 'Зв\'язок з головним підрозділом');

CREATE TABLE `departments_permitted` (
  `permit_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `documents_requests` (
  `request_id` int(11) NOT NULL,
  `type` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_done` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `documents_signs` (
  `sign_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `files` (
  `file_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `real_name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `addition_to` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `letters` (
  `letter_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(512) DEFAULT NULL,
  `message` varchar(2048) DEFAULT NULL,
  `files` varchar(64) DEFAULT NULL,
  `unread` tinyint(1) NOT NULL DEFAULT '1',
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `message_types` (
  `type_id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `prescriptions` (
  `prescription_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `name` varchar(512) DEFAULT NULL,
  `description` varchar(4096) DEFAULT NULL,
  `file_id` int(11) NOT NULL,
  `additions_id` varchar(128) DEFAULT NULL,
  `public` tinyint(1) DEFAULT '0',
  `time_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `roles` (
  `post_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(128) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(64) NOT NULL,
  `middle_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `role_id` int(11) NOT NULL,
  `avatar` varchar(64) NOT NULL,
  `department_id` int(11) NOT NULL,
  `groups` varchar(64) DEFAULT NULL,
  `decree_last_view` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `prescription_last_view` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `locale` varchar(8) NOT NULL DEFAULT 'uk_UA',
  `results_per_page` tinyint(3) UNSIGNED NOT NULL DEFAULT '10'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users_groups` (`group_id`, `name`) VALUES
(1, 'Адміністратор'),
(2, 'Секретар'),
(3, 'Архіваріус'),
(4, 'Директор'),
(5, 'Заступник директора');

CREATE TABLE `users_groups_permissions` (
  `permission_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_groups_permitted` (
  `permit_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users_resume` (
  `user_id` int(11) NOT NULL,
  `phone_number` varchar(32) DEFAULT NULL,
  `address` varchar(64) DEFAULT NULL,
  `city` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `archive_requests`
  ADD PRIMARY KEY (`request_id`);

ALTER TABLE `decrees`
  ADD PRIMARY KEY (`decree_id`);

ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

ALTER TABLE `departments_backup`
  ADD PRIMARY KEY (`department_id`);

ALTER TABLE `departments_documents`
  ADD PRIMARY KEY (`document_id`);


ALTER TABLE `departments_documents_versions`
  ADD PRIMARY KEY (`version_id`);

ALTER TABLE `departments_permitted`
  ADD PRIMARY KEY (`permit_id`);

ALTER TABLE `documents_requests`
  ADD PRIMARY KEY (`request_id`);

ALTER TABLE `documents_signs`
  ADD PRIMARY KEY (`sign_id`);

ALTER TABLE `files`
  ADD PRIMARY KEY (`file_id`);

ALTER TABLE `letters`
  ADD PRIMARY KEY (`letter_id`);

ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`prescription_id`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`post_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

ALTER TABLE `users_groups_permissions`
  ADD PRIMARY KEY (`permission_id`);

ALTER TABLE `users_groups_permitted`
  ADD PRIMARY KEY (`permit_id`);

ALTER TABLE `users_resume`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);


ALTER TABLE `archive_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `decrees`
  MODIFY `decree_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `departments_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `departments_documents_versions`
  MODIFY `version_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `departments_permitted`
  MODIFY `permit_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `documents_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `documents_signs`
  MODIFY `sign_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `letters`
  MODIFY `letter_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `prescriptions`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `roles`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_groups_permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_groups_permitted`
  MODIFY `permit_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_resume`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;