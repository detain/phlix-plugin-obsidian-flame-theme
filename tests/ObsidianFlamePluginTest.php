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
    public function testImplementsLifecycleInterface(): void
    {
        $this->assertInstanceOf(\Phlix\Shared\Plugin\LifecycleInterface::class, $this->plugin);
    }

    #[Test]
    public function testImplementsThemeSourceInterface(): void
    {
        $this->assertInstanceOf(\Phlix\Theming\ThemeSourceInterface::class, $this->plugin);
    }

    #[Test]
    public function testThemeSourceNameReturnsCorrectValue(): void
    {
        $this->assertSame('obsidian-flame', $this->plugin->themeSourceName());
    }

    #[Test]
    public function testProvidedThemesReturnsExpectedStructure(): void
    {
        $themes = $this->plugin->providedThemes();

        $this->assertIsArray($themes);
        $this->assertNotEmpty($themes, 'Plugin must provide at least one theme');

        $theme = $themes[0];
        $this->assertSame('obsidian-flame', $theme['id']);
        $this->assertSame('Obsidian Flame', $theme['name']);
        $this->assertTrue($theme['dark']);
        $this->assertSame('midnight', $theme['extends']);
        $this->assertArrayHasKey('tokens', $theme);
    }

    #[Test]
    public function testAllTokensAreValidCssValues(): void
    {
        $themes = $this->plugin->providedThemes();
        $theme = $themes[0];
        $tokens = $theme['tokens'];

        $forbidden = ['var(', 'url(', '/*', ';', '}', '{', '\\', "\n", "\t"];

        foreach ($tokens as $token => $value) {
            $this->assertIsString($value, "Token {$token} must have a string value");
            $this->assertNotEmpty($value, "Token {$token} must not be empty");

            foreach ($forbidden as $f) {
                $this->assertStringNotContainsString(
                    $f,
                    $value,
                    "Token {$token} contains forbidden CSS construct \"{$f}\""
                );
            }
        }
    }

    #[Test]
    public function testOnEnableDoesNotThrow(): void
    {
        $container = $this->createMock(\Psr\Container\ContainerInterface::class);

        $this->plugin->onEnable($container);
        $this->assertTrue(true);
    }

    #[Test]
    public function testOnDisableDoesNotThrow(): void
    {
        $this->plugin->onDisable();
        $this->assertTrue(true);
    }

    #[Test]
    public function testSubscribedEventsReturnsEmptyArray(): void
    {
        $events = $this->plugin->subscribedEvents();
        $this->assertIsArray($events);
        $this->assertEmpty($events);
    }
}
