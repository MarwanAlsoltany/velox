<?php

/**
 * @author Marwan Al-Soltany <MarwanAlsoltany@gmail.com>
 * @copyright Marwan Al-Soltany 2021
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Controller;

use MAKS\Velox\Backend\Controller;
use App\Model\Person;

class PersonsController extends Controller
{
    protected function associateModel(): ?string
    {
        return Person::class;
    }

    protected function registerRoutes(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function indexAction(): string
    {
        $persons = $this->model->all();

        return $this->view->render('persons/index', [
            'title' => 'Listing Persons',
            'persons' => $persons
        ]);
    }

    /**
     * @return string
     */
    public function createAction(): string
    {
        return $this->view->render('persons/create', [
            'title' => 'Create a new Person',
        ]);
    }

    /**
     * @return void
     */
    public function storeAction(): void
    {
        $attributes = $this->globals->post->getAll();

        // TODO: add some validation before setting the attributes
        $person = $this->model->create([
            'name'     => $attributes['name'] ?? '',
            'age'      => $attributes['age'] ?? '',
            'username' => $attributes['username'] ?? '',
            'email'    => $attributes['email'] ?? '',
            'address'  => $attributes['address'] ?? '',
            'company'  => $attributes['company'] ?? '',
        ]);

        $person->save();

        $this->session->flash('A new person was created successfully!', 'notification');

        $this->router->redirect('/persons');
    }

    /**
     * @param string $path
     * @param string $id
     *
     * @return string
     */
    public function showAction(string $path, ?string $id): string
    {
        $person = $this->model->find($id);
        $others = $this->model->where('id', '<>', $id);

        return $this->view->render('persons/show', [
            'title' => 'Showing: ' . $person->getName(),
            'person' => $person,
            'others' => $others
        ]);
    }

    /**
     * @param string $path
     * @param string $id
     *
     * @return string
     */
    public function editAction(string $path, ?string $id): string
    {
        $person = $this->model->find($id);

        return $this->view->render('persons/edit', [
            'title' => 'Editing: ' . $person->getName(),
            'person' => $person
        ]);
    }

    /**
     * @param string $path
     * @param string $id
     *
     * @return void
     */
    public function updateAction(string $path, ?string $id): void
    {
        $person = $this->model->find($id);

        $attributes = $this->globals->post->getAll();

        // TODO: add some validation before setting the attributes
        $person->setAttributes($attributes);

        $person->save();

        $this->session->flash('Person with ID ' . $id . ' was updated successfully!', 'notification');

        $this->router->redirect('/persons');
    }

    /**
     * @param string $path
     * @param string $id
     *
     * @return void
     */
    public function destroyAction(string $path, ?string $id): void
    {
        $person = $this->model->find($id);

        $person->delete();

        $this->session->flash('Person with ID ' . $id . ' was deleted successfully!', 'notification');

        $this->router->redirect('/persons');
    }

    /**
     * @route ("/persons/list", {GET})
     *
     * @return void
     */
    public function listAction(): void
    {
        $this->router->forward('/persons');
    }

    /**
     * This method is used to seed the database with some test data.
     *
     * @param bool $keepOldData
     *
     * @return void
     */
    public static function createTestData(bool $keepOldData = true): void
    {
        if (!$keepOldData) {
            array_map(fn ($person) => $person->delete(), Person::all());
        }

        $json = file_get_contents('https://jsonplaceholder.typicode.com/users');

        foreach (json_decode($json) as $person) {
            $person = new Person([
                'name' => $person->name,
                'age' => rand(18, 67),
                'username' => strtolower($person->username),
                'email' => strtolower($person->email),
                'address' => (function () use ($person) {
                    $address = (array)$person->address;
                    unset($address['geo']);
                    return implode(', ', $address);
                })(),
                'company' => $person->company->name,
            ]);

            $person->save();
        }
    }
}
