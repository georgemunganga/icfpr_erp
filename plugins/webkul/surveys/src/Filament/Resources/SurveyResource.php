<?php

namespace Webkul\Survey\Filament\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Webkul\Security\Filament\Resources\CompanyResource;
use Webkul\Survey\Enums\SurveyStatus;
use Webkul\Survey\Filament\Resources\SurveyResource\Pages\CreateSurvey;
use Webkul\Survey\Filament\Resources\SurveyResource\Pages\EditSurvey;
use Webkul\Survey\Filament\Resources\SurveyResource\Pages\ListSurveys;
use Webkul\Survey\Filament\Resources\SurveyResource\Pages\ViewSurvey;
use Webkul\Survey\Filament\Resources\SurveyResource\RelationManagers\QuestionsRelationManager;
use Webkul\Survey\Filament\Resources\SurveyResource\RelationManagers\ResponsesRelationManager;
use Webkul\Survey\Models\Survey;

class SurveyResource extends Resource
{
    protected static ?string $model = Survey::class;

    protected static ?string $slug = 'research/surveys';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return 'Surveys';
    }

    public static function getNavigationGroup(): string
    {
        return __('admin.navigation.policy-research');
    }

    public static function form(Schema $schema): Schema
    {
        $ownershipFields = [
            Select::make('company_id')
                ->relationship('company', 'name')
                ->searchable()
                ->preload()
                ->default(fn () => Auth::user()?->default_company_id)
                ->createOptionForm(fn (Schema $schema) => CompanyResource::form($schema)),
        ];

        if (DatabaseSchema::hasTable('projects_projects') && DatabaseSchema::hasTable('projects_tasks')) {
            $ownershipFields = [
                ...$ownershipFields,
                Select::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(fn (Set $set) => $set('task_id', null))
                    ->live(),
                Select::make('task_id')
                    ->label('Task')
                    ->relationship(
                        'task',
                        'title',
                        modifyQueryUsing: fn (Builder $query, Get $get) => $query
                            ->when($get('project_id'), fn (Builder $query, $projectId) => $query->where('project_id', $projectId))
                    )
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get): bool => blank($get('project_id')))
                    ->placeholder('Optional task link')
                    ->helperText('Optional. If a project is selected, tasks are filtered to that project.')
                    ->nullable(),
            ];
        }

        $ownershipFields = [
            ...$ownershipFields,
            TextInput::make('slug')
                ->helperText('Used for a readable identifier. Leave blank to generate automatically.')
                ->maxLength(255),
            TextInput::make('public_token')
                ->disabled()
                ->dehydrated(false)
                ->helperText('Generated automatically when the survey is created.'),
        ];

        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Survey')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus(),
                                RichEditor::make('description')
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Access')
                            ->schema([
                                Select::make('status')
                                    ->options(SurveyStatus::options())
                                    ->default(SurveyStatus::Draft->value)
                                    ->required(),
                                Toggle::make('is_public')
                                    ->label('Allow public submissions')
                                    ->default(false),
                                DateTimePicker::make('opens_at'),
                                DateTimePicker::make('closes_at')
                                    ->afterOrEqual('opens_at'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make('Ownership')
                            ->schema($ownershipFields),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('title')
                ->searchable()
                ->sortable(),
            TextColumn::make('status')
                ->badge()
                ->sortable(),
        ];

        if (static::hasProjectTables()) {
            $columns = [
                ...$columns,
                TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('task.title')
                    ->label('Task')
                    ->searchable()
                    ->toggleable(),
            ];
        }

        $columns = [
            ...$columns,
            IconColumn::make('is_public')
                ->label('Public')
                ->boolean(),
            TextColumn::make('questions_count')
                ->label('Questions')
                ->sortable(),
            TextColumn::make('responses_count')
                ->label('Responses')
                ->sortable(),
            TextColumn::make('opens_at')
                ->dateTime()
                ->sortable()
                ->toggleable(),
            TextColumn::make('closes_at')
                ->dateTime()
                ->sortable()
                ->toggleable(),
            TextColumn::make('updated_at')
                ->since()
                ->sortable()
                ->label('Updated'),
        ];

        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount(['questions', 'responses']))
            ->columns($columns)
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        $surveyEntries = [
            TextEntry::make('title'),
            TextEntry::make('status')->badge(),
            TextEntry::make('description')->html()->columnSpanFull(),
            TextEntry::make('slug'),
            TextEntry::make('public_token'),
        ];

        if (static::hasProjectTables()) {
            $surveyEntries = [
                ...$surveyEntries,
                TextEntry::make('project.name')->label('Project'),
                TextEntry::make('task.title')->label('Task'),
            ];
        }

        $surveyEntries = [
            ...$surveyEntries,
            TextEntry::make('questions_count')->label('Questions'),
            TextEntry::make('responses_count')->label('Responses'),
        ];

        return $schema
            ->components([
                Section::make('Survey')
                    ->schema($surveyEntries)
                    ->columns(2),
                Section::make('Responses')
                    ->schema([
                        RepeatableEntry::make('responses')
                            ->schema([
                                TextEntry::make('respondent_name')->placeholder('Anonymous'),
                                TextEntry::make('respondent_email'),
                                TextEntry::make('submitted_at')->dateTime(),
                                RepeatableEntry::make('answers')
                                    ->schema([
                                        TextEntry::make('question.prompt'),
                                        TextEntry::make('value_text')
                                            ->placeholder(fn ($record) => filled($record->value_json) ? json_encode($record->value_json) : '—'),
                                        KeyValueEntry::make('value_json')->visible(fn ($state) => filled($state)),
                                    ])
                                    ->columns(1),
                            ])
                            ->contained(false)
                            ->columns(1),
                    ])
                    ->visible(fn (Survey $record) => $record->responses_count > 0),
            ]);
    }

    protected static function hasProjectTables(): bool
    {
        return DatabaseSchema::hasTable('projects_projects') && DatabaseSchema::hasTable('projects_tasks');
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Survey builder', [
                QuestionsRelationManager::class,
                ResponsesRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSurveys::route('/'),
            'create' => CreateSurvey::route('/create'),
            'view'   => ViewSurvey::route('/{record}'),
            'edit'   => EditSurvey::route('/{record}/edit'),
        ];
    }
}
