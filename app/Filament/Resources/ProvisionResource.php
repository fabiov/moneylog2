<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProvisionResource\Pages;
use App\Models\Provision;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProvisionResource extends Resource
{
    protected static ?string $model = Provision::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->type('number')
                    ->step(0.01),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('date')
                    ->default(now())
                    ->required()
                    ->maxDate(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->actions([Tables\Actions\EditAction::make()])
            ->columns([
                Tables\Columns\TextColumn::make('date')->date('d/m/Y')->sortable()->width(105),
                Tables\Columns\TextColumn::make('amount')->money('eur')->sortable()->alignRight(),
                Tables\Columns\TextColumn::make('description')->wrap(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25)
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProvisions::route('/'),
            'create' => Pages\CreateProvision::route('/create'),
            'edit' => Pages\EditProvision::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var User $user */
        $user = Auth::user();

        return parent::getEloquentQuery()->where('user_id', '=', $user->id);
    }
}
