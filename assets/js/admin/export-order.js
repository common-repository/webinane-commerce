(function ($) {
	$(document).ready(function () {
		$("#wpcommerce-exporter-date1, #wpcommerce-exporter-date2").datepicker();

		$("form.wpcommerce-exporter").submit(function (e) {
			e.preventDefault();
			var columns = $("#wpcommerce-exporter-columns").val();
			var status = $("#wpcommerce-exporter-types").val();
			var st_date = $("#wpcommerce-exporter-date1").val();
			var nd_date = $("#wpcommerce-exporter-date2").val();
			$.ajax({
				url: wpcm_data.ajaxurl,
				type: 'POST',
				data: {
					action: 'wpcm_export_orders',
					columns: columns,
					status: status,
					st_date: st_date,
					nd_date: nd_date,
				},
				beforeSend: function () {
					$(".wpcommerce-exporter-button > span").show();
				},
				success: function (res) {
					$(".wpcommerce-exporter-button > span").hide();
					if(res.success) {
						window.location.href = res.data.url;
					} else {
						alert(res.data.message)
					}

				},
				fail: function(res) {
					$(".wpcommerce-exporter-button > span").hide();
					alert(res)
				}
			});
		});
	});
})(jQuery);
