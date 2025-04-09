<?php

namespace App\Entity;

use App\Repository\TestCaseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestCaseRepository::class)]
class TestCase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(referencedColumnName: "task_id")]
    private Task $task;

    #[ORM\Column(type: 'text')]
    private ?string $input;

    #[ORM\Column(type: 'string')]
    private string $expectedOutput;

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

    public function getInput(): mixed
    {
        $decoded = json_decode($this->input, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->input;
    }


    public function setInput(string $input): self
    {
        $this->input = $input;

        return $this;
    }

    public function getExpectedOutput(): string
    {
        return $this->expectedOutput;
    }

    public function setExpectedOutput(string $expectedOutput): self
    {
        $this->expectedOutput = $expectedOutput;

        return $this;
    }
}
