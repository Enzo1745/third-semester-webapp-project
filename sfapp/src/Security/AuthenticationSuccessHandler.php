<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Routing\RouterInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        if ($token->getRoleNames()){
            foreach ($token->getRoleNames() as $role){
                if ($role == 'ROLE_CHARGE'){
                    return new RedirectResponse($this->router->generate('app_room_list'));
                }
                if ($role == 'ROLE_TECH'){
                    return new RedirectResponse($this->router->generate('app_technician_sa'));
                }
            }
        }
        return new RedirectResponse($this->router->generate('app_connexion'));
    }
}
