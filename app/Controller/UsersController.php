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

class UsersController extends Controller
{
    protected function associateModel(): ?string
    {
        return $this->config->get('auth.user.model');
    }

    protected function registerRoutes(): bool
    {
        return true;
    }

    /**
     * @route("/register", {GET, POST})
     *
     * @return string|void
     */
    public function registerAction()
    {
        if ($this->auth->check()) {
            return $this->router->forward('/auth');
        }

        if ($this->globals->server->get('REQUEST_METHOD') == 'GET') {
            return $this->view->render('users/register', [
                'title' => 'Register',
            ]);
        }

        $username = $this->globals->post->get('username');
        $password = $this->globals->post->get('password');

        $usernameIsValid = preg_match('/[a-zA-Z0-9.-_]+/', $username);

        if (!$usernameIsValid) {
            return $this->view->render('users/register', [
                'title' => 'Register',
            ]);
        }

        $success = $this->auth->register($username, $password);

        if (!$success) {
            $this->session->flash('Username already taken!', 'notification');

            return $this->view->render('users/register', [
                'title' => 'Register',
            ]);
        }

        $this->session->flash('User was registered successfully!', 'notification');

        return $this->router->redirect('/login');
    }

    /**
     * @route("/unregister", {GET})
     *
     * @return void
     */
    public function unregisterAction()
    {
        if ($this->auth->check() === false) {
            $this->auth->fail();
        }

        $this->auth->unregister(
            $this->auth->user()->getUsername()
        );

        $this->session->flash('User was unregistered successfully!', 'notification');

        return $this->router->redirect('/register');
    }

    /**
     * @route("/login", {GET, POST})
     *
     * @return string|void
     */
    public function loginAction()
    {
        if ($this->auth->check()) {
            return $this->router->forward('/auth');
        }

        if ($this->globals->server->get('REQUEST_METHOD') == 'GET') {
            return $this->view->render('users/login', [
                'title' => 'Log in',
            ]);
        }

        $username = $this->globals->post->get('username');
        $password = $this->globals->post->get('password');

        $success = $this->auth->login($username, $password);

        if ($success) {
            return $this->router->redirect('/auth');
        } else {
            $this->session->flash('Wrong username or password!', 'notification');

            return $this->view->render('users/login', [
                'title' => 'Log in',
            ]);
        }

        return $this->router->redirect('/login');
    }

    /**
     * @route("/logout", {GET})
     *
     * @return void
     */
    public function logoutAction()
    {
        $this->auth->logout();

        return $this->router->redirect('/login');
    }

    /**
     * @route("/auth", {GET})
     *
     * @return void
     */
    public function indexAction()
    {
        return $this->view->render('users/index', [
            'title' => 'Auth',
            'user' => $this->auth->user(),
        ]);
    }

    /**
     * @route("/auth*", {GET})
     *
     * @return void
     */
    public function authMiddleware()
    {
        if ($this->auth->check() === false) {
            $this->auth->fail();
        }
    }
}
