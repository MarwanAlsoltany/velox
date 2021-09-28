<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Model;

use MAKS\Velox\Backend\Model;

class Person extends Model
{
    public static function schema(): string
    {
        return vsprintf('
            CREATE TABLE IF NOT EXISTS `%s` (
                `%s` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `age` INT,
                `username` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255),
                `address` VARCHAR(255),
                `company` VARCHAR(255)
            );
        ', [
            static::getTable(),
            static::getPrimaryKey(),
        ]);
    }

    public static function getTable(): string
    {
        return 'persons';
        // or overwrite `self::$table` property instead.
    }

    public static function getColumns(): array
    {
        return [
            'id',
            'name',
            'age',
            'username',
            'email',
            'address',
            'company',
        ];
        // or overwrite `self::$columns` property instead.
    }

    public static function getPrimaryKey(): string
    {
        return 'id';
        // or overwrite `self::$primaryKey` property instead.
    }

    protected function bootstrap(): void
    {
        // add your own bootstrap logic here
    }
}
