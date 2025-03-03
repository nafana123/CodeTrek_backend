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

    #[ORM\Column(type: 'json')]
    private array $input;

    #[ORM\Column(type: 'string')]
    private string $expectedOutput;

    #[ORM\Column(type: 'string')]
    private string $executionTemplate;

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

    public function getInput(): array
    {
        return $this->input;
    }

    public function setInput(array $input): self
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

    public function getExecutionTemplate(): string
    {
        return $this->executionTemplate;
    }

    public function setExecutionTemplate(string $executionTemplate): self
    {
        $this->executionTemplate = $executionTemplate;

        return $this;
    }

}
