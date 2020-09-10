<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200910234000 extends AbstractMigration {

	public function getDescription() {
		return 'added user id to tags';
	}

	public function up(Schema $schema): void {
		$this->addSql(<<<EOT
ALTER TABLE `tags`
	ADD COLUMN `user_id` INT NULL DEFAULT NULL AFTER `label`,
	ADD CONSTRAINT `tags_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
	DROP INDEX `label`,
	ADD UNIQUE INDEX `label` (`label`, `user_id`) USING BTREE;

EOT
		);
	}

	public function down(Schema $schema) {
		// we don't do downs, life is too short to be down
	}

}
