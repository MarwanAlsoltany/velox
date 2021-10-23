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

        // this data is built using https://jsonplaceholder.typicode.com/users
        $persons = [
            [
                'name' => 'Leanne Graham',
                'age' => 28,
                'username' => 'bret',
                'email' => 'sincere@april.biz',
                'address' => 'Kulas Light, Apt. 556, Gwenborough, 92998-3874',
                'company' => 'Romaguera-Crona',
            ],
            [
                'name' => 'Ervin Howell',
                'age' => 40,
                'username' => 'antonette',
                'email' => 'shanna@melissa.tv',
                'address' => 'Victor Plains, Suite 879, Wisokyburgh, 90566-7771',
                'company' => 'Deckow-Crist',
            ],
            [
                'name' => 'Clementine Bauch',
                'age' => 42,
                'username' => 'samantha',
                'email' => 'nathan@yesenia.net',
                'address' => 'Douglas Extension, Suite 847, McKenziehaven, 59590-4157',
                'company' => 'Romaguera-Jacobson',
            ],
            [
                'name' => 'Patricia Lebsack',
                'age' => 63,
                'username' => 'karianne',
                'email' => 'julianne.oconner@kory.org',
                'address' => 'Hoeger Mall, Apt. 692, South Elvis, 53919-4257',
                'company' => 'Robel-Corkery',
            ],
            [
                'name' => 'Chelsey Dietrich',
                'age' => 21,
                'username' => 'kamren',
                'email' => 'lucio_hettinger@annie.ca',
                'address' => 'Skiles Walks, Suite 351, Roscoeview, 33263',
                'company' => 'Keebler LLC',
            ],
            [
                'name' => 'Mrs. Dennis Schulist',
                'age' => 48,
                'username' => 'leopoldo_corkery',
                'email' => 'karley_dach@jasper.info',
                'address' => 'Norberto Crossing, Apt. 950, South Christy, 23505-1337',
                'company' => 'Considine-Lockman',
            ],
            [
                'name' => 'Kurtis Weissnat',
                'age' => 33,
                'username' => 'elwyn.skiles',
                'email' => 'telly.hoeger@billy.biz',
                'address' => 'Rex Trail, Suite 280, Howemouth, 58804-1099',
                'company' => 'Johns Group',
            ],
            [
                'name' => 'Nicholas Runolfsdottir V',
                'age' => 27,
                'username' => 'maxime_nienow',
                'email' => 'sherwood@rosamond.me',
                'address' => 'Ellsworth Summit, Suite 729, Aliyaview, 45169',
                'company' => 'Abernathy Group',
            ],
            [
                'name' => 'Glenna Reichert',
                'age' => 65,
                'username' => 'delphine',
                'email' => 'chaim_mcdermott@dana.io',
                'address' => 'Dayna Park, Suite 449, Bartholomebury, 76495-3109',
                'company' => 'Yost and Sons',
            ],
            [
                'name' => 'Clementina DuBuque',
                'age' => 37,
                'username' => 'moriah.stanton',
                'email' => 'rey.padberg@karina.biz',
                'address' => 'Kattie Turnpike, Suite 198, Lebsackbury, 31428-2261',
                'company' => 'Hoeger LLC',
            ],
        ];

        foreach ($persons as $person) {
            $person = new Person($person);
            $person->save();
        }
    }
}
