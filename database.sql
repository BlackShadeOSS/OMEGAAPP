CREATE TABLE IF NOT EXISTS `posts` (
	`post_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`author` int NOT NULL,
	`author-firm` int,
	`content` varchar(512) NOT NULL,
	`likes` int NOT NULL,
	`shares` int NOT NULL,
	`comments_number` int NOT NULL,
	`files_id` int,
	`post_as_comment` int,
	PRIMARY KEY (`post_id`)
);
CREATE TABLE IF NOT EXISTS `user_account` (
	`user_ac_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`username` varchar(50) NOT NULL,
	`password` varchar(64) NOT NULL,
	`email` varchar(50) NOT NULL,
	`followers` int NOT NULL,
	`is_cn_admin` boolean NOT NULL DEFAULT false,
	`is_verified` boolean NOT NULL DEFAULT false,
	PRIMARY KEY (`user_ac_id`)
);
CREATE TABLE IF NOT EXISTS `firm_account` (
	`firm_ac_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`firm_name` varchar(50) NOT NULL,
	`followers` int NOT NULL,
	`manager` int NOT NULL,
	PRIMARY KEY (`firm_ac_id`)
);
CREATE TABLE IF NOT EXISTS `fa_manager` (
	`manager_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`firm_id` int NOT NULL,
	`user_id` int NOT NULL,
	PRIMARY KEY (`manager_id`)
);
CREATE TABLE IF NOT EXISTS `community_note` (
	`id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`cn_post_id` int NOT NULL,
	`content` varchar(512) NOT NULL,
	PRIMARY KEY (`id`)
);
CREATE TABLE IF NOT EXISTS `follow` (
	`follow_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`user_ac_id` int,
	`user_follower_id` int,
	`firm_ac_id` int,
	`firm_follower_id` int,
	PRIMARY KEY (`follow_id`)
);
CREATE TABLE IF NOT EXISTS `like` (
	`follow_id` int AUTO_INCREMENT NOT NULL UNIQUE,
	`post_id` int NOT NULL,
	`user_follower_id` int,
	`firm_follower_id` int,
	PRIMARY KEY (`follow_id`)
);
ALTER TABLE `posts`
ADD CONSTRAINT `posts_fk1` FOREIGN KEY (`author`) REFERENCES `user_account`(`user_ac_id`);
ALTER TABLE `posts`
ADD CONSTRAINT `posts_fk2` FOREIGN KEY (`author-firm`) REFERENCES `firm_account`(`firm_ac_id`);
ALTER TABLE `posts`
ADD CONSTRAINT `posts_fk8` FOREIGN KEY (`post_as_comment`) REFERENCES `posts`(`post_id`);
ALTER TABLE `fa_manager`
ADD CONSTRAINT `fa_manager_fk1` FOREIGN KEY (`firm_id`) REFERENCES `firm_account`(`firm_ac_id`);
ALTER TABLE `fa_manager`
ADD CONSTRAINT `fa_manager_fk2` FOREIGN KEY (`user_id`) REFERENCES `user_account`(`user_ac_id`);
ALTER TABLE `community_note`
ADD CONSTRAINT `community_note_fk1` FOREIGN KEY (`cn_post_id`) REFERENCES `posts`(`post_id`);
ALTER TABLE `follow`
ADD CONSTRAINT `follow_fk1` FOREIGN KEY (`user_ac_id`) REFERENCES `user_account`(`user_ac_id`);
ALTER TABLE `follow`
ADD CONSTRAINT `follow_fk2` FOREIGN KEY (`user_follower_id`) REFERENCES `user_account`(`user_ac_id`);
ALTER TABLE `follow`
ADD CONSTRAINT `follow_fk3` FOREIGN KEY (`firm_ac_id`) REFERENCES `firm_account`(`firm_ac_id`);
ALTER TABLE `follow`
ADD CONSTRAINT `follow_fk4` FOREIGN KEY (`firm_follower_id`) REFERENCES `firm_account`(`firm_ac_id`);
ALTER TABLE `like`
ADD CONSTRAINT `like_fk1` FOREIGN KEY (`post_id`) REFERENCES `posts`(`post_id`);
ALTER TABLE `like`
ADD CONSTRAINT `like_fk2` FOREIGN KEY (`user_follower_id`) REFERENCES `user_account`(`user_ac_id`);
ALTER TABLE `like`
ADD CONSTRAINT `like_fk3` FOREIGN KEY (`firm_follower_id`) REFERENCES `firm_account`(`firm_ac_id`);