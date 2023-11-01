<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "detailUser",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getUsers")
 * )
 *
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "deleteUser",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getDetailUser", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getUsers', 'getDetailUser'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers', 'getDetailUser'])]
    #[Assert\NotBlank(message: "Le prénom de l'utilisateur est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: 'Le prénom doit faire au moins {{ limit }} caractères', maxMessage: 'Le prénom ne peut pas faire plus de {{ limit }} caractères')]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers', 'getDetailUser'])]
    #[Assert\NotBlank(message: "Le nom de l'utilisateur est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: 'Le nom doit faire au moins {{ limit }} caractères', maxMessage: 'Le nom ne peut pas faire plus de {{ limit }} caractères')]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers', 'getDetailUser'])]
    #[Assert\NotBlank(message: "L'email de l'utilisateur est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "L'email doit faire au moins {{ limit }} caractères", maxMessage: "L'email ne peut pas faire plus de {{ limit }} caractères")]
    #[Assert\Email(message: "L'email saisi n'est pas valide")]
    #[Assert\Unique(message: 'Cet email existe déjà')]
    private ?string $email = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getDetailUser'])]
    private ?Client $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }
}
