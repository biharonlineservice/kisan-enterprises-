<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteInquiry extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_READ = 'read';
    public const STATUS_RESPONDED = 'responded';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'inquiry_type',
        'subject',
        'message',
        'status',
        'admin_notes',
    ];

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }
}
