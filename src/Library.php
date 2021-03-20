<?php

namespace App;

use App\Entity\Account;
use App\Entity\Event;
use App\Entity\EventHasSourceEvent;
use App\Entity\EventHasTag;
use App\Entity\Helper\InterfaceStartEnd;
use App\Entity\Source;
use App\Entity\SourceEvent;
use App\Entity\Tag;
use App\Service\HistoryWorker\HistoryWorker;
use App\Service\HistoryWorker\HistoryWorkerService;

class Library
{
    public static function GUID()
    {
        return strtolower(
            sprintf(
                '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
                mt_rand(0, 65535),
                mt_rand(0, 65535),
                mt_rand(0, 65535),
                mt_rand(16384, 20479),
                mt_rand(32768, 49151),
                mt_rand(0, 65535),
                mt_rand(0, 65535),
                mt_rand(0, 65535)
            )
        );
    }

    /**
     * Public for testing
     * @param type $key
     * @param type $value
     * @return type
     */
    public static function getIcalLine(string $key, ?string $value, bool $escapeSemiColons=true)
    {
        if (is_null($value)) {
            $value = '';
        }

        // should be wrapping long lines and escaping new lines
        $value = str_replace("\\", "\\\\", $value);
        $value = str_replace("\r", "", str_replace("\n", '\\n', $value));
        if ($escapeSemiColons) {
            $value = str_replace(";", "\\;", $value);
        }
        $value = str_replace(",", "\\,", $value);
        $value = iconv("UTF-8", "ISO-8859-1//TRANSLIT//IGNORE", $value);
        // google calendar does not like a space after the ':'.
        $out = $key . ":" . $value;
        if (strlen($out) > 75) {
            $out = $key . ":";
            # first Line;
            $charsToAdd = 75 - strlen($out);
            $out .= substr($value, 0, $charsToAdd) . "\r\n";
            $value = substr($value, $charsToAdd);
            # rest of the lines
            while ($value) {
                $out .= " " . substr($value, 0, 74) . "\r\n";
                $value = substr($value, 74);
            }
            return $out;
        } else {
            return $out . "\r\n";
        }
    }


    public static function randomString($minLength = 10, $maxLength = 100)
    {
        $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $string = '';
        $length = mt_rand($minLength, $maxLength);
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $string;
    }

    public static function makeAccountUsernameCanonical($username)
    {
        // TODO we can do better than this (UTF-8 stuff, etc) but this will do for now.
        return strtolower($username);
    }

    public static function makeEmailCanonical($email)
    {
        // Lower case everything after @ for instance, so bob@EXAMPLE.com and bob@example.com are the same
        $bits = explode('@', $email, 2);
        return $bits[0] . '@' . strtolower($bits[1]);
    }

    public static function getAPIJSONResponseForObject(InterfaceStartEnd $data)
    {
        if ($data->isAllDay()) {
            return [
                'all_day'=>$data->isAllDay(),
                'start_epoch'=>$data->getStart()->getTimeStamp(),
                'end_epoch'=>$data->getEnd()->getTimeStamp(),
                'start_utc' => [
                    'year'=>intval($data->getStart('UTC')->format('Y')),
                    'month'=>intval($data->getStart('UTC')->format('n')),
                    'day'=>intval($data->getStart('UTC')->format('j')),
                ],
                'end_utc' => [
                    'year'=>intval($data->getEnd('UTC')->format('Y')),
                    'month'=>intval($data->getEnd('UTC')->format('n')),
                    'day'=>intval($data->getEnd('UTC')->format('j')),
                ],
                'start_timezone' => [
                    'year'=>intval($data->getStart()->format('Y')),
                    'month'=>intval($data->getStart()->format('n')),
                    'day'=>intval($data->getStart()->format('j')),
                ],
                'end_timezone' => [
                    'year'=>intval($data->getEnd()->format('Y')),
                    'month'=>intval($data->getEnd()->format('n')),
                    'day'=>intval($data->getEnd()->format('j')),
                ],
            ];
        } else {
            return [
                'all_day'=>$data->isAllDay(),
                'start_epoch'=>$data->getStart()->getTimeStamp(),
                'end_epoch'=>$data->getEnd()->getTimeStamp(),
                'start_utc' => [
                    'year'=>intval($data->getStart('UTC')->format('Y')),
                    'month'=>intval($data->getStart('UTC')->format('n')),
                    'day'=>intval($data->getStart('UTC')->format('j')),
                    'hour'=>intval($data->getStart('UTC')->format('G')),
                    'minute'=>intval($data->getStart('UTC')->format('i')),
                    'second'=>intval($data->getStart('UTC')->format('s')),
                    'iso8601'=>intval($data->getStart('UTC')->format('c')),
                ],
                'end_utc' => [
                    'year'=>intval($data->getEnd('UTC')->format('Y')),
                    'month'=>intval($data->getEnd('UTC')->format('n')),
                    'day'=>intval($data->getEnd('UTC')->format('j')),
                    'hour'=>intval($data->getEnd('UTC')->format('G')),
                    'minute'=>intval($data->getEnd('UTC')->format('i')),
                    'second'=>intval($data->getEnd('UTC')->format('s')),
                    'iso8601'=>intval($data->getEnd('UTC')->format('c')),
                ],
                'start_timezone' => [
                    'year'=>intval($data->getStart()->format('Y')),
                    'month'=>intval($data->getStart()->format('n')),
                    'day'=>intval($data->getStart()->format('j')),
                    'hour'=>intval($data->getStart()->format('G')),
                    'minute'=>intval($data->getStart()->format('i')),
                    'second'=>intval($data->getStart()->format('s')),
                    'iso8601'=>intval($data->getStart()->format('c')),
                ],
                'end_timezone' => [
                    'year'=>intval($data->getEnd()->format('Y')),
                    'month'=>intval($data->getEnd()->format('n')),
                    'day'=>intval($data->getEnd()->format('j')),
                    'hour'=>intval($data->getEnd()->format('G')),
                    'minute'=>intval($data->getEnd()->format('i')),
                    'second'=>intval($data->getEnd()->format('s')),
                    'iso8601'=>intval($data->getEnd()->format('c')),
                ]
            ];
        }
    }

    public static function parseWebFingerResourceToUsernameAndHost($in)
    {
        if (substr($in, 0, 5) == 'acct:') {
            $in = substr($in, 5);
        }

        $bits = explode("@", $in);
        while ($bits[0] == '') {
            array_shift($bits);
        }

        $username = array_shift($bits);
        $host = array_shift($bits);

        return [ $username, $host ];
    }

    public static function parseAccountHandleWithServerToUsernameAndHost($in)
    {
        $bits = explode("@", $in);
        while ($bits[0] == '') {
            array_shift($bits);
        }

        $username = array_shift($bits);
        $host = array_shift($bits);

        return [ $username, $host ];
    }

    public static function parseURLToSSLAndHost($url)
    {
        $url_bits = parse_url(trim($url));
        if (!$url_bits) {
            throw new \Exception("Are you sure this is a URL? " . $url);
        }
        if (!in_array($url_bits['scheme'], ['http','https'])) {
            throw new \Exception("Must be http or https: " . $url);
        }

        // TODO use port, user and pass too!
        $host = $url_bits['host'];
        if (array_key_exists('port', $url_bits) && $url_bits['port'] && $url_bits['port'] != 80 && $url_bits['port'] != 443) {
            $host .= ':'.$url_bits['port'];
        }
        $ssl = ($url_bits['scheme'] == 'https');

        return [ $ssl, $host ];
    }

    public static function getActivityStreamsActorURLFromWebFingerData($data)
    {
        foreach ($data['links'] as $linkData) {
            if ($linkData['rel']== 'self' && $linkData['type'] == 'application/activity+json') {
                return $linkData['href'];
            }
        }
    }
    
    
    public static function isEndBeforeStartByArrays($startDate, $startTime, $endDate, $endTime)
    {
        if ($endDate['year'] < $startDate['year']) {
            return true;
        } elseif ($endDate['year'] == $startDate['year']) {
            if ($endDate['month'] < $startDate['month']) {
                return true;
            } elseif ($endDate['month'] == $startDate['month']) {
                if ($endDate['day'] < $startDate['day']) {
                    return true;
                } elseif ($endDate['day'] == $startDate['day'] && !is_null($startTime)) {
                    if ($endTime['hour'] < $startTime['hour']) {
                        return true;
                    } elseif ($endTime['hour'] == $startTime['hour']) {
                        if ($endTime['minute'] < $startTime['minute']) {
                            return true;
                        } elseif ($endTime['minute'] == $startTime['minute']) {
                            if ($endTime['second'] < $startTime['second']) {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
}
