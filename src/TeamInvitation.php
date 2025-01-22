<?php

namespace ArtisanBuild\Verbstream;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamInvitation extends Model
{
    protected $fillable = [
        'email',
        'role',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Verbstream::teamModel());
    }
}
