<?php

declare(strict_types=1);

namespace Depa\SuluBlockHeroBundle\DependencyInjection;

use Depa\SuluBlockHelperBundle\DependencyInjection\AbstractBlockExtension;

class SuluBlockHeroExtension extends AbstractBlockExtension
{
    protected function getBundleName(): string
    {
        return 'SuluBlockHeroBundle';
    }

    protected function getPackageName(): string
    {
        return 'depa/sulu-block-hero';
    }

    protected function getMetadataParameterName(): string
    {
        return 'sulu_block_hero.bundle_metadata';
    }

    protected function getSuluAdminTemplateKey(): string
    {
        return 'sulu_block_hero';
    }
}
