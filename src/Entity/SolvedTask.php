<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SolvedTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private string $code;

    #[ORM\ManyToOne(targetEntity: TaskLanguage::class)]
    #[ORM\JoinColumn(name: 'task_language_id', referencedColumnName: 'id')]
    private TaskLanguage $taskLanguage;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getTaskLanguage(): TaskLanguage
    {
        return $this->taskLanguage;
    }

    public function setTaskLanguage(TaskLanguage $taskLanguage): self
    {
        $this->taskLanguage = $taskLanguage;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
