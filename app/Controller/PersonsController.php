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
    /**
     * {@inheritDoc}
     */
    protected function associateModel(): ?string
    {
        return Person::class;
    }

    /**
     * {@inheritDoc}
     */
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
     * @route("/persons/list", {GET})
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

        $persons = [
            [
                'name' => 'Todd Riley',
                'age' => 32,
                'username' => 'todd.riley',
                'email' => 'todd.riley@domain.tld',
                'address' => 'Andre Street 3, 69841 Santiago City, Country',
                'company' => 'Wesley Benson and Sons',
            ],
            [
                'name' => 'Clarence Graham',
                'age' => 49,
                'username' => 'clarence.graham',
                'email' => 'clarence.graham@domain.tld',
                'address' => 'Troy Street 62, 69572 Holloway City, Country',
                'company' => 'Lena Sanchez LLC',
            ],
            [
                'name' => 'Nannie Obrien',
                'age' => 28,
                'username' => 'nannie.obrien',
                'email' => 'nannie.obrien@domain.tld',
                'address' => 'Barbara Street 64, 32953 Crawford City, Country',
                'company' => 'Aiden Sanchez Inc.',
            ],
            [
                'name' => 'Stanley Holt',
                'age' => 61,
                'username' => 'stanley.holt',
                'email' => 'stanley.holt@domain.tld',
                'address' => 'Nicholas Street 89, 99114 Owens City, Country',
                'company' => 'Olive Brock LLC',
            ],
            [
                'name' => 'Jeanette Cunningham',
                'age' => 23,
                'username' => 'jeanette.cunningham',
                'email' => 'jeanette.cunningham@domain.tld',
                'address' => 'Josephine Street 52, 99445 Hall City, Country',
                'company' => 'Leona Johnston Inc.',
            ],
            [
                'name' => 'Tyler Cruz',
                'age' => 52,
                'username' => 'tyler.cruz',
                'email' => 'tyler.cruz@domain.tld',
                'address' => 'Floyd Street 23, 26375 Mason City, Sint Country',
                'company' => 'Abbie Coleman LLC',
            ],
            [
                'name' => 'Walter Stewart',
                'age' => 27,
                'username' => 'walter.stewart',
                'email' => 'walter.stewart@domain.tld',
                'address' => 'Della Street 100, 62204 Huff City, Country',
                'company' => 'Antonio Potter Ltd.',
            ],
            [
                'name' => 'Eric Lee',
                'age' => 41,
                'username' => 'eric.lee',
                'email' => 'eric.lee@domain.tld',
                'address' => 'Paul Street 13, 72687 Spencer City, Country',
                'company' => 'Sylvia Schneider Corp',
            ],
            [
                'name' => 'Ruth Harmon',
                'age' => 29,
                'username' => 'ruth.harmon',
                'email' => 'ruth.harmon@domain.tld',
                'address' => 'Beatrice Street 99, 38186 Perez City, Country',
                'company' => 'Andrew Poole LLC',
            ],
            [
                'name' => 'Jim Craig',
                'age' => 65,
                'username' => 'jim.craig',
                'email' => 'jim.craig@domain.tld',
                'address' => 'Kate Street 76, 54801 Harris City, Country',
                'company' => 'Owen Ferguson Ltd.',
            ],
        ];

        foreach ($persons as $person) {
            $person = new Person($person);
            $person->save();
        }
    }
}
