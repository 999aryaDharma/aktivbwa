<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Workshop;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\WorkshopResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\WorkshopResource\RelationManagers;

class WorkshopResource extends Resource
{
    protected static ?string $model = Workshop::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Fieldset::make('Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                    Forms\Components\Textarea::make('address')
                    ->rows(3)
                    ->required()
                    ->maxLength(255),

                    Forms\Components\FileUpload::make('thumbnail')
                    ->image()
                    ->required(),

                    Forms\Components\FileUpload::make('venue_thumbnail')
                    ->image()
                    ->required(),

                    Forms\Components\FileUpload::make('bg_map')
                    ->image()
                    ->required(),

                    Forms\Components\Repeater::make('benefits')
                    ->relationship('benefits')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    ])
                ]),

                Fieldset::make('Additional')
                ->schema([
                    Forms\Components\Textarea::make('about')
                    ->required(),

                    Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp '),

                    Forms\Components\Select::make('is_open')
                    ->options([
                        true => 'Open',
                        false => 'Not Available',
                    ])
                    ->required(),

                    Forms\Components\Select::make('has_started')
                    ->options([
                        true => 'Started',
                        false => 'Not Started Yet',
                    ])
                    ->required(),

                    Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                    Forms\Components\Select::make('workshop_instructor_id')
                    ->relationship('instructor', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                    Forms\Components\DateTimePicker::make('started_at')
                    ->required(),

                    Forms\Components\TimePicker::make('time_at')
                    ->required(),

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\ImageColumn::make('thumbnail'),

                Tables\Columns\TextColumn::make('name')
                ->searchable(),

                Tables\Columns\TextColumn::make('category.name'),

                Tables\Columns\TextColumn::make('instructor.name'),

                Tables\Columns\IconColumn::make('has_started')
                ->boolean()
                ->trueColor('success')
                ->falseColor('danger')
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->label('Started'),

                Tables\Columns\TextColumn::make('participants_count')
                ->label('Participants')
                ->counts('participants'),
            ])
            ->filters([
                //
                SelectFilter::make('category_id')
                ->label('category')
                ->relationship('category', 'name'),

                SelectFilter::make('workshop_instructor_id')
                ->label('workshop_instructor')
                ->relationship('instructor', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkshops::route('/'),
            'create' => Pages\CreateWorkshop::route('/create'),
            'edit' => Pages\EditWorkshop::route('/{record}/edit'),
        ];
    }
}
