<?php

namespace App\Controller;

use App\Service\HistoryWorker\HistoryWorkerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Tag;
use App\Library;
use App\Form\TagNewType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

class AccountManageTagNewController extends AccountManageController
{
    public function newTag($account_username, Request $request, HistoryWorkerService $historyWorkerService)
    {
        $this->setUpAccountManage($account_username, $request);

        // build the form
        $form = $this->createForm(TagNewType::class, null, array(
            'account' => $this->account,
        ));

        // handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $existingTag = $this->getDoctrine()->getRepository(Tag::class)->findOneBy(array('account' => $this->account, 'title' => $form->get('title')->getData()));
            ;
            if ($existingTag) {
                $form->get('title')->addError(new FormError('There is already a tag with that title'));
            }

            if ($form->isValid()) {

                // Set data
                $tag = new Tag();
                $tag->setTitle($form->get('title')->getData());
                $tag->setDescription($form->get('description')->getData());
                $tag->setEnabled(true);
                $tag->setId(Library::GUID());
                $tag->setAccount($this->account);
                $tag->setPrivacy($form->get('privacy')->getData());

                // Save
                $historyWorker = $historyWorkerService->getHistoryWorker($this->account, $this->get('security.token_storage')->getToken()->getUser());
                $historyWorker->addTag($tag);
                $historyWorkerService->persistHistoryWorker($historyWorker);

                // redirect
                $this->addFlash(
                    'success',
                    'Tag created!'
                );
                return $this->redirectToRoute('account_manage_tag', ['account_username' => $this->account->getUsername()]);
            }
        }

        return $this->render('account/manage/tag/new.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'form' => $form->createView(),
        ]));
    }
}
