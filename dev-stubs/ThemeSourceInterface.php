<?php

declare(strict_types=1);

namespace Phlix\Theming;

/**
 * ThemeSourceInterface for Phlix theme plugins.
 *
 * Stub for standalone CI when the host contract is unavailable.
 */
interface ThemeSourceInterface
{
    /**
     * Returns the unique identifier for this theme source.
     */
    public function themeSourceName(): string;

    /**
     * Returns the themes provided by this source.
     *
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     dark: bool,
     *     extends: string,
     *     tokens: array<string, string>
     * }>
     */
    public function providedThemes(): array;
}
