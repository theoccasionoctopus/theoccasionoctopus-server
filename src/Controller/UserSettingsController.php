<?php

namespace App\Controller;

use App\Entity\APIAccessToken;
use App\Form\APIAccessTokenNewType;
use App\MeetupAPICaller;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\UserRegisterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use App\Entity\Account;
use App\Library;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserSettingsController extends BaseController
{

    /** @var  User */
    protected $user;

    public function build() {
        $this->user= $this->get('security.token_storage')->getToken()->getUser();
        if (!($this->user instanceof User)) {
            throw new  AccessDeniedException('You must log in first!');
        }
    }

    public function index(Request $request) {
        $this->build();

        return $this->render(
            'user/settings/index.html.twig',
            $this->getTemplateVariables([
                'user'=>$this->user,
            ])
        );
    }

    public function accessTokens(Request $request) {
        $this->build();

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(APIAccessToken::class);
        $accessTokens = $repository->findBy(['user'=>$this->user]);

        return $this->render(
            'user/settings/accessTokens.html.twig',
            $this->getTemplateVariables([
                'user'=>$this->user,
                'accessTokens'=>$accessTokens,
            ])
        );
    }

    public function accessTokensNew(Request $request) {
        $this->build();

        // TODO check $user $limitNumberOfAPIAccessTokens and block

        $accessToken = new APIAccessToken();
        $accessToken->setUser($this->user);
        $accessToken->setToken(Library::randomString(50, 200));
        // TODO check this is not already used!!!!

        // TODO let user set one acount only

        // build the form
        $form = $this->createForm(APIAccessTokenNewType::class, $accessToken, array(
        ) );

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $doctrine = $this->getDoctrine();
            $doctrine->getManager()->persist($accessToken);
            $doctrine->getManager()->flush();

            // redirect
            $this->addFlash(
                'success',
                'API Token created!'
            );
            return $this->redirectToRoute('user_settings_access_token');
        }

        return $this->render('user/settings/newAccessToken.html.twig', $this->getTemplateVariables([
            'user'=>$this->user,
            'form' => $form->createView(),
        ]));
    }

}