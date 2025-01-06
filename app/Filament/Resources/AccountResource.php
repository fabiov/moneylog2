<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->maxLength(255)
                ->required(),
            Forms\Components\Select::make('status')
                ->options(['closed' => 'Closed', 'open' => 'Open', 'highlight' => 'Highlight'])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->actions([Tables\Actions\EditAction::make()])
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('status')
                    ->alignCenter()
                    ->badge()
                    ->colors(['primary' => 'open', 'success' => 'highlight', 'danger' => 'closed']),
            ])
            ->filters([])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->defaultPaginationPageOption(25);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }

    /**
     * @return Builder<Account>
     */
    public static function getEloquentQuery(): Builder
    {
        /** @var User $user */
        $user = Auth::user();

        return parent::getEloquentQuery()->where('user_id', '=', $user->id);
    }
}
