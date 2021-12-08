<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoginFormAuthenticator extends AbstractGuardAuthenticator
{
    protected $encoder; //
    public function __construct(UserPasswordEncoderInterface $encoder) //
    {
        $this->encoder = $encoder; //
    }

    public function supports(Request $request)
    {
        return 'security_login' === $request->attributes->get('_route') // ‘je n’interviens que si la route est security_login
            && $request->isMethod('POST'); // et que si la méthode est POST
    }

    public function getCredentials(Request $request) // donne-moi les infos qui m'intéresse pour la connexion
    {
        return $request->request->get('login'); // dd($request) montre les 3 infos contenues dans 'login': mail, password et _token
    }

    public function getUser($credentials, UserProviderInterface $userProvider) // reçoit les infos de getCredentials() et est-ce qu'elles correspondent à un user de la bdd?
    {
        // UserProviderInterface est un objet qui va être capable d'aller chercher un utilisateur dans les entités User grâce à l'email.
        try {
            return $userProvider->loadUserByUsername($credentials['email']);
        } catch (UsernameNotFoundException $e) {
            throw new AuthenticationException("Cette adresse email n'est pas connue");
        }
    }

    public function checkCredentials($credentials, UserInterface $user) // reçoit les credentials plus le user trouvé en bdd
    {
        // vérifier que le mdp fourni correspond bien au mdp de la bdd. On a besoin de se faire livrer le service d'encodage
        // doit retourner vrai si les credentials sont valides.
        $isValid = $this->encoder->isPasswordValid($user, $credentials['password']); // vrai ou faux
        if (!$isValid) {
            throw new AuthenticationException('Le mot de passe est incorect');
        }
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) // dans toute la procédure le douanier peut passer une AuthenticationException si y a failure.
    {
        // si on valide avec une erreur, comme la fequète en post continue, ça revient sur le formulaire vierge. Donc inutile de faire un redirect, on ne fait rien.
        // Par contre, on va vouloir afficher la raison de l'échec.
        // c’est à nous de stocker l’erreur qui a eu lieu dans la session et on l’a dans $exception :
        $request->attributes->set(Security::AUTHENTICATION_ERROR, $exception); // ça stocke dans les attributs de la requête une clé Security::AUTHENTICATION_ERROR et la valeur qu’aura cette key c’est $exception
        $login = $request->request->get('login');
        $request->attributes->set(Security::LAST_USERNAME, $login['email']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return new RedirectResponse('/');
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse('/login');
    }

    public function supportsRememberMe()
    {
        // todo
    }
}
