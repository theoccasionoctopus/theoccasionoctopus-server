<?php

namespace App\Controller;

use App\Entity\Import;
use App\FilterParams\EventListFilterParams;
use App\Form\ImportNewType;
use App\RepositoryQuery\EventRepositoryQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;



class AccountManageSettingsController extends AccountManageController
{

    public function index($account_username, Request $request)
    {

        $this->build($account_username);

        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Import::class);
        $imports = $repository->findBy(['account'=>$this->account]);

        return $this->render('account/manage/settings/index.html.twig', $this->getTemplateVariables([
            'imports'=>$imports,
        ]));

    }

    public function newImport($account_username, Request $request)
    {

        $this->build($account_username);

        // build the form
        $import = new Import();
        $import->setAccount($this->account);
        $import->setId(Library::GUID());
        $import->setEnabled(true);
        // TODO let user select in form instead
        $import->setDefaultCountry($this->account->getAccountLocal()->getDefaultCountry());
        $import->setDefaultTimezone($this->account->getAccountLocal()->getDefaultTimezone());

        $form = $this->createForm(ImportNewType::class, $import, array(
            'account' => $this->account,
        ));

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Save
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($import);
            $entityManager->flush();

            // redirect
            return $this->redirectToRoute('account_manage_settings', ['account_username' => $this->account->getUsername() ]);
        }

        return $this->render('account/manage/settings/newImport.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'form' => $form->createView(),
        ]));

    }


}
