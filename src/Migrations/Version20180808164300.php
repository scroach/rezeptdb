<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180808164300 extends AbstractMigration {

	public function getDescription() {
		return 'added indizes removed unused column';
	}

	public function up(Schema $schema): void {
		$this->addSql(<<<EOT
ALTER TABLE `ingredients`
	CHANGE COLUMN `group_id` `group_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `id`,
	DROP COLUMN `recipe_id`,
	ADD CONSTRAINT `ingredients_group_id` FOREIGN KEY (`group_id`) REFERENCES `ingredient_groups` (`id`) ON DELETE CASCADE;

DELETE FROM images WHERE recipe_id NOT IN (SELECT id FROM recipes);

ALTER TABLE `images`
	ADD CONSTRAINT `images_recipe_id` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

EOT
		);
	}

	public function down(Schema $schema) {
		// we don't do downs, life is too short to be down
	}

}
