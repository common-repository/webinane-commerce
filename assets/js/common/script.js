jQuery(document).ready(function ($) {

	"use strict";

    // date picker

    if( $('#wpcm-datepicker').length) {
	    $('#wpcm-datepicker').datetimepicker({
	    	sideBySide: true
	    });
    }


    // text editor
    if ($.isFunction($.fn.froalaEditor)) {
    	$('.wpcm-text-editor').froalaEditor({
    		pluginsEnabled: ['paragraphFormat', 'fullscreen', 'align', 'outdent', 'indent', 'lists', 'undo', 'redo', 'quote', 'insertLink', 'help'],
    		toolbarButtons: ['paragraphFormat', 'bold', 'italic', 'underline', 'formatUL', 'formatOL', 'quote', 'align', 'insertLink', 'fullscreen', 'strikeThrough', 'clearFormatting', 'outdent', 'indent', 'undo', 'redo', 'help'],
    		heightMin: 209,
    		placeholderText: ''

    	});
    }

    // subtabs

    $('.wpcm-tabs-group li a').click(function(){
    	if($(this).is('.submenu')) {
    		$('.wpcm-subtabs-group').toggleClass('active');
    	}
    	else{
    		$('.wpcm-subtabs-group').removeClass('active');
    	}
    });

    // payment method
    
    $("input[name$='payment']").click(function() {
    	var payment_type = $(this).val();

    	$(".wpcm-payment-box").hide();
    	$("#" + payment_type).show();
    });


    // Touch Spin cart qty number

    if ($.isFunction($.fn.TouchSpin)) {
    	$('.qty').TouchSpin({});
    }

    // close and open tab

    $( '.wpcm-close-open' ).on( 'click', function(e){
        e.preventDefault();;
        $(this).parents('.wpcm-tab-area').find('.wpcm-option-row').slideToggle('slow','linear');
    });

    // Order Popup
    $( '.wpcm-order-info' ).on( 'click', function(){
        $( '.wpcm-modal' ).addClass( 'wpcm-show-model' );
        $( 'body' ).addClass( 'wpcm-modal-visible' );
        return false;
    });

    $( '.wpcm-modal-overlay, .wpcm-model-close' ).on( 'click', function(){

        if ( $( 'body' ).hasClass( 'wpcm-modal-visible' ) ) {
            $( 'body' ).removeClass( 'wpcm-modal-visible' );
            $( '.wpcm-modal' ).removeClass( 'wpcm-show-model' );
        }

    });


});  