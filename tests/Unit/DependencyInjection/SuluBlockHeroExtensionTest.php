<?php

declare(strict_types=1);

namespace Depa\SuluBlockHeroBundle\Tests\Unit\DependencyInjection;

use Depa\SuluBlockHeroBundle\DependencyInjection\SuluBlockHeroExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SuluBlockHeroExtensionTest extends TestCase
{
    private ContainerBuilder $container;
    private SuluBlockHeroExtension $extension;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new SuluBlockHeroExtension();
    }

    public function testLoadSetsBundleMetadataParameter(): void
    {
        $this->extension->load([], $this->container);
        self::assertTrue($this->container->hasParameter('sulu_block_hero.bundle_metadata'));
    }

    public function testBundleMetadataHasRequiredKeys(): void
    {
        $this->extension->load([], $this->container);
        $meta = $this->container->getParameter('sulu_block_hero.bundle_metadata');
        self::assertIsArray($meta);
        self::assertArrayHasKey('bundle', $meta);
        self::assertArrayHasKey('package', $meta);
        self::assertArrayHasKey('blocks', $meta);
        self::assertArrayHasKey('children', $meta);
    }

    public function testBundleMetadataContainsCorrectBundleName(): void
    {
        $this->extension->load([], $this->container);
        $meta = $this->container->getParameter('sulu_block_hero.bundle_metadata');
        self::assertIsArray($meta);
        self::assertSame('SuluBlockHeroBundle', $meta['bundle']);
    }

    public function testBundleMetadataContainsCorrectPackageName(): void
    {
        $this->extension->load([], $this->container);
        $meta = $this->container->getParameter('sulu_block_hero.bundle_metadata');
        self::assertIsArray($meta);
        self::assertSame('depa/sulu-block-hero', $meta['package']);
    }

    public function testBundleMetadataContainsAtLeastOneBlock(): void
    {
        $this->extension->load([], $this->container);
        $meta = $this->container->getParameter('sulu_block_hero.bundle_metadata');
        self::assertIsArray($meta);
        self::assertNotEmpty($meta['blocks']);
    }

    public function testBlocksAreSortedAndUnique(): void
    {
        $this->extension->load([], $this->container);
        $meta = $this->container->getParameter('sulu_block_hero.bundle_metadata');
        self::assertIsArray($meta);
        $blocks = $meta['blocks'];
        $sorted = $blocks;
        sort($sorted);
        self::assertSame($sorted, $blocks, 'blocks must be sorted');
        self::assertSame(array_unique($blocks), $blocks, 'blocks must be unique');
    }

    public function testKnownHeroBlocksArePresent(): void
    {
        $this->extension->load([], $this->container);
        $meta = $this->container->getParameter('sulu_block_hero.bundle_metadata');
        self::assertIsArray($meta);

        foreach (['block--hero-call2action', 'block--hero-content', 'block--hero-image', 'block--hero-promo-image'] as $expected) {
            self::assertContains($expected, $meta['blocks']);
        }
    }

    public function testChildrenValuesAreArraysOfStrings(): void
    {
        $this->extension->load([], $this->container);
        $meta = $this->container->getParameter('sulu_block_hero.bundle_metadata');
        self::assertIsArray($meta);

        foreach ($meta['children'] as $parent => $kids) {
            self::assertIsArray($kids, "Children of '{$parent}' must be an array");
            foreach ($kids as $child) {
                self::assertIsString($child);
            }
        }
    }

    public function testHeroContentHasChildrenFromXml(): void
    {
        $this->extension->load([], $this->container);
        $meta = $this->container->getParameter('sulu_block_hero.bundle_metadata');
        self::assertIsArray($meta);

        self::assertArrayHasKey('block--hero-content', $meta['children']);
        self::assertNotEmpty($meta['children']['block--hero-content']);
    }
}
