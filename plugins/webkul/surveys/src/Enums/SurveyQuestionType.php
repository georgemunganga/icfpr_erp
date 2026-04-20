<?php

namespace Webkul\Survey\Enums;

enum SurveyQuestionType: string
{
    case ShortText = 'short_text';
    case LongText = 'long_text';
    case Email = 'email';
    case Phone = 'phone';
    case Url = 'url';
    case Number = 'number';
    case Date = 'date';
    case SingleChoice = 'single_choice';
    case MultipleChoice = 'multiple_choice';
    case YesNo = 'yes_no';
    case Rating = 'rating';
    case Select = 'select';

    public static function options(): array
    {
        return [
            self::ShortText->value      => 'Short text',
            self::LongText->value       => 'Long text',
            self::Email->value          => 'Email',
            self::Phone->value          => 'Phone',
            self::Url->value            => 'URL',
            self::Number->value         => 'Number',
            self::Date->value           => 'Date',
            self::SingleChoice->value   => 'Single choice',
            self::MultipleChoice->value => 'Multiple choice',
            self::YesNo->value          => 'Yes / No',
            self::Rating->value         => 'Rating',
            self::Select->value         => 'Dropdown',
        ];
    }

    public function supportsOptions(): bool
    {
        return in_array($this, [self::SingleChoice, self::MultipleChoice, self::Select], true);
    }

    public function inputType(): string
    {
        return match ($this) {
            self::ShortText      => 'text',
            self::LongText       => 'textarea',
            self::Email          => 'email',
            self::Phone          => 'tel',
            self::Url            => 'url',
            self::Number         => 'number',
            self::Date           => 'date',
            self::SingleChoice   => 'radio',
            self::MultipleChoice => 'checkbox',
            self::YesNo          => 'boolean',
            self::Rating         => 'rating',
            self::Select         => 'select',
        };
    }

    public function answerFormat(): string
    {
        return match ($this) {
            self::MultipleChoice => 'array',
            self::YesNo          => 'boolean',
            self::Number, self::Rating => 'number',
            self::Date => 'date',
            default    => 'string',
        };
    }

    public function acceptsMultipleAnswers(): bool
    {
        return $this === self::MultipleChoice;
    }
}
