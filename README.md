# sulu-block-hero

Hero block collection for Sulu CMS — 4 full-width hero and stage blocks for page headers and promotional areas.

## Included Blocks

| Block | Description |
|---|---|
| `block--hero-content` | Hero with text content and optional CTA |
| `block--hero-call2action` | Hero focused on a call-to-action |
| `block--hero-image` | Full-width hero image |
| `block--hero-promo-image` | Promotional hero with image and overlay |

## Requirements

- PHP 8.2+
- Symfony 7.0+
- Sulu CMS 3.0+
- `depa/sulu-block-helper`
- `depa/sulu-block-content` (referenced block types)

## Installation

```bash
composer require depa/sulu-block-hero
```

Register in `config/bundles.php`:

```php
Depa\SuluBlockHelperBundle\SuluBlockHelperBundle::class => ['all' => true],
Depa\SuluBlockHeroBundle\SuluBlockHeroBundle::class     => ['all' => true],
```

## License

Proprietary — Copyright (c) depa Berlin GmbH & Co. KG. All rights reserved.
See [LICENSE](LICENSE) for details.
