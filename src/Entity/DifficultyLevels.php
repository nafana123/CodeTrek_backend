<?php

namespace App\Entity;

use App\Repository\DifficultyLevelsRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: DifficultyLevelsRepository::class)]
class DifficultyLevels
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $level;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): self
    {
        $this->level = $level;
        return $this;
    }
}