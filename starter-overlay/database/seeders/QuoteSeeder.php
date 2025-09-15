<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Quote;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('fixtures/quotes.json');
        $data = json_decode(File::get($path), true);

        foreach ($data as $row) {
            $text = $row['q'];
            $author = $row['a'] ?? null;
            $hash = hash('sha256', $text . '|' . $author);

            Quote::firstOrCreate(
                ['unique_hash' => $hash],
                ['text' => $text, 'author' => $author, 'source_key' => 'fixture']
            );
        }
    }
}
