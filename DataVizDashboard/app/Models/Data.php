<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    protected $table = 'data'; // Explicitly specify table name if needed

    protected $fillable = [
        'end_year', 'citylng', 'citylat', 'intensity', 'sector', 'topic', 'insight', 'swot', 'url',
        'region', 'start_year', 'impact', 'added', 'published', 'city', 'country', 'relevance', 'pestle',
        'source', 'title', 'likelihood'
    ];

    public $timestamps = false; // Set this to false if you do not use Laravel's default timestamps
}

