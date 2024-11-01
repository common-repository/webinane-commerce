(function($){

	var WPMC_add_to_cart = {

		el: '',
		res: {
			msg: '',
			type: 'error'
		},
		init: function() {

			$('.wpcm-add-cart-button.button-ajax').on('click', '.ajax_add_to_cart', (e) => {
				this.add_to_cart(e)
			});
		},

		add_to_cart: function(e) {
			e.preventDefault();
			

			var thisis = e.target,
			parent = $(thisis).parent(),
			id = $(thisis).attr('id'),
			data = wpcm_cart_button[id];

			this.el = thisis

			if ( data === undefined ) {
				alert('no data provide');
				return;
			}

			$.extend(data, {subaction: 'ajax_add_to_cart', is_ajax: true, action: wpcm_data.ajax_action});

			parent.find('.wpcm-ajax-processing').removeClass('hide');

			$.ajax({
				url: wpcm_data.ajaxurl,
				type: 'POST',
				data: data,
				success: (res) => {
					if( res.success ) {
						this.res = {
							type: 'success',
							msg: res.message
						}
					} else {
						this.res = {
							type: 'error',
							msg: res.message
						}
					}
					parent.find('.wpcm-ajax-processing').addClass('hide');

					this.notif()

					if( res.success && res.redirect ) {
						window.location = res.redirect
					}
				},
				fail: (res) => {
					parent.find('.wpcm-ajax-processing').addClass('hide');
				}
			});
		},
		notif() {
			let parent = $(this.el).parent()

			if( ! parent.find('.action-response').length ) {
				parent.prepend('<div class="action-response"></div>')
			}

			parent.find('.action-response')
			.addClass(this.res.type)
			.html(this.res.msg)
			.slideDown()

			setTimeout(() => {
				parent.find('.action-response').slideUp()
			}, 10000)
		}
	};

	$(document).ready(WPMC_add_to_cart.init());
})(window.jQuery);