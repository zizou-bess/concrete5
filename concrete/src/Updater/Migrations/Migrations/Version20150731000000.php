<?php

namespace Concrete\Core\Updater\Migrations\Migrations;

use Concrete\Core\Updater\Migrations\AbstractMigration;
use Concrete\Core\Updater\Migrations\DirectSchemaUpgraderInterface;
use Concrete\Core\Updater\Migrations\ManagedSchemaUpgraderInterface;
use Concrete\Core\Updater\Migrations\RepeatableMigrationInterface;
use Doctrine\DBAL\Schema\Schema;

class Version20150731000000 extends AbstractMigration implements RepeatableMigrationInterface, ManagedSchemaUpgraderInterface, DirectSchemaUpgraderInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \Doctrine\DBAL\Migrations\AbstractMigration::getDescription()
     */
    public function getDescription()
    {
        return '5.7.5.2';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Updater\Migrations\ManagedSchemaUpgraderInterface::upgradeSchema()
     */
    public function upgradeSchema(Schema $schema)
    {
        try {
            $table = $schema->getTable('SystemDatabaseQueryLog');
            $table->addColumn('ID', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $table->setPrimaryKey(['ID']);
        } catch (\Exception $e) {
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Updater\Migrations\DirectSchemaUpgraderInterface::upgradeDatabase()
     */
    public function upgradeDatabase()
    {
        $db = \Database::connection();
        $db->executeQuery('DELETE FROM FileSetFiles WHERE fID NOT IN (SELECT fID FROM Files)');
        $db->executeQuery('DELETE FROM FileSearchIndexAttributes WHERE fID NOT IN (SELECT fID FROM Files)');
        $db->executeQuery('DELETE FROM DownloadStatistics WHERE fID NOT IN (SELECT fID FROM Files)');
        $db->executeQuery('DELETE FROM FilePermissionAssignments WHERE fID NOT IN (SELECT fID FROM Files)');

        $this->refreshBlockType('page_list');
    }
}
