<?php

namespace App\Entity;

use App\Library;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user_account")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=500, unique=false, nullable=false)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=500, unique=true, nullable=false)
     */
    private $emailCanonical;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(name="created_at", type="integer", nullable=false)
     */
    private $created;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : false})
     */
    private $locked = false;


    /**
     * @ORM\Column(name="limit_number_of_accounts_manage", type="integer", nullable=false, options={"default" : 100})
     */
    private $limitNumberOfAccountsManage = 100;

    /**
     * @ORM\Column(name="limit_number_of_api_access_tokens", type="integer", nullable=false, options={"default" : 100})
     */
    private $limitNumberOfAPIAccessTokens = 100;


    /**
     * @ORM\OneToMany(targetEntity="UserManageAccount", mappedBy="user")
     */
    private $managesAccount;


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->emailCanonical = Library::makeEmailCanonical($email);
    }


    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole($role)
    {
        $this->roles[] = $role;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }





    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->created = time();
    }

    /**
     * @return bool
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
    public function getLimitNumberOfAccountsManage()
    {
        return $this->limitNumberOfAccountsManage;
    }

    /**
     * @return mixed
     */
    public function getLimitNumberOfAPIAccessTokens()
    {
        return $this->limitNumberOfAPIAccessTokens;
    }


    public function isSysAdmin(): bool
    {
        return in_array('ROLE_SYSADMIN', $this->roles);
    }
}
