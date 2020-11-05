<?php

declare(strict_types=1);

/*
 * @copyright   2020 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        https://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\Migrations;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\SkipMigration;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

final class Version20201102133546 extends AbstractMauticMigration
{
    private $tableName;
    private $indexName;

    public function preUp(Schema $schema): void
    {
        $this->tableName = $this->getTableName();
        $this->indexName = $this->generatePropertyName($this->tableName, 'idx', ['email_id']);

        $sql = <<<SQL
            SHOW INDEX FROM $this->tableName WHERE Key_name = '$this->indexName';
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $found = (bool) $stmt->fetch(FetchMode::ASSOCIATIVE);
        $stmt->closeCursor();

        if (!$found) {
            throw new SkipMigration('Schema includes this migration');
        }
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE '.$this->tableName.' DROP INDEX '.$this->indexName.';');
    }

    private function getTableName(): string
    {
        return $this->prefix.'email_assets_xref';
    }
}
