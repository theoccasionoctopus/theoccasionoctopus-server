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
use Psr\Log\LoggerInterface;
use App\Message\NewUserMessage;

class UserController extends BaseController
{
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {

        // If already logged in, go to homepage
        $user = $this->get('security.token_storage')->getToken() ? $this->get('security.token_storage')->getToken()->getUser() : null;
        if ($user instanceof User) {
            return $this->redirectToRoute('index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'user/login.html.twig',
            $this->getTemplateVariables(['last_username' => $lastUsername, 'error' => $error])
        );
    }

    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, UserAuthenticator $formAuthenticator, LoggerInterface $logger)
    {
        // Read Only
        if ($this->getParameter('app.instance_read_only')) {
            return $this->render('instance_read_only.html.twig');
        }

        // If already logged in, go to homepage
        $user = $this->get('security.token_storage')->getToken() ? $this->get('security.token_storage')->getToken()->getUser() : null;
        if ($user instanceof User) {
            return $this->redirectToRoute('index');
        }

        //  build the form
        $user = new User();
        $form = $this->createForm(UserRegisterType::class, $user);

        //  handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            // TODO if email already used, show nice error

            if ($form->get('magicWord')->getData() != $this->getParameter('app.user_register_instance_password')) {

                // Log
                $logger->info('User Register Instance Password Wrong', []);

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

                // Log
                $logger->info('New user created', ['user_id'=>$user->getId()]);

                // Message
                $this->dispatchMessage(new NewUserMessage($user->getId()));

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
