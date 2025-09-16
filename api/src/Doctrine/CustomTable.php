<?php

namespace App\Doctrine;

use Doctrine\DBAL\Schema\Table as BaseTable;

class CustomTable extends BaseTable
{
    public function addIndex(array $columns, ?string $name = null, array $flags = [], array $options = [])
    {
        if (null === $name) {
            $name = sprintf('idx_%s_%s', strtolower($this->getName()), strtolower(implode('_', $columns)));
        }

        return parent::addIndex($columns, $name, $flags, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function addUniqueIndex(array $columns, $name = null, array $options = [])
    {
        if (null === $name) {
            $name = sprintf('uniq_%s_%s', strtolower($this->getName()), strtolower(implode('_', $columns)));
        }

        return parent::addUniqueIndex($columns, $name);
    }

    public function addForeignKeyConstraint(
        $foreignTable,
        array $localColumns,
        array $foreignColumns,
        array $options = [],
        $name = null
    ) {
        if (null === $name) {
            $refTable = is_string($foreignTable) ? $foreignTable : $foreignTable->getName();
            $name = sprintf('fk_%s_%s_%s', strtolower($this->getName()), strtolower($refTable), implode('_', $localColumns));
        }

        return parent::addForeignKeyConstraint($foreignTable, $localColumns, $foreignColumns, $options, $name);
    }

    /**
     * Generates an identifier from a list of column names obeying a certain string length.
     *
     * This is especially important for Oracle, since it does not allow identifiers larger than 30 chars,
     * however building idents automatically for foreign keys, composite keys or such can easily create
     * very long names.
     *
     * @param string[] $columnNames
     * @param string   $prefix
     * @param int      $maxSize
     *
     * @return string
     */
    protected function _generateIdentifierName($columnNames, $prefix = '', $maxSize = 30)
    {
        return sprintf('%s_%s', strtolower($prefix), strtolower(implode('_', $columnNames)));
    }
}
