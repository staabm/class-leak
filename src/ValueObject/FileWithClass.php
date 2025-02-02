<?php

declare(strict_types=1);

namespace TomasVotruba\ClassLeak\ValueObject;

use JsonSerializable;
use Nette\Utils\FileSystem;
use TomasVotruba\ClassLeak\FileSystem\StaticRelativeFilePathHelper;

final readonly class FileWithClass implements JsonSerializable
{
    /**
     * @param string[] $attributes
     */
    public function __construct(
        private string $filePath,
        private string $className,
        private bool $hasParentClassOrInterface,
        private array $attributes,
    ) {
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getFilePath(): string
    {
        return StaticRelativeFilePathHelper::resolveFromCwd($this->filePath);
    }

    public function hasParentClassOrInterface(): bool
    {
        return $this->hasParentClassOrInterface;
    }

    /**
     * @return string[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array{file_path: string, class: string, attributes: string[]}
     */
    public function jsonSerialize(): array
    {
        return [
            'file_path' => $this->filePath,
            'class' => $this->className,
            'attributes' => $this->attributes,
        ];
    }

    /**
     * Is serialized, could be hidden inside json output magic
     */
    public function isSerialized(): bool
    {
        $fileContents = FileSystem::read($this->filePath);
        return str_contains($fileContents, '@Serializer');
    }

    /**
     * Dummy check for Doctrine ORM/ODM entity
     */
    public function isEntity(): bool
    {
        $fileContents = FileSystem::read($this->filePath);

        if (str_contains($fileContents, 'Doctrine\ODM\MongoDB\Mapping\Annotations')) {
            return true;
        }

        if (str_contains($fileContents, 'Doctrine\ORM\Annotations')) {
            return true;
        }

        if (str_contains($fileContents, '@ORM\Entity')) {
            return true;
        }

        if (str_contains($fileContents, '@Entity')) {
            return true;
        }

        if (str_contains($fileContents, '@ODM\\Document')) {
            return true;
        }

        return str_contains($fileContents, '@Document');
    }

    public function isTrait(): bool
    {
        return trait_exists($this->className);
    }
}
