<?php

class Hello extends App
{
    public function emailTest()
    {
        Route::get('/send-email', function () {
            $to_email_address = 'abumucal@gmail.com';
            $subject = 'testing mail';
            $message = 'testing mail from devprogress.a1educare';

            mail($to_email_address, $subject, $message);
            // mail($to_email_address, $subject, $message, [$headers], [$parameters]);
            return view('welcome');
        });
    }
}
