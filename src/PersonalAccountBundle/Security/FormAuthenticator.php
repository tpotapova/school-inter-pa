<?php
/**
 * Created by PhpStorm.
 * User: Potap
 * Date: 29.10.2016
 * Time: 1:26
 */

namespace PersonalAccountBundle\Security;

use Doctrine\ORM\EntityManager;
use PersonalAccountBundle\PersonalAccountBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;


class FormAuthenticator extends AbstractFormLoginAuthenticator
{

    private $router;
    private $encoder;
    private $em;


    public function __construct(RouterInterface $router,UserPasswordEncoderInterface $encoder, EntityManager $em)
    {
        $this->router = $router;
        $this->encoder = $encoder;
        $this->em = $em;
    }

    public function getCredentials(Request $request)
    {

        if ($request->getMethod() != Request::METHOD_POST || $request->getPathInfo() != '/login') {
            return;
        }
        $username = $request->request->get('_name');
        $request->getSession()->set(Security::LAST_USERNAME, $username);
        $password = $request->request->get('_password');
        return [
            'username'=>$username,
            'password' => $password,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['username'];
        return $userProvider->loadUserByUsername($username);

    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $plainPassword = $credentials['password'];
        if ($this->encoder->isPasswordValid($user, $plainPassword)) {
            return true;
        }
        throw new BadCredentialsException();
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
       $request->getSession()->set(Security::AUTHENTICATION_ERROR,$exception);

       $url = $this->router->generate('login');
       return new RedirectResponse($url);
    }

    public function  onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $user = $token->getUser();
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $response = new RedirectResponse($this->router->generate('students'));
        }
        else if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $teacher = $this->em->getRepository('PersonalAccountBundle:Teacher')->findOneBy([
                'user_id'=>$user->getId()
            ]);
            if (is_null($teacher)) {
                $response = new RedirectResponse($this->router->generate('indexTeacher'));
            }
            else {
                $response = new RedirectResponse($this->router->generate('teacher'));
            }
        }
        else {
            $student = $this->em->getRepository('PersonalAccountBundle:Student')->findOneBy([
                'user_id'=>$user->getId()
            ]);
            if (is_null($student)){
                $response = new RedirectResponse($this->router->generate('indexStudent'));
            }
            else {
                $response = new RedirectResponse($this->router->generate('student'));
            }

        }
        return $response;
        //$url = $this->router->generate('index');
        //return new RedirectResponse($url);
    }

    public function supportsRememberMe()
    {
        return true;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('login');
    }

    protected function getDefaultSuccessRedirectUrl()
    {

    }
}