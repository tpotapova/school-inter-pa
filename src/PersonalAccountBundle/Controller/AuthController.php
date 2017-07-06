<?php
namespace PersonalAccountBundle\Controller;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PersonalAccountBundle\Entity\User;
use PersonalAccountBundle\Form\UserType;

class AuthController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $helper = $this->get('security.authentication_utils');

        return $this->render(
            'default/login.html.twig',
            array(
                'last_username' => $helper->getLastUsername(),
                'error'         => $helper->getLastAuthenticationError(),
            )
        );
    }
    /**
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the new users password
            $encoder = $this->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // Save
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $user_id = $user->getId();
            $session = $request->getSession();
            $session->set('user_id', $user_id);
            if ($user->getRole() == 'ROLE_USER'){
                return $this->redirectToRoute('indexStudent');
            }
            else if ($user->getRole() == 'ROLE_ADMIN'){
                return $this->redirectToRoute('indexTeacher');
            }
        }

        return $this->render('default/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/login_check", name="security_login_check")
     */
    public function loginCheckAction()
    {

    }
    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {

    }

}
