<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\BaseModel;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Spatie\MailTemplates\Interfaces\MailTemplateInterface;
use Spatie\MailTemplates\TemplateMailable;

/**
 * @mixin Eloquent
 * @property string code
 * @property string to
 * @property string reply_to
 * @property string html_template
 */
class MailTemplate extends \Spatie\MailTemplates\Models\MailTemplate
{
    use HasFactory;

    protected $fillable = [
        'code',
        'mailable',
        'subject',
        'html_template',
        'text_template',
        'reply_to',
        'to'
    ];

    public function getNameAttribute()
    {
        return $this->code;
    }
}
