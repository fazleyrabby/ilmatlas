<?php

namespace App\Modules\SEO\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('modules.seo.static.contact');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        try {
            Mail::raw("Name: {$data['name']}\nEmail: {$data['email']}\n\n{$data['message']}", function ($msg) {
                $msg->to(config('mail.from.address'))
                    ->subject('EduBase Contact Form Submission');
            });
        } catch (\Exception $e) {
            // Log but don't break — mail may not be configured
            logger()->error('Contact form mail failed: '.$e->getMessage());
        }

        return redirect()->route('contact')->with('success', 'Thank you! Your message has been sent.');
    }
}
