<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    protected $fillable = (['user_id', 'book_id', 'tgl_pinjam', 'tgl_batas', 'tgl_kembali', 'status']);

    protected $casts = [
        'tgl_pinjam' => 'date',
        'tgl_batas' => 'date',
        'tgl_kembali' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->BelongsTo(Book::class);
    }
}
