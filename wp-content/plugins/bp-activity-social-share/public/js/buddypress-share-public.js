(function ($) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    $(document).ready(function () {

        $(document).on('click', ".bp-share-button", function (e) {
            $(this).parent().next(".service-buttons").toggle(500);
        });
        $(document).on("click", ".bp-share.has-popup", function (e) {
            var display_attr = $(this).attr('attr-display');
            if ('no-popup' != display_attr) {
                e.preventDefault();
                console.log(display_attr);
                goclicky($(this).attr("href"));
            }
        });
        function FindLeftWindowBoundry() {
            // In Internet Explorer window.screenLeft is the window's left boundry
            if (window.screenLeft) {
                return window.screenLeft;
            }

            // In Firefox window.screenX is the window's left boundry
            if (window.screenX)
                return window.screenX;

            return 0;
        }
        // Find Left Boundry of current Window
        function FindTopWindowBoundry() {
            // In Internet Explorer window.screenLeft is the window's left boundry
            if (window.screenTop) {
                return window.screenTop;
            }

            // In Firefox window.screenY is the window's left boundry
            if (window.screenY)
                return window.screenY;

            return 0;
        }

        function goclicky(meh) {

            console.log(FindLeftWindowBoundry(), FindTopWindowBoundry());
            var x = screen.width / 2 - 700 / 2 + FindLeftWindowBoundry();
            var y = screen.height / 2 - 450 / 2 + FindTopWindowBoundry();
            window.open(meh, '', 'height=485,width=700,left=' + x + ',top=' + y);
        }



        $(document).on('click', ".bp-cpoy", function (e) {
            e.preventDefault();
            var copyText = $(this).data('href');

            document.addEventListener('copy', function (e) {
                e.clipboardData.setData('text/plain', copyText);
                e.preventDefault();
            }, true);

            document.execCommand('copy');
            var tooltip = $(this).next();
            tooltip.removeClass('tooltip-hide');
            setTimeout(function () {
                tooltip.addClass('tooltip-hide');
            }, 500);


        });
    });

})(jQuery);
