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

    protected $uidSuffix;

    /**
     * ICalBuilderForAccount constructor.
     * @param Account $account
     */
    public function __construct(Account $account, $container)
    {
        $this->account = $account;
        $this->container = $container;
        $urlBits = parse_url($this->container->get('parameter_bag')->get('app.instance_url'));
        $this->uidSuffix = $urlBits['host'];
    }


    public function getStart()
    {
        $txt = Library::getIcalLine('BEGIN', 'VCALENDAR');
        $txt .= Library::getIcalLine('VERSION', '2.0');
        $txt .= Library::getIcalLine('PRODID', '-//TheOccasionOctopus//NONSGML TheOccasionOctopus//EN');
        $txt .= Library::getIcalLine('X-WR-CALNAME', $this->account->getTitle(). " - ".$this->container->get('parameter_bag')->get('app.instance_name'));
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
        $txt .= Library::getIcalLine('UID', $event->getAccount()->getId().'_'.$event->getSlug().'@'.$this->uidSuffix);

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
                    'event_slug' => $event->getSlug(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $description = $event->getDescription()."\n\n\nEvent Page:".$url."\n\n";
            if ($event->getUrl()) {
                $description .= 'Find out more: '. $event->getUrl()."\n\n";
            }
            if ($event->getUrlTickets()) {
                $description .= 'Get Tickets: '. $event->getUrl()."\n\n";
            }
            $txt .= Library::getIcalLine('DESCRIPTION', $description);

            // TODO a HTML description?
        }

        if ($event->isAllDay()) {
            $txt .= Library::getIcalLine('DTSTART;VALUE=DATE', $event->getStart()->format("Ymd"));
            $txt .= Library::getIcalLine('DTEND;VALUE=DATE', $event->getEnd()->add(new \DateInterval("P1D"))->format("Ymd"));
        } else {
            $txt .= Library::getIcalLine('DTSTART', $event->getStart('UTC')->format("Ymd") . "T" . $event->getStart('UTC')->format("His") . "Z");
            $txt .= Library::getIcalLine('DTEND', $event->getEnd('UTC')->format("Ymd") . "T" . $event->getEnd('UTC')->format("His") . "Z");
        }

        if ($event->getRrule()) {
            $txt .= Library::getIcalLine('RRULE', $event->getRrule(), false);
        }

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
