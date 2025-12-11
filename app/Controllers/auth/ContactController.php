<?php

namespace App\Controllers\auth;

class ContactController extends BaseController
{
    // app/Controllers/ContactController.php
    public function submit()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'contact_name' => 'required|min_length[3]|max_length[100]',
            'contact_email' => 'required|valid_email',
            'contact_subject' => 'required',
            'contact_message' => 'required|min_length[10]|max_length[2000]',
            'privacy_consent' => 'required',
        ];

        if ($validation->setRules($rules)->withRequest($this->request)->run()) {
            // Process the form (send email, save to database, etc.)

            // Set success message
            session()->setFlashdata('success', 'Thank you for your message! We will get back to you soon.');

            // Clear form
            return redirect()->to(current_url());
        } else {
            // Validation failed
            session()->setFlashdata('errors', $validation->getErrors());
            session()->setFlashdata('error', 'Please fix the errors below.');

            // Keep old input
            session()->setFlashdata('_ci_old_input', $this->request->getPost());

            return redirect()->back()->withInput();
        }
    }
}