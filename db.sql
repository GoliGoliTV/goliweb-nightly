DROP TABLE IF EXISTS `usergroup`;
DROP TABLE IF EXISTS `user`;
DROP TABLE IF EXISTS `category`;
DROP TABLE IF EXISTS `video`;
DROP TABLE IF EXISTS `danmaku`;
DROP TABLE IF EXISTS `comment`;
DROP TABLE IF EXISTS `message`;

CREATE TABLE `usergroup` (
	`id` INT AUTO_INCREMENT,
	`name` VARCHAR(20) NOT NULL,
	`description` TEXT NOT NULL,
	UNIQUE (`name`),
	PRIMARY KEY (`id`)
) CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB;

CREATE TABLE `user` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(30) NOT NULL DEFAULT '',
	`email` VARCHAR(32) NOT NULL,
	`username` VARCHAR(30) NOT NULL,
	`password` VARCHAR(64) NOT NULL,
	`login_hash` VARCHAR(64),
	`group` INT NOT NULL,
	`blanklisted` TINYINT(1) NOT NULL DEFAULT 0,
	`status` TINYINT(1) NOT NULL DEFAULT 1,
	`avatar` TEXT,
	`reg_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`lastlogin_time` TIMESTAMP,
	UNIQUE (`username`),
	UNIQUE (`email`),
	PRIMARY KEY (`id`),
	FOREIGN KEY (`group`) REFERENCES `usergroup`(`id`)
) CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB;

CREATE TABLE `category` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(20) NOT NULL,
	`description` TEXT NOT NULL,
	PRIMARY KEY (`id`)
) CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `video` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`title` TEXT NOT NULL,
	`upload_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`status` TINYINT(1) NOT NULL,
	`description` TEXT NOT NULL,
	`duration` TIME NOT NULL,
	`owner` INT NOT NULL,
	`play_times` INT NOT NULL DEFAULT 0,
	`category` INT NOT NULL,
	`tags` TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	FOREIGN KEY (`owner`) REFERENCES `user`(`id`),
	FOREIGN KEY (`category`) REFERENCES `category`(`id`)
) CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB;

CREATE TABLE `comment` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`video` INT NOT NULL,
	`owner` INT NOT NULL,
	`content` TEXT NOT NULL,
	`reply_to` INT DEFAULT NULL,
	`created_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modified_time` TIMESTAMP,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`video`) REFERENCES `video`(`id`),
	FOREIGN KEY (`owner`) REFERENCES `user`(`id`)
) CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB;

CREATE TABLE `message` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`from` INT NOT NULL,
	`to` INT NOT NULL,
	`title` TEXT NOT NULL,
	`content` TEXT,
	`sent_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`read_time` TIMESTAMP,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`from`) REFERENCES `user`(`id`),
	FOREIGN KEY (`to`) REFERENCES `user`(`id`)
) CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB;