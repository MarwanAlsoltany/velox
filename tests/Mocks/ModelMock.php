<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Mocks;

use MAKS\Velox\Backend\Model;

class ModelMock extends Model
{
    protected static ?string $table = 'test';

    protected static ?array $columns = ['id', 'name', 'age'];

    protected static ?string $primaryKey = 'id';

    public static function schema(): string
    {
        return vsprintf('
            CREATE TABLE IF NOT EXISTS `%s` (
                `%s` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `age` INT
            );
        ', [
            static::getTable(),
            static::getPrimaryKey(),
        ]);
    }
}
