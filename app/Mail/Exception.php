<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Exception extends Mailable
{
    use Queueable, SerializesModels;
	
	public $content;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Exception - Stellar OMS',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {

        $user = DB::table('oms_users')->where('id', Auth::id())->first();

        $this->content['UserName'] = $user->username;

        DB::unprepared("INSERT INTO oms_email_log (subject, content, dateTime, userName, currentURL, prevURL, lineNo) VALUES ('Exception', '".$this->content['message']."', '".date('Y-m-d H:i:s')."', '".$user->username."', '".$this->content['url']."', '".$this->content['prevurl']."', 'in ".addslashes($this->content['file']).' line '.$this->content['line']."') ");
		
		return new Content(
            view: 'globalException.exception',
            with: [
                'content' => $this->content
            ],
        );
		
        /* return new Content(
            view: 'view.name',
        ); */
    }

}
