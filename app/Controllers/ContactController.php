<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ContactMessageModel;

class ContactController extends BaseController
{
    public function index()
    {
        $titl['pag'] = 'contact';
        $data['info'] = '';

        return view('headers_footers/head_landing')
            . view('headers_footers/sidebar_landing', $titl)
            . view('landing/contact', $data)
            . view('headers_footers/footer_landing');
    }

    public function send()
    {
        $rules = [
            'contact_name'    => 'required|min_length[2]|max_length[255]',
            'contact_email'   => 'required|valid_email|max_length[255]',
            'contact_subject' => 'required|max_length[255]',
            'contact_message' => 'required|min_length[10]',
            'privacy_consent' => 'required',
            'contact_attachment' => [
                'label' => 'Attachment',
                'rules' => [
                    'permit_empty', // Allow nulls explicitly
                    'uploaded[contact_attachment]', // This check is contradictory with permit_empty if strictly interpreted by CI4 depending on version, but usually we use check() logic. 
                                                    // Make it simpler: Only validate if file provided.
                    'mime_in[contact_attachment,image/jpg,image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document]',
                    'max_size[contact_attachment,5120]',
                ],
                'errors' => [
                    'mime_in' => 'Invalid file type. Please upload an image or document.',
                    'max_size' => 'File size too large. Maximum size is 5MB.',
                ]
            ],
        ];

        // Refined File Validation Logic
        $file = $this->request->getFile('contact_attachment');
        // If no file is uploaded (error == 4), remove the validation rules entirely to allow "null"
        if ($file && $file->getError() == UPLOAD_ERR_NO_FILE) {
            unset($rules['contact_attachment']);
        }

        if (! $this->validate($rules)) {
            return redirect()->route('contact')->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $attachmentPath = null;
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'contact_me/', $newName);
            $attachmentPath = 'contact_me/' . $newName;
        }

        $model = new ContactMessageModel();
        
        $saveData = [
            'name'       => $this->request->getPost('contact_name'),
            'email'      => $this->request->getPost('contact_email'),
            'subject'    => $this->request->getPost('contact_subject'),
            'message'    => $this->request->getPost('contact_message'),
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'attachment' => $attachmentPath,
        ];

        if ($model->save($saveData)) {
            return redirect()->route('contact')->with('success', 'Your message has been sent successfully. We will get back to you shortly!');
        } else {
            return redirect()->route('contact')->withInput()->with('error', 'Something went wrong. Please try again later.');
        }
    }
}
