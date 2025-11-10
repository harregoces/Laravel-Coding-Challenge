<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class FavoriteQuotesSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(3)->get();
        $quotes = Quote::inRandomOrder()->take(9)->get();

        DB::transaction(function () use ($users, $quotes) {
            foreach ($users as $idx => $user) {
                foreach ($quotes->slice($idx * 3, 3) as $quote) {
                    $user->favoriteQuotes()->syncWithoutDetaching([$quote->id]);
                }
            }
        });
    }
}
