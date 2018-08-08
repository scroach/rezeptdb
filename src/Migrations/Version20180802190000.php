<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180802190000 extends AbstractMigration {

	public function getDescription() {
		return 'added ingredient groups';
	}

	public function up(Schema $schema): void {
		$this->addSql(<<<EOT
CREATE TABLE `ingredient_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `recipe_id` int(11) DEFAULT NULL,
  `label` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recipe_id` (`recipe_id`),
  CONSTRAINT `ingredient_groups_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ingredient_groups (recipe_id, label) SELECT id, "" FROM recipes;

ALTER TABLE `ingredients`
	ADD COLUMN `group_id` INT(11) NULL AFTER `recipe_id`;

UPDATE ingredients i SET i.group_id = (SELECT g.id FROM ingredient_groups g WHERE g.recipe_id = i.recipe_id);
EOT
		);
	}

	public function down(Schema $schema) {
		// we don't do downs, life is too short to be down
	}

}
