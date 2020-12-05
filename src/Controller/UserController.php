<?php

namespace App\Controller;

use App\Security\UserAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\UserRegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use App\Entity\Account;
use App\Library;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class UserController extends BaseController
{

    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'user/login.html.twig',
            $this->getTemplateVariables(['last_username' => $lastUsername, 'error' => $error])
        );
    }

    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, UserAuthenticator $formAuthenticator)
    {
        //  build the form
        $user = new User();
        $form = $this->createForm(UserRegisterType::class, $user);

        //  handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            // TODO if email already used, show nice error

            if ($form->get('magicWord')->getData() != $this->getParameter('app.user_register_instance_password')) {

                $form->get('magicWord')->addError(new FormError('Wrong Magic Word!'));

            }

            if ($form->isValid()) {

                // Encode the password (you could also do this via Doctrine listener)
                $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);

                //  save the User!
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // Log user in ourselves and redirect
                $token = $formAuthenticator->createAuthenticatedToken($user, 'main');
                $guardHandler->authenticateWithToken($token, $request, 'main');
                $this->addFlash(
                    'success',
                    'Welcome!'
                );
                return $this->redirectToRoute('index');

            }
        }

        return $this->render(
            'user/register.html.twig',
            $this->getTemplateVariables(array('form' => $form->createView()))
        );
    }


}
