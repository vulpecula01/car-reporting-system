<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class TomorrowReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $department, public Carbon $date, public Collection $events)
    {
    }

    public function envelope(): Envelope
    {
        $dept = User::DEPARTMENTS[$this->department] ?? $this->department;
        return new Envelope(
            subject: "[Mobile Planner] งานแผนก {$dept} วันที่ " . $this->date->format('d/m/Y')
                . " ({$this->events->count()} งาน)",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reminder');
    }
}
