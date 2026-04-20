<?php

namespace Webkul\Survey\Enums;

enum SurveyStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';

    public static function options(): array
    {
        return [
            self::Draft->value     => 'Draft',
            self::Published->value => 'Published',
            self::Closed->value    => 'Closed',
        ];
    }
}
