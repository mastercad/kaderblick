<?php

namespace App\Doctrine;

use Doctrine\DBAL\Schema\Schema as BaseSchema;

class CustomSchema extends BaseSchema
{
    public function createTable($name)
    {
        $table = new CustomTable($name);
        $this->_addTable($table);

        foreach ($this->_schemaConfig->getDefaultTableOptions() as $option => $value) {
            $table->addOption($option, $value);
        }

        return $table;
    }
}
