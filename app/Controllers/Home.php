<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string{
        $data['pag']        = 'landing';
        $data['page_title'] = 'Eaves Droid - Mobile Data Intelligence Platform';
        $data['page_desc']  = 'Eaves Droid is a free mobile data intelligence platform. Analyze calls, SMS, and files from Android devices with AI-powered correlation mapping and visual dashboards.';
        $data['page_keys']  = 'mobile data intelligence, android data analysis, call log analysis, SMS correlation, phone data analytics, free mobile analytics platform';
        return view('headers_footers/head_landing', $data)
            . view('landing/landing')
            . view('headers_footers/footer_landing');
    }

    public function landing_download($pg = 'download'){
        $data['pag']        = 'download';
        $data['page_title'] = 'Download Android Client | Eaves Droid';
        $data['page_desc']  = 'Download the Eaves Droid Android client app to start securely collecting and syncing your mobile data for deep analytics and visualization.';
        $data['page_keys']  = 'download android app, mobile data collector, eaves droid apk, android client download, data sync app';
        return view('headers_footers/head_landing', $data)
            . view('landing/'. $pg)
            . view('headers_footers/footer_landing');
    }

    public function landing_aboutus($pg = 'about'){
        $data['pag']        = 'about';
        $data['page_title'] = 'About Us | Eaves Droid';
        $data['page_desc']  = 'Learn about Eaves Droid — a solo-built, full-stack mobile data intelligence platform combining Android data extraction, cloud processing, and AI-powered analytics.';
        $data['page_keys']  = 'about eaves droid, mobile data platform, android analytics, data intelligence, solo developer, full stack';
        return view('headers_footers/head_landing', $data)
            . view('landing/'. $pg)
            . view('headers_footers/footer_landing');
    }

    public function landing_contactus($pg = 'contact'){
        $data['pag']        = 'contact';
        $data['page_title'] = 'Contact Us | Eaves Droid';
        $data['page_desc']  = 'Get in touch with the Eaves Droid team. Send us a message for support, partnership inquiries, or feedback about our mobile data intelligence platform.';
        $data['page_keys']  = 'contact eaves droid, mobile analytics support, get in touch, data intelligence help';
        return view('headers_footers/head_landing', $data)
            . view('landing/'. $pg)
            . view('headers_footers/footer_landing');
    }

    public function landing_faqs($pg = 'faq'){
        $data['pag']        = 'faqs_terms';
        $data['page_title'] = 'FAQ & Terms of Service | Eaves Droid';
        $data['page_desc']  = 'Find answers to frequently asked questions about Eaves Droid, including technical requirements, data privacy, account management, and terms of service.';
        $data['page_keys']  = 'eaves droid faq, terms of service, mobile analytics questions, data privacy, account help, android analytics faq';
        return view('headers_footers/head_landing', $data)
            . view('landing/'. $pg)
            . view('headers_footers/footer_landing');
    }

    public function landing_privacy($pg = 'privacy'){
        $data['pag']        = 'privacy-policy';
        $data['page_title'] = 'Privacy Policy | Eaves Droid';
        $data['page_desc']  = 'Read the Eaves Droid Privacy Policy to understand how we collect, use, store, and protect your mobile data when you use our intelligence platform.';
        $data['page_keys']  = 'eaves droid privacy policy, data privacy, mobile data protection, data security, privacy terms';
        return view('headers_footers/head_landing', $data)
            . view('landing/privacy')
            . view('headers_footers/footer_landing');
    }

    public function landing_how_to($pg = 'howto'){
        $data['pag']        = 'howto';
        $data['page_title'] = 'How It Works | Eaves Droid';
        $data['page_desc']  = 'Learn how Eaves Droid works in 4 simple steps — create an account, install the Android client, collect your mobile data, and view AI-powered insights on your dashboard.';
        $data['page_keys']  = 'how eaves droid works, mobile data analysis steps, android data extraction guide, phone analytics tutorial';
        return view('headers_footers/head_landing', $data)
            . view('landing/'. $pg)
            . view('headers_footers/footer_landing');
    }

    public function landing_prices($pg = 'prices'){
        $data['pag']        = 'pricing';
        $data['page_title'] = 'Pricing Plans | Eaves Droid';
        $data['page_desc']  = 'Compare Eaves Droid pricing plans — Free, Gold, and Platinum. Choose the tier that fits how many devices you monitor and how deep your data analysis needs to go.';
        $data['page_keys']  = 'eaves droid pricing, free plan, gold plan, platinum plan, mobile analytics subscription, device monitoring plans';

        $planModel = new \App\Models\PlanModel();
        $versions = $planModel->getAllCurrentVersions();
        $plans = [];
        foreach ($versions as $v) {
            $v['features'] = json_decode($v['features'] ?? '{}', true) ?: [];
            $v['ml_algorithms'] = json_decode($v['ml_algorithms'] ?? '[]', true) ?: [];
            $plans[$v['slug']] = $v;
        }
        $data['plans'] = $plans;

        return view('headers_footers/head_landing', $data)
            . view('landing/'. $pg)
            . view('headers_footers/footer_landing');
    }

    public function landing_error_404($pg = 'landing_404'){
        $data['page_title'] = '404 Not Found | Eaves Droid';
        $data['page_desc']  = 'The page you are looking for could not be found on Eaves Droid.';
        $data['page_keys']  = 'page not found, 404, eaves droid';
        return view('headers_footers/head_landing', $data)
            . view('error/'. $pg)
            . view('headers_footers/footer_landing');
    }
}
