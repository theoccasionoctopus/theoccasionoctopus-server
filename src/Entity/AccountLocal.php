<?php

namespace App\Entity;

use App\Library;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountLocalRepository")
 * @ORM\Table(name="account_local")
 * @ORM\HasLifecycleCallbacks()
 */
class AccountLocal
{


    /**
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     * @ORM\OneToOne(targetEntity="App\Entity\Account")
     */
    private $account;

    /**
     * @ORM\Column(type="string", length=500, unique=false, nullable=false)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=500, unique=true, nullable=false)
     */
    private $usernameCanonical;

    /**
     * @var Country
     * @ORM\JoinColumn(name="default_country_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="App\Entity\Country", inversedBy="accounts")
     *
     */
    private $default_country;

    /**
     * @var TimeZone
     * @ORM\JoinColumn(name="default_timezone_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="App\Entity\TimeZone", inversedBy="accounts")
     *
     */
    private $default_timezone;


    /**
     *
     *
     * @ORM\Column(name="default_privacy", type="smallint", unique=false, nullable=false, options={"default" : 10000})
     */
    private $default_privacy;


    /**
     * @ORM\Column(name="seo_index_follow", type="boolean", nullable=false, options={"default" : false})
     */
    private $seo_index_follow;


    /**
     * @ORM\Column(name="list_in_directory", type="boolean", nullable=false, options={"default" : false})
     */
    private $list_in_directory;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : false})
     */
    private $locked = false;


    /**
     * Should really be nullable=false but we have old accounts to deal with
     * @ORM\Column(name="key_private", type="text", nullable=true)
     */
    private $keyPrivate;

    /**
     * Should really be nullable=false but we have old accounts to deal with
     * @ORM\Column(name="key_public", type="text", nullable=true)
     */
    private $keyPublic;

    /**
     * @var boolean
     * @ORM\Column(name="manually_approves_followers", type="boolean", nullable=false, options={"default" : false})
     */
    private $manuallyApprovesFollowers = false;

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
        $this->usernameCanonical = Library::makeAccountUsernameCanonical($username);
    }

    /**
     * @return mixed
     */
    public function getUsernameCanonical()
    {
        return $this->usernameCanonical;
    }

    /**
     * @return Country
     */
    public function getDefaultCountry(): Country
    {
        return $this->default_country;
    }

    /**
     * @param Country $default_country
     */
    public function setDefaultCountry(Country $default_country)
    {
        $this->default_country = $default_country;
    }

    /**
     * @return TimeZone
     */
    public function getDefaultTimezone(): TimeZone
    {
        return $this->default_timezone;
    }

    /**
     * @param TimeZone $default_timezone
     */
    public function setDefaultTimezone(TimeZone $default_timezone)
    {
        $this->default_timezone = $default_timezone;
    }

    /**
     * @return mixed
     */
    public function getDefaultPrivacy()
    {
        return $this->default_privacy;
    }

    /**
     * @param mixed $default_privacy
     */
    public function setDefaultPrivacy($default_privacy)
    {
        $this->default_privacy = $default_privacy;
    }

    /**
     * @return mixed
     */
    public function getSEOIndexFollow()
    {
        return $this->seo_index_follow;
    }

    /**
     * @param mixed $seo_index_follow
     */
    public function setSEOIndexFollow($seo_index_follow)
    {
        $this->seo_index_follow = $seo_index_follow;
    }

    /**
     * @return mixed
     */
    public function getListInDirectory()
    {
        return $this->list_in_directory;
    }

    /**
     * @param mixed $list_in_directory
     */
    public function setListInDirectory($list_in_directory)
    {
        $this->list_in_directory = $list_in_directory;
    }

    /**
     * @return mixed
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param mixed $locked
     */
    public function setLocked(bool $locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return mixed
     */
    public function getKeyPrivate()
    {
        return $this->keyPrivate;
    }

    /**
     * @return mixed
     */
    public function getKeyPublic()
    {
        return $this->keyPublic;
    }

    public function generateNewKey()
    {
        $openssl_options = [
            'digest_alg'       => 'sha512',
            'private_key_bits' => 4096,
            'encrypt_key'      => false,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        $result = openssl_pkey_new($openssl_options);

        if (empty($result)) {
            throw new \Exception('openssl_pkey_new failed');
        }

        openssl_pkey_export($result, $this->keyPrivate);

        $details = openssl_pkey_get_details($result);
        $this->keyPublic = $details["key"];
    }

    /**
     * @return bool
     */
    public function isManuallyApprovesFollowers(): bool
    {
        return $this->manuallyApprovesFollowers;
    }

    /**
     * @param bool $manuallyApprovesFollowers
     */
    public function setManuallyApprovesFollowers(bool $manuallyApprovesFollowers)
    {
        $this->manuallyApprovesFollowers = $manuallyApprovesFollowers;
    }
}
