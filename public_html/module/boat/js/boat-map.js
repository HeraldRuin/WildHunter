var mapEngine = new BCMapEngine('bc_results_map',{
    fitBounds:bookingCore.map_options.map_fit_bounds,
    center:[bc_map_data.map_lat_default, bc_map_data.map_lng_default ],
    zoom:bc_map_data.map_zoom_default,
    disableScripts:true,
    markerClustering:bookingCore.map_options.map_clustering,
    ready: function (engineMap) {
        if(bc_map_data.markers){
            engineMap.addMarkers2(bc_map_data.markers);
        }
    }
});
jQuery(function ($) {


	$(".bc-filter-price").each(function () {
		var input_price = $(this).find(".filter-price");
		var min = input_price.data("min");
		var max = input_price.data("max");
		var from = input_price.data("from");
		var to = input_price.data("to");
		var symbol = input_price.data("symbol");
		input_price.ionRangeSlider({
			type: "double",
			grid: true,
			min: min,
			max: max,
			from: from,
			to: to,
			prefix: symbol
		});
	});

	$('.bc_form_search_map .smart-search .child_id').change(function () {
		reloadForm();
	});
    $('.bc_form_search_map .g-map-place input[name=map_place]').change(function () {
        setTimeout(function () {
            reloadForm()
        },500)
    });

	$('.bc_form_search_map .input-filter').change(function () {
		reloadForm();
	});
	$('.bc_form_search_map .btn-filter,.btn-apply-advances').click(function () {
		reloadForm();
	});
	$('.btn-apply-advances').click(function(){
		$('#advance_filters').addClass('d-none');
	})

	function reloadForm(){
		$('.map_loading').show();
		$.ajax({
			data:$('.bc_form_search_map input,select,textarea,input:hidden,#advance_filters input,select,textarea').serialize()+'&_ajax=1',
			url:window.location.href.split('?')[0],
			dataType:'json',
			type:'get',
			success:function (json) {
				$('.map_loading').hide();
				if(json.status)
				{
					mapEngine.clearMarkers();
					mapEngine.addMarkers2(json.markers);

					$('.bc-list-item').replaceWith(json.html);

					$('.listing_items').animate({
                        scrollTop:0
                    },'fast');

					if(window.lazyLoadInstance){
						window.lazyLoadInstance.update();
					}

				}

			},
			error:function (e) {
				$('.map_loading').hide();
				if(e.responseText){
					$('.bc-list-item').html('<p class="alert-text danger">'+e.responseText+'</p>')
				}
			}
		})
	}

	function reloadFormByUrl(url){
        $('.map_loading').show();
        $.ajax({
            url:url,
            dataType:'json',
            type:'get',
            success:function (json) {
                $('.map_loading').hide();
                if(json.status)
                {
                    mapEngine.clearMarkers();
                    mapEngine.addMarkers2(json.markers);

                    $('.bc-list-item').replaceWith(json.html);

					setTimeout(function () {
						$('.listing_items').animate({
							scrollTop:0
						},'fast');
						if($(document).width() < 991){
							$('html,body').animate({
								scrollTop: $(".listing_items").offset().top - 50
							},'fast');
						}
					},500);

                    if(window.lazyLoadInstance){
                        window.lazyLoadInstance.update();
                    }
                }

            },
            error:function (e) {
                $('.map_loading').hide();
                if(e.responseText){
                    $('.bc-list-item').html('<p class="alert-text danger">'+e.responseText+'</p>')
                }
            }
        })
	}

	$('.toggle-advance-filter').click(function () {
		var id = $(this).data('target');
		$(id).toggleClass('d-none');
	});

    $(document).on('click', '.filter-item .dropdown-menu', function (e) {

        if(!$(e.target).hasClass('btn-apply-advances')){
            e.stopPropagation();
		}
    })
		.on('click','.bc-pagination a',function (e) {
			e.preventDefault();
            reloadFormByUrl($(this).attr('href'));
        })
	;

});
