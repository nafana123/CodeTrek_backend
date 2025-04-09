<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskLanguage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(name: "task_id", referencedColumnName: "task_id")]
    private Task $task;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: "language_id", referencedColumnName: "id")]
    private Language $language;

    #[ORM\Column(type: 'text')]
    private $codeTemplates;

    #[ORM\Column(type: 'text')]
    private $executionTemplate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task): self
    {
        $this->task = $task;
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

    public function getCodeTemplates(): ?string
    {
        return $this->codeTemplates;
    }

    public function setCodeTemplates(?string $codeTemplates): self
    {
        $this->codeTemplates = $codeTemplates;

        return $this;
    }


    public function getExecutionTemplate()
    {
        return $this->executionTemplate;
    }

    public function setExecutionTemplate($executionTemplate): self
    {
        $this->executionTemplate = $executionTemplate;

        return $this;
    }
}
