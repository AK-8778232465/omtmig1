<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Log;
use Mail;
use App\Mail\Exception;


class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->sendEmail($e);
        });
    }

    // Send mail
    public function sendEmail(Throwable $exception)
    {
      
        try {
            $content['message'] = $exception->getMessage();
            $content['file'] = $exception->getFile();
            $content['line'] = $exception->getLine();
            $content['trace'] = $exception->getTrace();
            $content['url'] = request()->url();
            $content['body'] = request()->all();
            $content['ip'] = request()->ip();
            $content['UserName'] = session('UserName');
            $content['prevurl'] = url()->previous();
    
            if (isset(request()->all()['tempfilename'])) {
                $content['tempfilename'] = request()->all()['tempfilename'];
                $content['originalfilename'] = request()->all()['originalfilename'];
            }

            $toAddr = 'rajalakshmimani@stellaripl.com';
            $ccAddr = 'shanmugam@stellaripl.com';
            $bccAddr = 'manikandan.v@stellaripl.com';
    
            Mail::to($toAddr)->cc($ccAddr)->bcc($bccAddr)->send(new Exception($content));
        }
		catch (Throwable $exception) {
            Log::error('Error sending email: ' . $exception->getMessage());
            Log::error($exception->getTraceAsString());
        }
    }
}
