<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateTaskRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $title,
        public ?string $description = null,
        public ?TaskStatus $status = null,
        public ?TaskPriority $priority = null,
        #[Assert\NotNull]
        #[Assert\Positive]
        public int $projectId = 0,
        #[Assert\Positive]
        public ?int $categoryId = null,
        #[Assert\Date]
        public ?string $dueDate = null,
    ) {
    }
}
