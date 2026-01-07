<?php

namespace App\Mail;

use App\Models\Boleto;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BoletoMensalMail extends Mailable
{
    use Queueable, SerializesModels;

    public Boleto $boleto;

    public function __construct(Boleto $boleto)
    {
        $this->boleto = $boleto;
    }

    public function build()
    {
        return $this->subject('Boleto disponível para pagamento')
            ->view('emails.boleto-mensal');
    }
}