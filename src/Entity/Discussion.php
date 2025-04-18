<?php

namespace App\Entity;

use App\Repository\DiscussionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscussionRepository::class)]
class Discussion
{
    public function __construct()
    {
        $this->replies = new ArrayCollection();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(name: "task_id", referencedColumnName: "task_id")]
    private Task $task;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\Column(type: 'text', name: 'message')]
    private string $message;
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $data = null;

    #[ORM\OneToMany(targetEntity: ReplyToMessage::class, mappedBy: "discussion", cascade: ["remove"], orphanRemoval: true)]
    private Collection $replies;

    #[ORM\Column(type: 'boolean')]
    private bool $isEdit = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }
    public function setTask(?Task $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getMessage(): ?string{
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getData(): ?\DateTimeInterface
    {
        return $this->data;
    }

    public function setData(?\DateTimeInterface $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getIsEdit(): bool
    {
        return $this->isEdit;
    }

    public function setIsEdit(bool $isEdit): self
    {
        $this->isEdit = $isEdit;

        return $this;
    }
}
