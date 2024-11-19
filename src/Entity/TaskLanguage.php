<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TaskLanguage
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(name: "task_id", referencedColumnName: "task_id")]
    private Task $task;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: "language_id", referencedColumnName: "id")]
    private Language $language;

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
}
