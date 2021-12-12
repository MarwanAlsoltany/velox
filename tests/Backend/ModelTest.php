<?php

declare(strict_types=1);

namespace MAKS\Velox\Tests\Backend;

use MAKS\Velox\Tests\TestCase;
use MAKS\Velox\Tests\Mocks\DatabaseMock;
use MAKS\Velox\Tests\Mocks\ModelMock;
use MAKS\Velox\Backend\Database;
use MAKS\Velox\Backend\Model;

class ModelTest extends TestCase
{
    private Model $model;


    public function setUp(): void
    {
        parent::setUp();

        $this->model = $this->getModelInstance();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->model);
    }


    public function testModelGetDatabaseMethod(): void
    {
        $initial = $this->getTestObjectProperty($this->model, 'database');
        $this->setTestObjectProperty($this->model, 'database', null);

        $database = $this->model->getDatabase();

        $this->assertInstanceOf(Database::class, $database);

        $this->setTestObjectProperty($this->model, 'database', $initial);
    }

    public function testModelGetTableMethod(): void
    {
        $initial = $this->getTestObjectProperty($this->model, 'table');
        $this->setTestObjectProperty($this->model, 'table', '');

        $table = $this->model->getTable();

        $this->assertIsString($table);
        $this->assertEquals('model_mock_model_entries', $table);

        $this->setTestObjectProperty($this->model, 'table', $initial);
    }

    public function testModelGetColumnsMethod(): void
    {
        $initial = $this->getTestObjectProperty($this->model, 'columns');
        $this->setTestObjectProperty($this->model, 'columns', []);

        $columns = $this->model->getColumns();

        $this->assertIsArray($columns);
        $this->assertContains('id', $columns);

        $this->setTestObjectProperty($this->model, 'columns', $initial);
    }

    public function testModelGetPrimaryKeyMethod(): void
    {
        $initial = $this->getTestObjectProperty($this->model, 'primaryKey');
        $this->setTestObjectProperty($this->model, 'primaryKey', '');

        $primaryKey = $this->model->getPrimaryKey();

        $this->assertIsString($primaryKey);
        $this->assertEquals('id', $primaryKey);

        $this->setTestObjectProperty($this->model, 'primaryKey', $initial);
    }

    public function testModelCreateMethod(): void
    {
        $model = $this->model->create([
            'name' => 'John Doe',
            'age' => 27
        ]);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals(null, $model->id);
        $this->assertEquals('John Doe', $model->name);
        $this->assertEquals(27, $model->age);
    }

    public function testModelSaveMethod(): void
    {
        $this->createModelTestData(1);

        $model = $this->model->first();

        $this->assertEquals(1, $model->id);
        $this->assertEquals('John Doe', $model->name);
        $this->assertEquals(27, $model->age);
    }

    public function testModelUpdateMethod(): void
    {
        $this->createModelTestData(1);

        $this->model->last()->update(['age' => 30]);

        $model = $this->model->last();

        $this->assertEquals(1, $model->id);
        $this->assertEquals('John Doe', $model->name);
        $this->assertEquals(30, $model->age);
    }

    public function testModelDeleteMethod(): void
    {
        $this->createModelTestData(1);

        $this->model->last()->delete();

        $this->assertNull($this->model->one());
    }

    public function testModelDestroyMethod(): void
    {
        $this->createModelTestData(1);

        $this->model->last()->destroy(1);

        $this->assertNull($this->model->one());
    }

    public function testModelHydrateMethod(): void
    {
        $data = $this->getModelTestData();

        $models = $this->model->hydrate($data);

        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        $this->assertInstanceOf(Model::class, $models[0]);
    }

    public function testModelFetchMethod(): void
    {
        $this->createModelTestData(2);

        $models = $this->model->fetch('SELECT * FROM @table WHERE `name` LIKE :n', [':n' => '%Doe']);

        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        $this->assertInstanceOf(Model::class, $models[0]);

        $arrays = $this->model->fetch('SELECT * FROM @table WHERE `name` LIKE :n', [':n' => '%Doe'], true);

        $this->assertIsArray($arrays);
        $this->assertNotEmpty($arrays);
        $this->assertIsArray($arrays[0]);
    }

    public function testModelAllMethod(): void
    {
        $this->createModelTestData(1);
        $this->createModelTestData(2);

        $models = $this->model->all();

        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        $this->assertInstanceOf(Model::class, $models[0]);

        $models = $this->model->all(['name' => 'John Doe'], 'age DESC', 1, 1);

        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        $this->assertInstanceOf(Model::class, $models[0]);
        $this->assertEquals(27, $models[0]->age);
    }

    public function testModelOneMethod(): void
    {
        $this->createModelTestData(2);

        $model = $this->model->one();

        $this->assertIsObject($model);
        $this->assertInstanceOf(Model::class, $model);

        $model = $this->model->one(['name' => 'John Doe']);

        $this->assertIsObject($model);
        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals('John Doe', $model->name);
    }

    public function testModelFirstMethod(): void
    {
        $this->createModelTestData(2);

        $model = $this->model->first();

        $this->assertIsObject($model);
        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals('John Doe', $model->name);
    }

    public function testModelLastMethod(): void
    {
        $this->createModelTestData(2);

        $model = $this->model->last();

        $this->assertIsObject($model);
        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals('Jane Doe', $model->name);
    }

    public function testModelFindMethod(): void
    {
        $this->createModelTestData(2);

        $model = $this->model->find(1);

        $this->assertIsObject($model);
        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals('John Doe', $model->name);

        $models = $this->model->find(['id' => 2, 'age' => 25]);

        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        $this->assertIsObject($models[0]);
        $this->assertInstanceOf(Model::class, $models[0]);
        $this->assertEquals('Jane Doe', $models[0]->name);
    }

    public function testModelCountMethod(): void
    {
        $this->createModelTestData(2);

        $count = $this->model->count();

        $this->assertIsInt($count);
        $this->assertEquals(2, $count);

        $count = $this->model->count(['name' => 'John Doe']);

        $this->assertIsInt($count);
        $this->assertEquals(1, $count);
    }

    public function testModelWhereMethod(): void
    {
        $this->createModelTestData(2);
        $this->createModelTestData(2);

        $models = $this->model->where('age', '>=', 27);

        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        $this->assertInstanceOf(Model::class, $models[0]);
        $this->assertEquals(27, $models[0]->age);

        $models = $this->model->where('name', 'LIKE', '%Doe', [
            ['AND', 'age', '=', 25],
            [
                ['AND', 'age', '<', 27],
                ['OR', 'name', 'IN', ['John Doe', 'Jane Doe']]
            ]
        ], 'age', 1, 1);

        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
        $this->assertInstanceOf(Model::class, $models[0]);
        $this->assertEquals('Jane Doe', $models[0]->name);
    }

    public function testModelWhereMethodThrowsAnExceptionWhenSuppliedWithInvalidOperator(): void
    {
        $this->createModelTestData(2);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/(Got \'.+\' as an argument in query at index \(.+\), which is an invalid or unsupported SQL operator)/');

        $this->model->where('age', '!==', 27);
    }

    public function testModelWhereMethodThrowsAnExceptionWhenSuppliedWithInvalidAdditionalCondition(): void
    {
        $this->createModelTestData(2);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/(The passed condition \[.+\] in query at index \(.+\) is invalid)/');

        $this->model->where('name', 'LIKE', '%Doe', [[]], 'age', 1, 1);
    }

    public function testModelToArrayMethod(): void
    {
        $this->createModelTestData(1);

        $model = $this->model->find(1);
        $array = $model->toArray();

        $this->assertInstanceOf(Model::class, $model);
        $this->assertIsArray($array);
        $this->assertNotEmpty($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('age', $array);
    }

    public function testModelToJsonMethod(): void
    {
        $this->createModelTestData(1);

        $model = $this->model->find(1);
        $json  = $model->toJson();

        $this->assertInstanceOf(Model::class, $model);
        $this->assertIsArray(json_decode($json, true));
        $this->assertIsString($json);
        $this->assertNotEmpty($json);
        $this->assertStringContainsString('id', $json);
        $this->assertStringContainsString('name', $json);
        $this->assertStringContainsString('age', $json);
    }

    public function testModelWhenCastedToAString(): void
    {
        $this->createModelTestData(1);

        $model = $this->model->find(1);
        $model  = (string)$model;

        $this->assertIsString($model);
        $this->assertNotEmpty($model);
        $this->assertStringContainsString('id', $model);
        $this->assertStringContainsString('name', $model);
        $this->assertStringContainsString('age', $model);
    }

    public function testModelGetMethodThrowsAnExceptionIfTheAttributeIsUnknown(): void
    {
        $this->createModelTestData(1);

        $model = $this->model->find(1);

        $name = $model->get('name');

        $this->assertEquals('John Doe', $name);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Cannot find attribute with the name)/');

        $model->get('unknown');
    }

    public function testModelMagicMethods(): void
    {
        $this->createModelTestData(1);

        $model = $this->model->find(1);

        unset($model->name);

        $this->assertFalse(isset($model->name));

        $model->setName('John Not Doe');
        $model->setAge(72);
        $name = $model->getName();
        $age  = $model->getAge();

        $this->assertEquals('John Not Doe', $name);
        $this->assertEquals(72, $age);

        $serialized = serialize($model);

        $this->assertIsString($serialized);
        $this->assertStringContainsString('id', $serialized);
        $this->assertStringContainsString('name', $serialized);
        $this->assertStringContainsString('age', $serialized);

        $unserialized = unserialize($serialized);

        $this->assertIsObject($unserialized);
        $this->assertInstanceOf(Model::class, $unserialized);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/(Call to undefined method)/');

        $model->unknown();
    }

    public function testModelIdIsUnsetWhenCloningTheModel(): void
    {
        $this->createModelTestData(1);

        $model = $this->model->find(1);

        $newModel = clone $model;

        $this->assertNull($newModel->getId());
    }

    public function testModelWhenDumped(): void
    {
        $this->createModelTestData(1);

        $model = $this->model->find(1);

        ob_start();
        var_dump($model);
        $dump = ob_get_clean();

        $this->assertIsString($dump);
        $this->assertNotEmpty($dump);
        $this->assertStringContainsString('id', $dump);
        $this->assertStringContainsString('name', $dump);
        $this->assertStringContainsString('age', $dump);
    }

    public function testModelArrayAccessImplementation(): void
    {
        $this->createModelTestData(1);

        $model = $this->model->find(1);

        $this->assertTrue(isset($model['name']));
        $this->assertTrue(isset($model['age']));

        $model['name'] = 'John Not Doe';
        $model['age']  = 72;

        $this->assertEquals('John Not Doe', $model['name']);
        $this->assertEquals(72, $model['age']);

        unset($model['id']);

        $this->assertFalse(isset($model['id']));
    }

    public function testModelTraversableAndIteratorAggregateImplementation(): void
    {
        $this->createModelTestData(1);

        $model = $this->model->find(1);

        $this->assertInstanceOf(\Traversable::class, $model->getIterator());

        foreach ($model as $key => $value) {
            $this->assertIsString($key);
            $this->assertNotEmpty($value);
        }
    }

    public function testModelDBALMigrateMethodThrowsAnExceptionIfCalledInAnAbstractContext(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessageMatches('/(Cannot migrate an abstract class)/');

        Model\DBAL::migrate();
    }

    public function testModelDBALFetchMethodThrowsAnExceptionIfCalledInAnAbstractContext(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessageMatches('/(Cannot fetch for an abstract class)/');

        Model\DBAL::fetch('SELECT * FROM @table WHERE `name` LIKE :name', [':name' => '%Doe']);
    }


    private function getModelInstance(): Model
    {
        $database = DatabaseMock::instance();

        $model = new ModelMock();

        // set model database to testing database
        $this->setTestObjectProperty($model, 'database', $database);

        return $model;
    }

    private function createModelTestData(int $count = 1): void
    {
        $people = $this->getModelTestData();

        for ($i = 0; $i < $count; $i++) {
            $this->model->create($people[$i])->save();
        }
    }

    private function getModelTestData(): array
    {
        return [
            [
                'name' => 'John Doe',
                'age' => 27
            ],
            [
                'name' => 'Jane Doe',
                'age' => 25
            ]
        ];
    }
}
