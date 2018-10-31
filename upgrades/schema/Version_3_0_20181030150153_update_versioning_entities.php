<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version_3_0_20181030150153_update_versioning_entities extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $versionEntitiesNameFromTo = [
            'Pim\\Bundle\\CatalogBundle\\Entity\\Locale' => 'Akeneo\\Channel\\Component\\Model\\Locale',
            'Pim\\Component\\Catalog\\Model\\Product' => 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product',
            'Pim\\Bundle\\CatalogBundle\\Entity\\Attribute' => 'Akeneo\\Pim\\Structure\\Component\\Model\\Attribute',
            'Pim\\Bundle\\CatalogBundle\\Entity\\Family' => 'Akeneo\\Pim\\Structure\\Component\\Model\\Family',
            'Pim\\Bundle\\CatalogBundle\\Entity\\Category' => 'Akeneo\\Pim\\Enrichment\\Component\\Category\\Model\\Category',
            'Pim\\Bundle\\CatalogBundle\\Entity\\Group' => 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Group',
            'Pim\\Bundle\\CatalogBundle\\Entity\\AttributeGroup' => 'Akeneo\\Pim\\Structure\\Component\\Model\\AttributeGroup',
            'Pim\\Bundle\\CatalogBundle\\Entity\\Channel' => 'Akeneo\\Channel\\Component\\Model\\Channel',
            'Pim\\Component\\Catalog\\Model\\ProductModel' => 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductModel',
            'Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType' => 'Akeneo\\Pim\\Structure\\Component\\Model\\AssociationType',
            'Akeneo\\Component\\Batch\\Model\\JobInstance' => 'Akeneo\\Tool\\Component\\Batch\\Model\\JobInstance',
        ];

        $updateSql = 'UPDATE pim_versioning_version SET resource_name = :after WHERE resource_name = :before';
        $preparedStatement = $this->connection->prepare($updateSql);

        foreach ($versionEntitiesNameFromTo as $before => $after) {
            $preparedStatement->execute(['before' => $before, 'after' => $after]);
        }

        /**
         * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
         * sql query has been made to the db during the migration process.
         */
        $this->addSql('SELECT 1');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
