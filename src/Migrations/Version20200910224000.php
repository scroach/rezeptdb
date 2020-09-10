<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200910224000 extends AbstractMigration {

	public function getDescription() {
		return 'added user id to recipes';
	}

	public function up(Schema $schema): void {
		$this->addSql(<<<EOT
ALTER TABLE `recipes`
	ADD COLUMN `user_id` INT NULL DEFAULT NULL AFTER `deleted_at`,
	ADD CONSTRAINT `recipes_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
EOT
		);
	}

	public function down(Schema $schema) {
		// we don't do downs, life is too short to be down
	}

}
