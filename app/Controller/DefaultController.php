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

class DefaultController extends Controller
{
    /**
     * Example action.
     *
     * @param string $path The current path matched by the router i.e. `/page/1`.
     * @param string|null $match The match from the route if there was any (`/page/{id}` -> `$match` = `id`).
     * @param mixed|null $previous The result of the last middleware or route with a matching path.
     *
     * @return string
     */
    public function exampleAction(string $path, ?string $match, $previous)
    {
        /**
         * This is an example to show you how to work with VELOX controllers.
         * What is written here is only for demonstration purposes.
         * Normally, here you should only process the data and pass them to some view.
         *
         * You may delete this file or change directory structure in "./app" as you wish, only do not forget to follow PSR-4.
         */

        $this->data->set('page.title', 'Example');

        $this->view->section(
            $this->config->get('view.defaultSectionName'),
            $this->view->partial('hero', [
                'title'=> 'Hi there,',
                'subtitle'=> 'This is the response from the ' . __METHOD__
            ])
        );

        return $this->view->render(
            $this->config->get('view.defaultPageName'),
            $this->config->get('view.defaultPageVars'),
            $this->config->get('view.defaultLayoutName')
        );
    }
}
