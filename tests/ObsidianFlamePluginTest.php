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

    /**
     * The token map of the shipped payload, narrowed at RUNTIME rather than
     * by a docblock cast — the shape claim is what this suite checks, so
     * asserting it is the point, not overhead. The narrowing also keeps the
     * file honest under phpstan level 9 (tests are in the analysed corpus).
     *
     * @return array<string, string>
     */
    private function tokensOfFirstPayload(): array
    {
        $themes = $this->plugin->providedThemes();
        $this->assertNotEmpty($themes, 'Plugin must provide at least one theme.');

        $tokens = $themes[0]['tokens'] ?? null;
        $this->assertIsArray($tokens, 'The payload must carry a tokens map.');

        $narrowed = [];
        foreach ($tokens as $key => $value) {
            $this->assertIsString($key, 'A token name must be a string.');
            $this->assertIsString($value, "Token {$key} must have a string value.");
            $narrowed[$key] = $value;
        }

        return $narrowed;
    }

    #[Test]
    public function testProvidedThemesReturnsExpectedStructure(): void
    {
        $themes = $this->plugin->providedThemes();

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
        $forbidden = ['var(', 'url(', '/*', ';', '}', '{', '\\', "\n", "\t"];

        foreach ($this->tokensOfFirstPayload() as $token => $value) {
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
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function testOnDisableDoesNotThrow(): void
    {
        $this->plugin->onDisable();
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function testSubscribedEventsReturnsEmptyArray(): void
    {
        $events = $this->plugin->subscribedEvents();
        $this->assertEmpty($events);
    }
}
