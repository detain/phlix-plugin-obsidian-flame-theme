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
    /**
     * Theme ids the host reserves for its own built-ins. A plugin claiming
     * one would silently replace a shipped theme, and the host's validator
     * rejects it — mirrored here so the mistake is caught before install.
     *
     * @var list<string>
     */
    private const RESERVED_IDS = ['nocturne', 'daylight', 'midnight'];

    /**
     * The ratified legacy alias pairs: each shipped `--color-*` alias and
     * the modern token it must mirror. Flattening loses CSS's var()
     * following (the server BuiltInThemes law), so a palette edit moves both
     * halves together — the mirror test fails the moment one half drifts.
     *
     * @var array<string, string> alias token => semantic token
     */
    private const LEGACY_ALIAS_PAIRS = [
        '--color-bg' => '--bg',
        '--color-surface' => '--surface',
        '--color-text' => '--text',
        '--color-text-muted' => '--text-muted',
        '--color-border' => '--border',
    ];

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

    /**
     * The manifest as it ships, through PHP's own JSON parser.
     *
     * @return array<string, mixed>
     */
    private function manifest(): array
    {
        $json = file_get_contents(__DIR__ . '/../plugin.json');
        $this->assertIsString($json, 'plugin.json must be readable.');

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    #[Test]
    public function testTheManifestDeclaresAUiThemePointingAtTheShippedClass(): void
    {
        $manifest = $this->manifest();

        $this->assertSame('phlix-plugin-obsidian-flame-theme', $manifest['name']);
        $this->assertSame('ui-theme', $manifest['type']);
        $this->assertSame(ObsidianFlamePlugin::class, $manifest['entry']);
        $this->assertSame([], $manifest['events']);
        $this->assertNull($manifest['signature']);
        // The theme-source capability arm landed in 0.44.0; declaring anything
        // lower would let the plugin install onto a server that cannot register
        // it, where it would appear enabled and contribute nothing.
        $this->assertSame('0.44.0', $manifest['phlix_min_server_version']);
    }

    #[Test]
    public function testTheManifestEntryClassActuallyExistsAndIsAutoloadable(): void
    {
        $manifest = $this->manifest();
        $this->assertIsString($manifest['entry']);
        /** @var class-string $entry */
        $entry = $manifest['entry'];

        // Resolved through the composer autoloader, i.e. exactly how the host
        // finds it after `composer install` inside var/plugins/<name>/.
        $this->assertTrue(class_exists($entry), "Manifest entry class {$entry} must be autoloadable.");
    }

    #[Test]
    public function testThemeIdsAreUniqueSlugsAndNeverClaimABuiltIn(): void
    {
        $ids = [];
        foreach ($this->plugin->providedThemes() as $payload) {
            $id = $payload['id'] ?? null;
            $this->assertIsString($id, 'Every payload must carry a string id.');
            $ids[] = $id;
        }

        $this->assertSame($ids, array_values(array_unique($ids)));
        foreach ($ids as $id) {
            // The host validates the id with this shape and rejects the
            // built-in names outright; either mistake fails enable on the
            // host while local CI would stay green without this pin.
            $this->assertMatchesRegularExpression('/^[a-z0-9](?:[a-z0-9-]{0,62}[a-z0-9])?\z/', $id);
            $this->assertNotContains($id, self::RESERVED_IDS, "Theme id {$id} is reserved by the host.");
        }
    }

    #[Test]
    public function testEveryPayloadUsesOnlyTheFiveKeysTheHostAccepts(): void
    {
        foreach ($this->plugin->providedThemes() as $payload) {
            $this->assertSame(
                ['id', 'name', 'dark', 'extends', 'tokens'],
                array_keys($payload),
                'The host rejects an unknown payload key outright.'
            );
            $this->assertIsBool($payload['dark'], '"dark" is checked with is_bool(); 1 or "true" is refused.');
            $this->assertIsString($payload['name']);
            $this->assertIsArray($payload['tokens']);
        }
    }

    #[Test]
    public function testLegacyColorAliasesMirrorTheirSemanticCounterparts(): void
    {
        $tokens = $this->tokensOfFirstPayload();

        foreach (self::LEGACY_ALIAS_PAIRS as $alias => $semantic) {
            $this->assertArrayHasKey($alias, $tokens, "Legacy alias {$alias} must ship.");
            $this->assertArrayHasKey($semantic, $tokens, "Semantic token {$semantic} must ship.");
            $this->assertSame(
                $tokens[$semantic],
                $tokens[$alias],
                "Legacy alias {$alias} drifted from {$semantic}: flattening loses var() following, "
                    . 'so a palette edit must move both halves of the pair together.'
            );
        }
    }

    #[Test]
    public function testTheShippedAliasSetIsExactlyTheRatifiedFive(): void
    {
        $aliases = array_values(array_filter(
            array_keys($this->tokensOfFirstPayload()),
            static fn (string $token): bool => str_starts_with($token, '--color-')
        ));

        // The 13 zero-consumer aliases stay omitted on purpose — the reviewed
        // family canon (sample DEAD_ALIASES) forbids re-inflating the payload,
        // and W-min139 -07 was DISPROVED on exactly that canon. A sixth
        // --color-* key is only legitimate together with a measured consumer.
        $this->assertSame(array_keys(self::LEGACY_ALIAS_PAIRS), $aliases);
    }
}
