// Sample JavaScript code, make sure to adapt it based on your needs
jQuery(document).ready(function($) {
    $('.nav-tab').click(function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        var tab = $(this).attr('href').split('&tab=')[1];
        if(tab == 'gtm') {
            // Show GTM settings and hide events
        } else if(tab == 'events') {
            // Show events and hide GTM settings
        }
    });
});
