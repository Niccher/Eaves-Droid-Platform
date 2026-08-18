<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;
use App\Models\FinderModel;
use App\Models\CryptModel;
use App\Models\ExtractModel;

class AnalyzeController extends BaseClientController
{
    /**
     * Build all phone number variants for a given raw number.
     * Handles: +2547XXXXXXXX, 07XXXXXXXX, #07XXXXXXXX
     */
    private function buildNumberVariants(string $rawNumber): array
    {
        $number = str_replace(' ', '', $rawNumber);
        // Strip leading # if present
        $clean = ltrim($number, '#');
        $variants = [];

        if (str_starts_with($clean, '+254')) {
            // +254711111111  →  also add 0711111111 and #0711111111
            $local   = '0' . substr($clean, 4);
            $hash    = '#' . $local;
            $variants = [$clean, $local, $hash];
        } elseif (str_starts_with($clean, '254') && strlen($clean) >= 12) {
            // 254711111111 (without +)
            $intl    = '+' . $clean;
            $local   = '0' . substr($clean, 3);
            $hash    = '#' . $local;
            $variants = [$intl, $clean, $local, $hash];
        } elseif (str_starts_with($clean, '0')) {
            // 0711111111  →  also add +254711111111 and #0711111111
            $intl    = '+254' . substr($clean, 1);
            $hash    = '#' . $clean;
            $variants = [$intl, $clean, $hash];
        } else {
            $variants = [$number];
        }

        return array_unique($variants);
    }

    public function sms($target_contact)
    {
        $model_finder  = new FinderModel();
        $model_crypt   = new CryptModel();
        $model_extract = new ExtractModel();
        $encrypter     = \Config\Services::encrypter();

        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        $data['pag']       = 'sms_analyse';
        $data['user_info'] = $model_finder->basic_user();

        // Populate sidebar counts
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $contact_id = $model_crypt->decrypt_id($target_contact);
        $contact    = $model_extract->get_contact_at($contact_id);

        if (!$contact) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Contact not found");
        }

        $variants = $this->buildNumberVariants($contact['Number'] ?? '');

        $data['sms_person']  = $contact['Number'] ?? '';
        $data['sms_saved']   = $contact['Name']   ?? 'Unknown';
        $data['number_variants'] = $variants;
        $data['contact']     = $contact;
        $data['sms_thread']  = $model_extract->get_sms_between_contacts(
            $data['user_info']['id'],
            $variants
        );
        $data['log_thread']  = $model_extract->get_logs_between_contacts(
            $data['user_info']['id'],
            $variants
        );

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/analyze/sms', $data)
            . view('headers_footers/footer_users');
    }

    public function calls($target_contact)
    {
        $model_finder  = new FinderModel();
        $model_crypt   = new CryptModel();
        $model_extract = new ExtractModel();
        $encrypter     = \Config\Services::encrypter();

        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        $data['pag']       = 'sms_analyse';
        $data['user_info'] = $model_finder->basic_user();

        // Populate sidebar counts
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

        $contact_id = $model_crypt->decrypt_id($target_contact);
        $contact    = $model_extract->get_contact_at($contact_id);

        if (!$contact) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Contact not found");
        }

        $variants = $this->buildNumberVariants($contact['Number'] ?? '');

        $data['log_person']      = $contact['Number'] ?? '';
        $data['log_saved']       = $contact['Name']   ?? 'Unknown';
        $data['number_variants'] = $variants;
        $data['contact']         = $contact;
        $data['log_thread']      = $model_extract->get_logs_between_contacts(
            $data['user_info']['id'],
            $variants
        );
        $data['sms_thread']      = $model_extract->get_sms_between_contacts(
            $data['user_info']['id'],
            $variants
        );

        return view('headers_footers/head_users')
            . view('headers_footers/sidebar_users', $data)
            . view('users/analyze/call_logs', $data)
            . view('headers_footers/footer_users');
    }
}
