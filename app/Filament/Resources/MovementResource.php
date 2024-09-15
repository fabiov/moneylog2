<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MovementResource\Pages;
use App\Models\Account;
use App\Models\Category;
use App\Models\Movement;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MovementResource extends Resource
{
    protected static ?string $model = Movement::class;

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
                    ->default(Carbon::now())
                    ->required(),
                Forms\Components\Select::make('account_id')
                    ->options(Account::where('status', '<>', 'closed')->orderBy('name')->pluck('name', 'id'))
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->options(Category::where('active', true)->orderBy('name')->pluck('name', 'id')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->date('d/m/Y')->sortable()->width(105),
                Tables\Columns\TextColumn::make('amount')->money('eur')->alignRight(),
                Tables\Columns\TextColumn::make('description')->wrap()->searchable(),
                Tables\Columns\TextColumn::make('account.name'),
                Tables\Columns\TextColumn::make('category.name'),
            ])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovements::route('/'),
            'create' => Pages\CreateMovement::route('/create'),
            'edit' => Pages\EditMovement::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var User $user */
        $user = Auth::user();

        return parent::getEloquentQuery()
            ->whereIn('account_id', Account::all()->where('user_id', '=', $user->id)->pluck('id'));
    }
}
