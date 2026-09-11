<?php

namespace App\Controllers\admin;

use App\Models\ContactMessageModel;
use CodeIgniter\HTTP\ResponseInterface;

class AdminInquiriesController extends BaseAdminController
{
    protected ContactMessageModel $inquiriesModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->inquiriesModel = new ContactMessageModel();
    }

    public function index()
    {
        $data['inquiries'] = $this->inquiriesModel->orderBy('created_at', 'DESC')->findAll();

        return $this->renderView('admin/support/inquiries', $data, [
            'pag' => 'inquiries',
            'title' => 'Inquiries Inbox',
            'subtitle' => 'Manage user messages from the Contact Us form'
        ]);
    }

    public function viewInquiry($id)
    {
        $inquiry = $this->inquiriesModel->find($id);
        if (!$inquiry) {
            return redirect()->route('admin_inquiries')->with('error', 'Inquiry not found.');
        }

        $data['inquiry'] = $inquiry;

        return $this->renderView('admin/support/view_inquiry', $data, [
            'pag' => 'inquiries',
            'title' => 'View Inquiry',
            'subtitle' => 'Message details'
        ]);
    }

    public function updateStatus($id)
    {
        $inquiry = $this->inquiriesModel->find($id);
        if (!$inquiry) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Inquiry not found.']);
        }

        $status = $this->request->getPost('status');
        if (!in_array($status, ['pending', 'in_progress', 'resolved'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid status.']);
        }

        $this->inquiriesModel->update($id, ['status' => $status]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Status updated successfully.']);
    }
    
    public function deleteInquiry($id)
    {
        if ($this->inquiriesModel->delete($id)) {
            return redirect()->route('admin_inquiries')->with('success', 'Inquiry deleted.');
        }
        return redirect()->route('admin_inquiries')->with('error', 'Failed to delete inquiry.');
    }

    public function downloadAttachment($filename)
    {
        $path = WRITEPATH . 'contact_me/' . $filename;
        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'Attachment not found on server.');
        }

        return $this->response->download($path, null);
    }
}
