<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Backend;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Tests\Mocks\DatabaseMock;
use MAKS\Velox\Backend\Database;

class DatabaseTest extends TestCase
{
    private Database $database;


    public function setUp(): void
    {
        parent::setUp();

        $this->database = $this->getDatabaseInstance();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->database);
    }


    public function testDatabaseInstanceMethod(): void
    {
        // make Database::instance() returns the save instance of $this->database
        // because by default it will return an instance using credentials from the config.
        // the Database class identifies a connection via the arguments it was constructed with,
        // thats why we need to do this, see Database::instance() and Database::connect() for more details
        $this->setTestObjectProperty($this->database, 'connections', [
            md5(serialize([])) => $this->database
        ]);

        $this->assertInstanceOf(Database::class, Database::instance());
    }

    public function testDatabasePerformMethod(): void
    {
        $this->createDatabaseTestData();

        $statement = $this->database->perform('SELECT :column FROM `test`', [':column' => 'text']);

        $this->assertInstanceOf(\PDOStatement::class, $statement);

        $this->dropDatabaseTestData();
    }

    public function testDatabasePerformMethodThrowsAnExceptionWithWrongQueries(): void
    {
        $this->expectException(\PDOException::class);

        $this->database->perform('SELECT * FROM `unknown`');
    }

    public function testDatabaseTransactionalMethod(): void
    {
        $array = $this->database->transactional(function () {
            /** @var Database $this */
            return $this->perform('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        });

        $this->assertIsArray($array);
    }

    public function testDatabaseTransactionalThrowsAnExceptionIfItFails(): void
    {
        $this->createDatabaseTestData();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Could not complete the transaction)/');

        $this->database->transactional(function () {
            /** @var Database $this */
            // using a wrong query to make it fail
            return $this->perform('UPDATE `test` -- SET `text` = "Test text III" WHERE `id` = 3');
        });

        $this->dropDatabaseTestData();
    }

    public function testDatabaseStatementClassDebugDumpParamsMethod(): void
    {
        $this->expectOutputString('');

        $dump = $this->database->perform('SHOW TABLES;')->debugDumpParams();

        $this->assertIsString($dump);
        $this->assertStringContainsString('SHOW TABLES;', $dump);
    }


    private function getDatabaseInstance(): Database
    {
        return DatabaseMock::instance();
    }

    private function dropDatabaseTestData(): void
    {
        $this->database->exec('DROP TABLE IF EXISTS `test`;');
    }

    private function createDatabaseTestData(): void
    {
        $this->dropDatabaseTestData();

        $this->database->exec('CREATE TABLE `test` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `text` VARCHAR(255));');
        $this->database->exec('INSERT INTO `test` (`text`) VALUES ("Test text 1"), ("Test text 2"), ("Test text 3");');
    }
}
