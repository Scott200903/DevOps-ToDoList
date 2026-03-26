<?php
class Task
{
    private int $id = 0;
    private string $category = '';
    private string $description = '';
    private int $complete = 0;

    public function getId(): int { return $this->id; }
    public function getCategory(): string { return $this->category; }
    public function getDescription(): string { return $this->description; }
    public function getComplete(): int { return $this->complete; }

    public function setId(int $id): void { $this->id = $id; }
    public function setCategory(string $category): void { $this->category = $category; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setComplete(int $complete): void { $this->complete = $complete; }
}
