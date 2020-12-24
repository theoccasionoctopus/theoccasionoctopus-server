<?php

namespace App;

use App\Entity\Account;
use App\Entity\Event;
use App\Entity\EventHasSourceEvent;
use App\Entity\EventHasTag;
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
    public static function getIcalLine(string $key, ?string $value)
    {
        if (is_null($value)) {
            $value = '';
        }

        // should be wrapping long lines and escaping new lines
        $value = str_replace("\\", "\\\\", $value);
        $value = str_replace("\r", "", str_replace("\n", '\\n', $value));
        $value = str_replace(";", "\\;", $value);
        $value = str_replace(",", "\\,", $value);
        $value = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);
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

    public static function getAPIJSONResponseForDateTime(\DateTimeInterface $dateTime)
    {
        return [
            'year'=>intval($dateTime->format('Y')),
            'month'=>intval($dateTime->format('n')),
            'day'=>intval($dateTime->format('j')),
            'hour'=>intval($dateTime->format('G')),
            'minute'=>intval($dateTime->format('i')),
            'second'=>intval($dateTime->format('s')),
            'iso8601'=>$dateTime->format('c'),
        ];
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
        $url_bits = parse_url($url);
        if (!$url_bits) {
            throw new Exception("Are you sure this is a URL? " . $url);
        }
        if (!in_array($url_bits['scheme'], ['http','https'])) {
            throw new Exception("Must be http or https: " . $url);
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
}
