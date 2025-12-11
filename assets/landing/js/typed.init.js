// index-business.html
if(document.getElementsByClassName('typed-startup').length > 0) {
    var typed = new Typed('.typed-startup', {
        stringsElement: '#typed-strings',
        typeSpeed: 200,
        loop: true,
        strings: [
            'Agency', 'Software', 'Technology', 'Studio', 'Webapps',
        ],
    });
}