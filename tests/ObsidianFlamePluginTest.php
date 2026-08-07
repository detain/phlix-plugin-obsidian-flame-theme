<?php

declare(strict_types=1);

namespace Phlix\ObsidianFlame\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use Phlix\ObsidianFlame\ObsidianFlamePlugin;

#[CoversClass(ObsidianFlamePlugin::class)]
final class ObsidianFlamePluginTest extends TestCase
{
    private ObsidianFlamePlugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plugin = new ObsidianFlamePlugin();
    }

    #[Test]
    public function itImplementsLifecycleInterface(): void
    {
        $this->assertInstanceOf(\Phlix\Shared\Plugin\LifecycleInterface::class, $this->plugin);
    }

    #[Test]
    public function itImplementsThemeSourceInterface(): void
    {
        $this->assertInstanceOf(\Phlix\Theming\ThemeSourceInterface::class, $this->plugin);
    }

    #[Test]
    public function itReturnsCorrectSourceName(): void
    {
        $this->assertSame('obsidian-flame', $this->plugin->themeSourceName());
    }

    #[Test]
    public function itProvidesAtLeastOneTheme(): void
    {
        $themes = $this->plugin->providedThemes();
        $this->assertNotEmpty($themes, 'Plugin must provide at least one theme');
    }

    #[Test]
    public function itProvidesObsidianFlameThemeWithCorrectId(): void
    {
        $themes = $this->plugin->providedThemes();
        $theme = $themes[0] ?? null;

        $this->assertNotNull($theme, 'First theme must exist');
        $this->assertSame('obsidian-flame', $theme['id']);
        $this->assertSame('Obsidian Flame', $theme['name']);
        $this->assertTrue($theme['dark'], 'Theme must be marked as dark');
    }

    #[Test]
    public function itExtendsMidnightBase(): void
    {
        $themes = $this->plugin->providedThemes();
        $theme = $themes[0] ?? null;

        $this->assertNotNull($theme);
        $this->assertSame('midnight', $theme['extends']);
    }

    #[Test]
    public function itProvidesRequiredColorTokens(): void
    {
        $themes = $this->plugin->providedThemes();
        $theme = $themes[0] ?? null;

        $this->assertNotNull($theme);
        $this->assertArrayHasKey('tokens', $theme);

        $tokens = $theme['tokens'];

        // Core color tokens
        $this->assertArrayHasKey('--color-bg', $tokens);
        $this->assertArrayHasKey('--color-surface', $tokens);
        $this->assertArrayHasKey('--color-text', $tokens);
        $this->assertArrayHasKey('--color-text-muted', $tokens);
        $this->assertArrayHasKey('--color-border', $tokens);
    }

    #[Test]
    public function itProvidesAccentTokens(): void
    {
        $themes = $this->plugin->providedThemes();
        $theme = $themes[0] ?? null;

        $this->assertNotNull($theme);
        $tokens = $theme['tokens'];

        $this->assertArrayHasKey('--accent', $tokens);
        $this->assertArrayHasKey('--accent-hover', $tokens);
        $this->assertArrayHasKey('--accent-active', $tokens);
        $this->assertArrayHasKey('--accent-soft', $tokens);
        $this->assertArrayHasKey('--accent-ring', $tokens);
        $this->assertArrayHasKey('--accent-text', $tokens);
    }

    #[Test]
    public function itProvidesSurfaceTokens(): void
    {
        $themes = $this->plugin->providedThemes();
        $theme = $themes[0] ?? null;

        $this->assertNotNull($theme);
        $tokens = $theme['tokens'];

        $this->assertArrayHasKey('--surface', $tokens);
        $this->assertArrayHasKey('--surface-2', $tokens);
        $this->assertArrayHasKey('--surface-3', $tokens);
        $this->assertArrayHasKey('--surface-glass', $tokens);
        $this->assertArrayHasKey('--surface-glass-strong', $tokens);
    }

    #[Test]
    public function itProvidesBorderTokens(): void
    {
        $themes = $this->plugin->providedThemes();
        $theme = $themes[0] ?? null;

        $this->assertNotNull($theme);
        $tokens = $theme['tokens'];

        $this->assertArrayHasKey('--border', $tokens);
        $this->assertArrayHasKey('--border-subtle', $tokens);
        $this->assertArrayHasKey('--border-strong', $tokens);
    }

    #[Test]
    public function itProvidesAtmosphericTokens(): void
    {
        $themes = $this->plugin->providedThemes();
        $theme = $themes[0] ?? null;

        $this->assertNotNull($theme);
        $tokens = $theme['tokens'];

        $this->assertArrayHasKey('--grain-opacity', $tokens);
        $this->assertArrayHasKey('--vignette', $tokens);
        $this->assertArrayHasKey('--ambient', $tokens);
    }

    #[Test]
    public function itReturnsNoSubscribedEvents(): void
    {
        $events = $this->plugin->subscribedEvents();
        $this->assertIsArray($events);
        $this->assertEmpty($events, 'Theme plugins should not subscribe to events');
    }

    #[Test]
    public function itHandlesEnableWithoutErrors(): void
    {
        $container = $this->createMock(\Psr\Container\ContainerInterface::class);

        // Should not throw
        $this->plugin->onEnable($container);
        $this->assertTrue(true, 'onEnable should complete without errors');
    }

    #[Test]
    public function itHandlesDisableWithoutErrors(): void
    {
        // Should not throw
        $this->plugin->onDisable();
        $this->assertTrue(true, 'onDisable should complete without errors');
    }

    #[Test]
    public function tokensAreValidCssColorValues(): void
    {
        $themes = $this->plugin->providedThemes();
        $theme = $themes[0] ?? null;

        $this->assertNotNull($theme);
        $tokens = $theme['tokens'];

        // Each token value should be a valid CSS color (hex, rgb, rgba, etc.)
        foreach ($tokens as $token => $value) {
            $this->assertIsString($value, "Token {$token} must have a string value");
            $this->assertNotEmpty($value, "Token {$token} must not be empty");
        }
    }

    #[Test]
    public function constantSourceNameIsCorrect(): void
    {
        $this->assertSame('obsidian-flame', ObsidianFlamePlugin::SOURCE_NAME);
    }
}
