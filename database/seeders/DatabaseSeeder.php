<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'Admin', 'email' => 'admin@perpus.com', 'password' => bcrypt("admin123"), 'role' => 'admin']);
        User::create(['name' => 'Mamat', 'email' => 'mamat@perpus.com', 'password' => bcrypt('mamat123'), 'kelas' => 'XII RPL 3']);

        Book::create(['judul' => 'Petaka Merah', 'penerbit' => 'Gramedia', 'penulis' => 'Nadia Omakase', 'tahun_rilis' => 2026, 'stock' => (4)]);
        Book::create(['judul' => 'Duniawi', 'penerbit' => 'YouBooks', 'penulis' => 'Rumahsakit', 'tahun_rilis' => 2015, 'stock' => (5)]);
    }
}
