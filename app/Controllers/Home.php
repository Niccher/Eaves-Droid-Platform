<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string{
        //return view('welcome_message'); 
	    $titl['pag'] = 'landing';
	    return view('headers_footers/head_landing')
		    . view('headers_footers/sidebar_landing', $titl)
		    . view('landing/landing')
		    . view('headers_footers/footer_landing');
    }

	public function landing_download($pg = 'download'){
		$titl['pag'] = 'download';

		return view('headers_footers/head_landing')
			. view('headers_footers/sidebar_landing', $titl)
			. view('landing/'. $pg)
			. view('headers_footers/footer_landing');
	}

	public function landing_aboutus($pg = 'about'){
		$titl['pag'] = 'about';

		return view('headers_footers/head_landing')
			. view('headers_footers/sidebar_landing', $titl)
			. view('landing/'. $pg)
			. view('headers_footers/footer_landing');
	}


	public function landing_contactus($pg = 'contact'){
		$titl['pag'] = 'contact';
		$data['info'] = '';

		return view('headers_footers/head_landing')
			. view('headers_footers/sidebar_landing', $titl)
			. view('landing/'. $pg, $data)
			. view('headers_footers/footer_landing');
	}

	public function landing_prices($pg = 'prices'){
		$titl['pag'] = 'prices';

		return view('headers_footers/head_landing')
			. view('headers_footers/sidebar_landing', $titl)
			. view('landing/'. $pg)
			. view('headers_footers/footer_landing');
	}

	public function landing_faqs($pg = 'faq'){
		$titl['pag'] = 'faqs_terms';

		return view('headers_footers/head_landing')
			. view('headers_footers/sidebar_landing', $titl)
			. view('landing/'. $pg)
			. view('headers_footers/footer_landing');
	}

	public function landing_how_to($pg = 'howto'){
		$titl['pag'] = 'faqs_terms';

		return view('headers_footers/head_landing')
			. view('headers_footers/sidebar_landing', $titl)
			. view('landing/landing/'. $pg)
			. view('headers_footers/footer_landing');
	}
	public function landing_error_404($pg = 'landing_404'){
		return view('headers_footers/head_landing')
			. view('error/'. $pg)
			. view('headers_footers/footer_landing');
	}
}
