<?php

namespace App\APIV1;

use App\Entity\Account;
use App\Entity\Event;
use App\Entity\History;
use App\Library;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ICalBuilderForAccount
{

    /** @var  Account */
    protected $account;

    protected $container;


    /**
     * ICalBuilderForAccount constructor.
     * @param Account $account
     */
    public function __construct(Account $account, $container)
    {
        $this->account = $account;
        $this->container = $container;
    }


    public function getStart()
    {
        $txt = Library::getIcalLine('BEGIN', 'VCALENDAR');
        $txt .= Library::getIcalLine('VERSION', '2.0');
        $txt .= Library::getIcalLine('PRODID', '-//TheOccasionOctopus//NONSGML TheOccasionOctopus//EN');
        // TODO use site instance in title
        $txt .= Library::getIcalLine('X-WR-CALNAME', $this->account->getTitle(). " - SITE INSTANCE NAME HERE");
        return $txt;
    }

    public function getEnd()
    {
        $txt = Library::getIcalLine('END', 'VCALENDAR');
        return $txt;
    }

    public function getEvent(Event $event)
    {
        $txt = Library::getIcalLine('BEGIN', 'VEVENT');
        $txt .= Library::getIcalLine('UID', $event->getId()); // TODO add "@ site name"

        if ($event->getDeleted()) {
            $txt .= Library::getIcalLine('SUMMARY', $event->getTitle(). " [DELETED]");
            $txt .= Library::getIcalLine('METHOD', 'CANCEL');
            $txt .= Library::getIcalLine('STATUS', 'CANCELLED');
            $txt .= Library::getIcalLine('DESCRIPTION', 'DELETED');
        } elseif ($event->getCancelled()) {
            $txt .= Library::getIcalLine('SUMMARY', $event->getTitle(). " [CANCELLED]");
            $txt .= Library::getIcalLine('METHOD', 'CANCEL');
            $txt .= Library::getIcalLine('STATUS', 'CANCELLED');
            $txt .= Library::getIcalLine('DESCRIPTION', 'CANCELLED');
        } else {
            $txt .= Library::getIcalLine('SUMMARY', $event->getTitle());

            $url = $this->container->get('router')->generate(
                'account_public_event_show_event',
                [
                    'account_username' => $event->getAccount()->getUserName(),
                    'event_id' => $event->getId(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            // TODO Change to site instance name
            $description = $event->getDescription()."\n\n\nEvent Page:".$url."\n\n";
            if ($event->getUrl()) {
                $description .= 'Find out more: '. $event->getUrl()."\n\n";
            }
            if ($event->getUrlTickets()) {
                $description .= 'Get Tickets: '. $event->getUrl()."\n\n";
            }
            $description .= "\n----\nPowered by The Occasion Octopus";
            $txt .= Library::getIcalLine('DESCRIPTION', $description);

            // TODO a HTML description?
        }

        $txt .= Library::getIcalLine('DTSTART', $event->getStart('UTC')->format("Ymd")."T".$event->getStart('UTC')->format("His")."Z");
        $txt .= Library::getIcalLine('DTEND', $event->getEnd('UTC')->format("Ymd")."T".$event->getEnd('UTC')->format("His")."Z");

        /** @var History $eventLastUpdatedHistory */
        $eventLastUpdatedHistory = $this->container->get('doctrine')->getRepository(History::class)->getLastHistoryForEvent($event);
        if ($eventLastUpdatedHistory) {
            $txt .= Library::getIcalLine(
                'LAST-MODIFIED',
                $eventLastUpdatedHistory->getCreated('UTC')->format("Ymd") . "T" .$eventLastUpdatedHistory->getCreated('UTC')->format("His") . "Z"
            );
            $txt .= Library::getIcalLine(
                'DTSTAMP',
                $eventLastUpdatedHistory->getCreated('UTC')->format("Ymd") . "T" .$eventLastUpdatedHistory->getCreated('UTC')->format("His") . "Z"
            );
            // 1602183673 is a magic number - it's the timestamp at the time we registered the domain for this software.
            // Since we can't have any values less than that, we will reduce SEQUENCE by that to keep SEQUENCE reasonably small.
            $txt .= Library::getIcalLine(
                'SEQUENCE',
                $eventLastUpdatedHistory->getCreated('UTC')->getTimestamp() - 1602183673
            );
        } else {
            $txt .= Library::getIcalLine('SEQUENCE', 0);
            $txt .= Library::getIcalLine('DTSTAMP', '20201008T190113Z');
        }

        $txt .= Library::getIcalLine('END', 'VEVENT');

        return $txt;
    }
}
