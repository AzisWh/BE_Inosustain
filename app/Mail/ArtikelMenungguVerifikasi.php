<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ArtikelModel;
use App\Models\User;
use Illuminate\Mail\Mailables\Envelope;

class ArtikelMenungguVerifikasi extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

     public $artikel;
     public $penulis;

     public function __construct($artikel, $penulis)
     {
         $this->artikel = $artikel;
         $this->penulis = $penulis;
     }

     public function envelope(): Envelope
     {
         return new Envelope(
             subject: 'Artikel Baru Created',
         );
     }
     
     public function build()
     {
         return $this->subject('Artikel Baru Menunggu Verifikasi')
         ->view('emails.artikel-menunggu')
                     ->with([
                         'artikel' => $this->artikel,
                         'penulis' => $this->penulis,
                     ]);
     }
}

   

