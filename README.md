# Domain Maker Bundle

`gibass/domain-maker` is a Symfony bundle that adds custom Maker commands to scaffold a Clean Architecture structure by domain.

The bundle generates classes from templates in `src/Resources/skeleton` and places them under your configured `src` directory.

## Features

- Create domain-oriented classes with interactive commands.
- Support create-or-choose workflows for existing classes.
- Handle dependencies between generated elements (for example `Repository` <-> `Entity` <-> `Gateway`).
- Auto-update route config when generating controllers (`config/routes.yaml`).

## Requirements

- PHP `>=8.2`
- `symfony/maker-bundle` `^1.61`

## Enable The Bundle

Register it in `config/bundles.php` (example for dev/test):

```php
<?php

return [
    // ...
    Gibass\DomainMakerBundle\DomainMakerBundle::class => ['dev' => true, 'test' => true],
];
```

## Configuration

Default configuration (from `Configuration.php`):

```yaml
domain_maker:
  parameters:
    root_namespace: App
    dir:
      src: '%kernel.project_dir%/src/'
      config: '%kernel.project_dir%/config/'
      test: '%kernel.project_dir%/tests/'
```

Optional override (example in `config/packages/domain_maker.yaml`):

```yaml
domain_maker:
  parameters:
    root_namespace: App
    dir:
      src: '%kernel.project_dir%/src/'
      config: '%kernel.project_dir%/config/'
```

## Available Commands

- `php bin/console maker:use-case`
- `php bin/console maker:entity`
- `php bin/console maker:gateway`
- `php bin/console maker:repository`
- `php bin/console maker:presenter`
- `php bin/console maker:controller`

## Generated Structure

For a domain like `User`, generated files are created under:

- `src/User/Domain/UseCase/*`
- `src/User/Domain/Model/Entity/*`
- `src/User/Domain/Gateway/*`
- `src/User/Infrastructure/Adapter/Repository/*`
- `src/User/UserInterface/Presenter/Json/*` or `src/User/UserInterface/Presenter/Html/*`
- `src/User/UserInterface/Controller/*`

## Interactive Flow

Each maker command first asks for the domain:

1. Create a new domain, or
2. Choose an existing one.

Depending on command type, it may also ask to:

- create a new dependency,
- choose an existing dependency,
- or skip optional dependencies.

Example:

- `maker:repository` can auto-create/select `Gateway` and select/create `Entity`.
- `maker:controller` can optionally include a `UseCase` and/or a `Presenter`.

## Templates

All generated code comes from these templates:

- `src/Resources/skeleton/use_case.tpl.php`
- `src/Resources/skeleton/entity/entity.tpl.php`
- `src/Resources/skeleton/gateway/gateway.tpl.php`
- `src/Resources/skeleton/repository/repository.tpl.php`
- `src/Resources/skeleton/presenter/*.tpl.php`
- `src/Resources/skeleton/controller/*.tpl.php`

## Notes

- Existing files are not overwritten: generation throws an error if target file already exists.
- Controller generation also writes route resource config into `config/routes.yaml`.
