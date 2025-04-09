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

    #[ORM\ManyToOne(targetEntity: DifficultyLevels::class)]
    #[ORM\JoinColumn(name: 'difficulty_id', referencedColumnName: 'id')]
    private DifficultyLevels $difficulty;

    #[ORM\Column(length: 255)]
    private ?string $input = null;

    #[ORM\Column(length: 255)]
    private ?string $output = null;

    #[ORM\Column( length: 255)]
    private ?string $answer;

    #[Orm\Column(name: 'test_case', type: 'text')]
    private ?string $testCase;

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

    public function getDifficulty(): DifficultyLevels
    {
        return $this->difficulty;
    }

    public function setDifficulty(DifficultyLevels $difficulty): self
    {
        $this->difficulty = $difficulty;
        return $this;
    }

    public function getInput(): ?string
    {
        return $this->input;
    }

    public function setInput(string $input): static
    {
        $this->input = $input;

        return $this;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(string $output): static
    {
        $this->output = $output;

        return $this;
    }
    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): static
    {
        $this->answer = $answer;

        return $this;
    }

    public function getTestCase(): mixed
    {
        $decoded = json_decode($this->testCase, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->testCase;
    }

    public function setTestCase(string $testCase): self
    {
        $this->testCase = $testCase;

        return $this;
    }
}
