<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string{
        $data['pag']        = 'landing';
        $data['page_title'] = 'Prj Images - Mobile Data Intelligence Platform';
        $data['page_desc']  = 'Prj Images is a free mobile data intelligence platform. Analyze calls, SMS, and files from Android devices with AI-powered correlation mapping and visual dashboards.';
        $data['page_keys']  = 'mobile data intelligence, android data analysis, call log analysis, SMS correlation, phone data analytics, free mobile analytics platform';
        return view('headers_footers/head_landing', $data)
            . view('landing/landing')
            . view('headers_footers/footer_landing');
    }

    public function landing_download($pg = 'download'){
        $data['pag']        = 'download';
        $data['page_title'] = 'Download Android Client | Prj Images';
        $data['page_desc']  = 'Download the Prj Images Android client app to start securely collecting and syncing your mobile data for deep analytics and visualization.';
        $data['page_keys']  = 'download android app, mobile data collector, prj images apk, android client download, data sync app';
        return view('headers_footers/head_landing', $data)
            . view('landing/'. $pg)
            . view('headers_footers/footer_landing');
    }

    public function landing_aboutus($pg = 'about'){
        $data['pag']        = 'about';
        $data['page_title'] = 'About Us | Prj Images';
        $data['page_desc']  = 'Learn about Prj Images — a solo-built, full-stack mobile data intelligence platform combining Android data extraction, cloud processing, and AI-powered analytics.';
        $data['page_keys']  = 'about prj images, mobile data platform, android analytics, data intelligence, solo developer, full stack';
        return view('headers_footers/head_landing', $data)
            . view('landing/'. $pg)
            . view('headers_footers/footer_landing');
    }

    public function landing_contactus($pg = 'contact'){
        $data['pag']        = 'contact';
        $data['page_title'] = 'Contact Us | Prj Images';
        $data['page_desc']  = 'Get in touch with the Prj Images team. Send us a message for support, partnership inquiries, or feedback about our mobile data intelligence platform.';
        $data['page_keys']  = 'contact prj images, mobile analytics support, get in touch, data intelligence help';
        return view('headers_footers/head_landing', $data)
            . view('landing/'. $pg)
            . view('headers_footers/footer_landing');
    }

    public function landing_faqs($pg = 'faq'){
        $data['pag']        = 'faqs_terms';
        $data['page_title'] = 'FAQ & Terms of Service | Prj Images';
        $data['page_desc']  = 'Find answers to frequently asked questions about Prj Images, including technical requirements, data privacy, account management, and terms of service.';
        $data['page_keys']  = 'prj images faq, terms of service, mobile analytics questions, data privacy, account help, android analytics faq';
        return view('headers_footers/head_landing', $data)
            . view('landing/'. $pg)
            . view('headers_footers/footer_landing');
    }

    public function landing_how_to($pg = 'howto'){
        $data['pag']        = 'howto';
        $data['page_title'] = 'How It Works | Prj Images';
        $data['page_desc']  = 'Learn how Prj Images works in 4 simple steps — create an account, install the Android client, collect your mobile data, and view AI-powered insights on your dashboard.';
        $data['page_keys']  = 'how prj images works, mobile data analysis steps, android data extraction guide, phone analytics tutorial';
        return view('headers_footers/head_landing', $data)
            . view('landing/'. $pg)
            . view('headers_footers/footer_landing');
    }

    public function landing_error_404($pg = 'landing_404'){
        $data['page_title'] = '404 Not Found | Prj Images';
        $data['page_desc']  = 'The page you are looking for could not be found on Prj Images.';
        $data['page_keys']  = 'page not found, 404, prj images';
        return view('headers_footers/head_landing', $data)
            . view('error/'. $pg)
            . view('headers_footers/footer_landing');
    }
}
