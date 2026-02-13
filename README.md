# Domain Maker Bundle
`gibass/domain-maker-bundle` is a Symfony bundle that adds custom Maker commands to scaffold a Clean Architecture structure by domain.

The bundle generates classes and places them under your configured `src` directory.

## Features

- Create domain-oriented classes with interactive commands.
- Support create-or-choose workflows for existing classes.
- Handle dependencies between generated elements (for example `Repository` <-> `Entity` <-> `Gateway`).
- Auto-update route config when generating controllers (`config/routes.yaml`).

## Requirements

- PHP `>=8.2`
- `symfony/maker-bundle` `^1.61`

## Folder Structure
For example, we need to create a Blog domain, with this bundle we can generate a folder structure and files
like this :
```scala
|-- src // source folder
|   `-- Blog // A Specific domain
|       |-- Domain
|       |   |-- Gateway
|       |   |   |-- PostGateway.php
|       |   |-- Model
|       |   |   |-- Entity
|       |   |   |   |-- Post.php
|       |   |-- UseCase
|       |   |   |-- CreatePost.php
|       |-- Infrastructure
|       |   |-- Adapter
|       |   |   |-- Repository
|       |   |       |-- PostRepository.php
|       |-- UserInterface
|       |   |-- Controller
|       |   |   |-- PostController.php
|       |   |-- Presenter
|       |   |   |-- Html
|       |   |   |   |-- PostPresenter.php
```

## Installation
1. Installing the bundle with composer :
```shell
composer require --dev gibass/domain-maker-bundle 
```

2. Add this line in `config/bundles.php`:
```php
<?php

return [
    // ...
    Gibass\DomainMakerBundle\DomainMakerBundle::class => ['dev' => true, 'test' => true],
];
```

## Configuration

Default configuration:

```yaml
domain_maker:
  parameters:
    root_namespace: App # Your project root namespace 
    dir:
      src: '%kernel.project_dir%/src/' # The source folder
      config: '%kernel.project_dir%/config/' # The symfony config folder
```

You can override the default configuration with your own values.
Create a file `domain_maker.yaml` under `config/packages`
Example : 
```yaml
domain_maker:
  parameters:
    root_namespace: MyProject # Set Root Namespace
```

## Available Commands

- `php bin/console maker:use-case`
- `php bin/console maker:entity`
- `php bin/console maker:gateway`
- `php bin/console maker:repository`
- `php bin/console maker:presenter`
- `php bin/console maker:controller`

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

## Notes

- Existing files are not overwritten: generation throws an error if target file already exists.
- Controller generation also writes route resource config into `config/routes.yaml`.
