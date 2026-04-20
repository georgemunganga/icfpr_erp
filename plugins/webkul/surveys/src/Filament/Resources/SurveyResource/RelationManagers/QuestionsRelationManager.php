<?php

namespace Webkul\Survey\Filament\Resources\SurveyResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Webkul\Survey\Enums\SurveyQuestionType;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Questions';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('prompt')
                ->required()
                ->rows(3)
                ->columnSpanFull(),
            Textarea::make('help_text')
                ->rows(2)
                ->columnSpanFull(),
            TextInput::make('placeholder')
                ->maxLength(255)
                ->columnSpanFull(),
            Select::make('type')
                ->options(SurveyQuestionType::options())
                ->required(),
            TextInput::make('sort')
                ->numeric()
                ->default(fn () => ($this->getOwnerRecord()->questions()->max('sort') ?? 0) + 1)
                ->required(),
            Toggle::make('is_required')
                ->default(false),
            Repeater::make('options')
                ->schema([
                    TextInput::make('label')
                        ->required(),
                    TextInput::make('value')
                        ->required(),
                ])
                ->helperText('Use this for single choice and multiple choice questions.')
                ->columns(2)
                ->visible(fn ($get) => in_array($get('type'), [
                    SurveyQuestionType::SingleChoice->value,
                    SurveyQuestionType::MultipleChoice->value,
                    SurveyQuestionType::Select->value,
                ], true))
                ->columnSpanFull(),
            KeyValue::make('settings')
                ->helperText('Optional metadata for clients, e.g. min, max, step, pattern.')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('sort')
                    ->sortable(),
                TextColumn::make('prompt')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('type')
                    ->badge(),
                IconColumn::make('is_required')
                    ->boolean()
                    ->label('Required'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('New question')
                    ->icon('heroicon-o-plus-circle'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
