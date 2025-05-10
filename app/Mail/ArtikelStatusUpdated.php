<?php

namespace App\Mail;

use App\Models\ArtikelModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ArtikelStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $artikel;

    public function __construct(ArtikelModel $artikel)
    {
        $this->artikel = $artikel;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Artikel Status Updated',
        );
    }

    /**
     * Get the message content definition.
     */
     public function build()
    {
        return $this->subject('Status Artikel Anda Telah Diperbarui')
                    ->view('emails.updatestatus')->with(['artikel' => $this->artikel]);
    }
}
