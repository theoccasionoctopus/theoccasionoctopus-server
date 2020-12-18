<?php

namespace App\Controller;

use App\FilterParams\AccountDiscoverEventListFilterParams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Account;
use App\Entity\Event;
use App\Library;
use App\Form\EventNewType;
use Symfony\Component\HttpFoundation\Request;

class AccountManageDiscoverEventListController extends AccountManageController
{
    public function indexDiscover($account_username, Request $request)
    {
        $this->build($account_username);

        $params = new AccountDiscoverEventListFilterParams($this->getDoctrine(), $this->account);
        $params->build($_GET);

        $eventOccurrences = $params->getRepositoryQuery()->getEventOccurrences();

        return $this->render('account/manage/discover/event/index.html.twig', $this->getTemplateVariables([
            'account'=> $this->account,
            'eventListFilterParams'=>$params,
            'eventOccurrences' => $eventOccurrences,
        ]));
    }
}
