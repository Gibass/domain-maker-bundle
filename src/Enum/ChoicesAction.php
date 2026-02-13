<?php

namespace Gibass\DomainMakerBundle\Enum;

enum ChoicesAction: string
{
    case no = 'No';
    case create = 'Create';
    case choose = 'Choose';

    public const REQUIRED = [self::create->value, self::choose->value];
    public const OPTIONAL = [self::no->value, self::create->value, self::choose->value];

    public static function getChoicesAction(DependencyType $choiceType): array
    {
        return match ($choiceType) {
            DependencyType::optional => self::OPTIONAL,
            DependencyType::required => self::REQUIRED,
        };
    }
}
