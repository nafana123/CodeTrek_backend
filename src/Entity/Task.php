<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $task_id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: 'language_id', referencedColumnName: 'id')]
    private Language $language;

    #[ORM\ManyToOne(targetEntity: DifficultyLevels::class)]
    #[ORM\JoinColumn(name: 'difficulty_id', referencedColumnName: 'id')]
    private DifficultyLevels $difficulty;

    public function getTaskId(): ?int
    {
        return $this->task_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function getDifficulty(): DifficultyLevels
    {
        return $this->difficulty;
    }

    public function setDifficulty(DifficultyLevels $difficulty): self
    {
        $this->difficulty = $difficulty;
        return $this;
    }
}
