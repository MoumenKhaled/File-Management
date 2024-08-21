<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgottenPassword extends Mailable
{
    use Queueable, SerializesModels;
    protected $ResetPassword;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($ResetPassword)
    {
        $this->ResetPassword=$ResetPassword;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $code=$this->ResetPassword->token;
       // $url=url(path:'api/forgotpassword/'.$this->ResetPassword->token);
        return $this->from(address:'moumen@gmai.com')->view(view:'mail.forget')->with([
            'email'=>$this->ResetPassword->email,
            'code'=>$code
             //'url'>=$url,

        ]);
       // return $this->view(view:'view.name');
    }
}
