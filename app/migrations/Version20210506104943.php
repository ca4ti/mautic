<?php

declare(strict_types=1);

/*
 * @copyright   2021 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        https://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\PreUpAssertionMigration;

final class Version20210506104943 extends PreUpAssertionMigration
{
    protected function preUpAssertions(): void
    {
        // Please add an assertion for every SQL you define in the `up()` method.
        // The order does matter!

        // E.g.:
        /*
        $this->skipAssertion(function (Schema $schema) {
            return $schema->hasTable("{$this->prefix}table_name");
        }, sprintf('Table %s already exists', "{$this->prefix}table_name"));

        $this->skipAssertion(function (Schema $schema) {
            return $schema->getTable("{$this->prefix}table_name")->hasIndex('index_name');
        }, sprintf('Index %s already exists', 'index_name'));
        */
    }

    public function up(Schema $schema): void
    {
        // Please modify to your needs
    }
}
