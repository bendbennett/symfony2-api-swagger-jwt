<?php

namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MongoDB\Document(repositoryClass="App\Repository\UserRepository", collection="users")
 * @OA\Schema(schema="User", required={"email"})
 */
class User implements UserInterface
{
    /**
     * @MongoDB\Id
     * @JMS\Type("string")
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     * @JMS\Type("string")
     * @Assert\Email()
     * @OA\Property(type="string", default="email@somewhere.com")
     */
    protected $email;

    /**
     * @MongoDB\Field(type="string")
     * @JMS\Type("string")
     * @JMS\SerializedName("firstName")
     * @OA\Property(type="string")
     */
    protected $firstName;

    /**
     * @MongoDB\Field(type="string")
     * @JMS\Type("string")
     * @JMS\SerializedName("lastName")
     * @OA\Property(type="string")
     */
    protected $lastName;

    /**
     * @MongoDB\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Exclude
     * @OA\Property(type="string")
     */
    protected $password;

    /**
     * @MongoDB\EmbedMany(targetDocument="UserCompany")
     * @JMS\Type("ArrayCollection<App\Document\UserCompany>")
     * @JMS\SerializedName("userCompanies")
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/UserCompany"))
     */
    private $userCompanies = array();

    /**
     *
     * This is specifically for security, there are no roles on a user object, roles are held at the company level.
     * These are roles extracted from the JWT and added to the roles field on the User object of the currently
     * logged in user and used to ensure that secured endpoints are not accessed without user having
     * appropriate role(s).
     *
     * DoctrineMongoListener is used to extract roles from JWT and call setRoles() following postLoad of Document.
     *
     * User::getRoles() is called when @ Security("is_granted('ROLE_xxx')") annotation is evaluated.
     *
     * @JMS\Exclude
     */
    protected $roles;

    /**
     * This is required so that User can hold an array of UserCompany objects
     */
    public function __construct()
    {
        $this->userCompanies = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return self
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get firstName
     *
     * @return string $firstName
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return self
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Get lastName
     *
     * @return string $lastName
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Add userCompany
     *
     * @param \App\Document\UserCompany $userCompany
     */
    public function addUserCompany(UserCompany $userCompany): void
    {
        $this->userCompanies[] = $userCompany;
    }

    /**
     * Remove userCompany
     *
     * @param \App\Document\UserCompany $userCompany
     */
    public function removeUserCompany(UserCompany $userCompany): void
    {
        $this->userCompanies->removeElement($userCompany);
    }

    /**
     * Get userCompanies
     *
     * @return \Doctrine\Common\Collections\Collection $userCompanies
     */
    public function getUserCompanies(): Collection
    {
        return $this->userCompanies;
    }

    public function getUserCompanyById(string $companyId)
    {
        $userCompany = $this->getUserCompanies()->filter(
            function ($userCompany) use ($companyId) {
                return $userCompany->getCompanyId() === $companyId;
            }
        )->first();

        return $userCompany;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get password
     *
     * @return string $password
     */
    public function getPassword(): string
    {
        return $this->password;
    }


    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * This is specifically for security, there are no roles on a user object, roles are held at the company level.
     * These are roles extracted from the JWT to ensure that secured endpoints are not accessed without user having
     * appropriate role(s). See $roles property above.
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getSalt(): void
    {
        return;
    }

    public function getUsername(): string
    {
        return $this->getEmail();
    }

    public function eraseCredentials(): bool
    {
        return true;
    }
}
