<?php

namespace App\Entity;

use App\Repository\ReplyToMessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReplyToMessageRepository::class)]
class ReplyToMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\ManyToOne(targetEntity: Discussion::class)]
    #[ORM\JoinColumn(name: 'discussion_id', referencedColumnName: 'id')]
    private ?Discussion $discussion = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\Column(name: 'reply_to', type: 'text')]
    private string $replyTo;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $data = null;
    #[ORM\Column(type: 'boolean')]
    private bool $isEdit = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDiscussion(): ?Discussion
    {
        return $this->discussion;
    }

    public function setDiscussion(?Discussion $discussion): self
    {
        $this->discussion = $discussion;

        return $this;
    }

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    public function setReplyTo(string $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function getData(): ?\DateTimeInterface
    {
        return $this->data;
    }

    public function setData(\DateTimeInterface $data): self
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
