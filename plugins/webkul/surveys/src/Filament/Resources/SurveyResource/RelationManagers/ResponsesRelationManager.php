<?php

namespace Webkul\Survey\Filament\Resources\SurveyResource\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResponsesRelationManager extends RelationManager
{
    protected static string $relationship = 'responses';

    protected static ?string $title = 'Responses';

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('respondent_name')->placeholder('Anonymous'),
            TextEntry::make('respondent_email'),
            TextEntry::make('respondent_phone'),
            TextEntry::make('respondent_organization'),
            TextEntry::make('respondent_location'),
            TextEntry::make('submitted_at')->dateTime(),
            RepeatableEntry::make('answers')
                ->schema([
                    TextEntry::make('question.prompt'),
                    TextEntry::make('value_text'),
                    KeyValueEntry::make('value_json')->visible(fn ($state) => filled($state)),
                ])
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['answers.question']))
            ->columns([
                TextColumn::make('respondent_name')
                    ->placeholder('Anonymous')
                    ->searchable(),
                TextColumn::make('respondent_email')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
