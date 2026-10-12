<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BorrowNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Event $event, public string $action = 'create')
    {
    }

    public function envelope(): Envelope
    {
        $verb = ['create' => 'ยืมรถ', 'update' => 'แก้ไขการยืมรถ', 'delete' => 'ยกเลิกการยืมรถ'][$this->action] ?? 'ยืมรถ';
        $by = User::DEPARTMENTS[$this->event->department] ?? $this->event->department;

        return new Envelope(
            subject: "[Mobile Planner] แผนก {$by} {$verb} " . optional($this->event->car)->name
                . ' วันที่ ' . date('d/m/Y', strtotime($this->event->start)),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.borrow');
    }
}
