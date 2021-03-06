<?php

namespace Concrete\Controller\SinglePage;

use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Page\Controller\PageController;
use Concrete\Core\User\User;

class PageForbidden extends PageController
{
    protected $viewPath = '/frontend/page_forbidden';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Controller\AbstractController::on_start()
     */
    public function on_start()
    {
        return $this->checkRedirectToLogin();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function view()
    {
        return $this->checkRedirectToLogin();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    protected function checkRedirectToLogin()
    {
        $result = null;
        $user = User::isLoggedIn() ? new User() : null;
        if ($user === null || !$user->isRegistered()) {
            $config = $this->app->make('config');
            if ($config->get('concrete.permissions.forward_to_login')) {
                $result = $this->app->make(ResponseFactoryInterface::class)->redirect('/login');
            }
        }

        return $result;
    }
}
