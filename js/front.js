jQuery(document).ready(function(){
	jQuery("#area-point-selected #redbox_point").attr("readonly", "readonly");
	let REDBOX_METHOD_SELECTED = false
	let REDBOX_FIRST_CHOOSE = true
	let REDBOX_POINT_SELECTED = false
	let REDBOX_LIST_POINT = []
	let REDBOX_LABEL = {
		en: {
			pick_up_success: "Selected RedBox Point successfully",
			select_point: "Ship to this location",
			label_accept_payment: "Accept payment",
			label_time_delivery: "Estimate time delivery",
			label_accept_payment_yes: "Yes",
			label_accept_payment_no: "No",
			label_restricted_access: "Restried access",
			label_confirm_can_access_retricted_area: "I confirm that i have access to this location",
			day: "days",
			select_other_point: "Select other Pickup point",
			select_a_point: "Select a Pickup point",
			counter_locker: "Locker and Counter",
			counter: "Counter",
			locker: "Locker"
		},
		ar: {
			pick_up_success: "تم الإختيار، اضعط على تأكيد الخزانة",
			select_point: "الشحن الى",
			label_accept_payment: "يقبل الدفع الإلكتروني",
			label_time_delivery: "الوقت المتوقع للتوصيل",
			label_accept_payment_yes: "نعم",
			label_accept_payment_no: "لا",
			label_restricted_access: "خزانة خاصة",
			label_confirm_can_access_retricted_area: "نعم، لدي تصريح للوصول لموقع الخزانة",
			day: "أيام",
			select_other_point: "اختر نقطة التقاط أخرى",
			select_a_point: "اختر نقطة الاستلام",
			counter_locker: "خزانة وكاونتر",
			counter: "كاونتر",
			locker: "خزانة"
		}
	}
	let REDBOX_MAP = false
	function getEstimateTime (hours, locale) {
        var days = hours / 24;
        if (days < 2) {
          return '1-2 ' + REDBOX_LABEL[locale].day;
        } else {
          var floor10 = Math.floor(days);
          return `${floor10}-${floor10 + 1}` + REDBOX_LABEL[locale].day;
        }
      }
	function unSelectPoint() {
		jQuery('.list-point .per-point-selected') ? jQuery('.list-point .per-point-selected').removeClass('per-point-selected') : null
	}
	function activeLabelPoint() {
		let locale = jQuery('.redbox').attr("lang") == "ar" ? "ar" : "en"
		jQuery('.bt-pick-point').off()
		jQuery('.bt-pick-point').click(function(){
			unSelectPoint()
			jQuery(this).parent().parent().addClass("per-point-selected")
			REDBOX_POINT_SELECTED = jQuery(this).parent().parent().attr('point-id')
			let pointInfo = jQuery(this).parent().parent().attr('value')
			var latLng = new mapkit.Coordinate(parseFloat(jQuery(this).parent().parent().attr('lat')), parseFloat(jQuery(this).parent().parent().attr('lng')))
			REDBOX_MAP.setCenterAnimated(latLng)
			jQuery("#area-point-selected #redbox_point").val(pointInfo)
			if (jQuery("#shipping_address_1").length) {
				jQuery("#shipping_address_1").val(pointInfo)
			}
			jQuery("#area-point-selected #redbox_point_id").val(REDBOX_POINT_SELECTED)
			jQuery('.redbox').addClass("redbox-hide")
			jQuery("#area-point-selected .bt-change-point").text(REDBOX_LABEL[locale].select_other_point)
			jQuery("#area-point-selected #redbox_point_field").show()
		});
	}
	function callAjaxGetListPoint(lat, lng) {
		jQuery.ajax({
			url: ajax_url,
			type:'GET',
			data:`action=getlispoint&lat=${lat}&lng=${lng}&distance=100000000`,
			success : function(response) {
                let data = JSON.parse(response)
                if (data.success) {
	                REDBOX_LIST_POINT = data.points
	                drawMapInThis(data.points, lat, lng)
	                jQuery('.redbox-waiting-response').remove()
	            } else {
	            	alert(data.msg)
	            }
			},
			error: function(e) {
				alert('Error occured');
			}
		});
	}
	function openModalRedbox() {
		jQuery('.redbox').removeClass("redbox-hide")
		if (REDBOX_FIRST_CHOOSE) {
			let LAT = 24.7135517
			let LNG = 46.6752957
			if (REDBOX_LIST_POINT.length) {
                drawMapInThis(REDBOX_LIST_POINT, LAT, LNG)
                jQuery('.redbox-waiting-response').remove()
			} else {
				callAjaxGetListPoint(LAT, LNG)
			}
		}
		jQuery('#close-modal-redbox').off()
		jQuery('#close-modal-redbox').click(function(){
			jQuery('.redbox').addClass("redbox-hide")
			let locale = jQuery('.redbox').attr("lang") == "ar" ? "ar" : "en"
			if (!REDBOX_POINT_SELECTED) {
				jQuery("#area-point-selected .bt-change-point").text(REDBOX_LABEL[locale].select_a_point)
				jQuery("#area-point-selected #redbox_point_field").hide()
			}
		});
	}

	function activeInputConfirm() {
		jQuery('.input-confirm-access-restricted').off()
		jQuery('.input-confirm-access-restricted').click(function(){
			if (jQuery(this).prop("checked") == true) {
				jQuery(this).parent().parent().parent().parent().next().next().find('button').removeAttr("disabled")
			} else {
				jQuery(this).parent().parent().parent().parent().next().next().find('button').attr("disabled", true)
			}
		});
	}

	function setClickCloseChoosePoint() {
		jQuery('#close-wrap-choose-point').off()
		jQuery('#close-wrap-choose-point').click(function(){
			jQuery( "#wrap-area-choose-point" ).animate({
			    bottom: -400
			}, 500, function() {
			    // Animation complete.
			});
		});
	}

	function renderListPoint(point) {
		const pathImage = jQuery('.redbox').attr("path")
		let locale = jQuery('.redbox').attr("lang") == "ar" ? "ar" : "en"
		let acceptPayment = point.lockers && point.lockers.find(e => e.accept_payment == true) ? REDBOX_LABEL[locale].label_accept_payment_yes : REDBOX_LABEL[locale].label_accept_payment_no
		let isRestrict = !point.is_public
		let msgRestrict = ''
		if (isRestrict) {
			msgRestrict = point.alert_message[locale] 
		}
		point.icon = "https://app.redboxsa.com/icon_locker.png"
		point.label_type = REDBOX_LABEL[locale].locker
		if (point.type_point == "Both") {
			point.icon = "https://app.redboxsa.com/icon_counter_locker.png"
			point.label_type = REDBOX_LABEL[locale].counter_locker
		} else if (point.type_point == "Counter") {
			point.icon = "https://app.redboxsa.com/icon_counter.png"
			point.label_type = REDBOX_LABEL[locale].counter
		}
		let htmlRestrict = isRestrict ? `
			<div class="step-i">
				<div class="step-i-1"><i class="fa fa-ban"></i></div>
    			<span>
    				<b>${REDBOX_LABEL[locale].label_restricted_access}:</b>${msgRestrict}
    				<label class="container-checkbox">
    					${REDBOX_LABEL[locale].label_confirm_can_access_retricted_area}
						<input class="input-confirm-access-restricted" type="checkbox">
						<small class="checkmark"></small>
					</label>
    			</span>
			</div>
		` : ""
		return `
			<div class="list-point">
				<div class="per-point ${point.id == REDBOX_POINT_SELECTED ? "per-point-selected" : ""}" point-id="${point.id}" city="${point.address.city}" value="${point.point_name} - ${point.address.city} - ${point.address.district} - ${point.address.street}" lat="${point.location.lat}" lng=${point.location.lng}>
	    			<span class="close-point-selected" id="close-wrap-choose-point" >
	    				<img src="${pathImage}/image_plugin/close_circle.png">
	    			</span>
	    			<div class="step1">
	    				<img src="${point.icon}">
	    				<div class="step-name">
	    					<span class="name-1">${jQuery('.redbox').attr('lang') == "ar" ? point.host_name_ar : point.host_name_en}</span>
	    					<span class="name-2">
	    						<b>${point.label_type}</b>
	    						- ${point.point_name}
	    					</span>
	    				</div>
	    			</div>
	    			<div class="step-2">
	    				<div class="step-i">
	    					<div class="step-i-1">
	    						<img src="${pathImage}/image_plugin/location.png">
	    					</div>
	    					<span>${point.address.city} - ${point.address.district} - ${point.address.street}</span>
	    				</div>
	    				<div class="step-i">
	    					<div class="step-i-1">
	    						<img src="${pathImage}/image_plugin/clock.png">
	    					</div>
	    					<span>${point.open_hour}</span>
	    				</div>
	    				<div class="step-i">
	    					<div class="step-i-1">
	    						<img src="${pathImage}/image_plugin/truck.png">
	    					</div>
	    					<span>${REDBOX_LABEL[locale].label_time_delivery}: ${getEstimateTime(point.estimateTime, locale)}</span>
	    				</div>
	    				${isRestrict ? htmlRestrict : ""}
	    			</div>
	    			<div class="selected-point-success">
	    				${REDBOX_LABEL[locale].pick_up_success}
	    			</div>
	    			<div class="area-select">
	    				<button type="button" ${isRestrict ? "disabled" : ""} class="bt-pick-point">${REDBOX_LABEL[locale].select_point}</button>
	    			</div>
	    		</div>
	    	</div>
		`
	}
	function drawMapInThis(points, lat, lng) {
		REDBOX_FIRST_CHOOSE = false
		let center = {
			lat: lat, lng: lng
		}
		if (!REDBOX_MAP) {
			map = new mapkit.Map("area-map", {
				showsUserLocationControl: true
			});
			var latLng = new mapkit.Coordinate(lat, lng)
			map.setCenterAnimated(latLng)
			map._impl.zoomLevel = 5

			points.map(e => {
				if (e.type_point === 'Locker') {
					if (e.status == "LockTemporary"){
						e.icoPath = `${redbox.stylesheetUri}/images/marker_locked.svg`;
					} else {
						e.icoPath = `${redbox.stylesheetUri}/images/marker_locker.svg`;
					}
				} else if (e.type_point === 'Counter') {
					e.icoPath = `${redbox.stylesheetUri}/images/marker_counter.svg`;
				} else {
					e.icoPath = `${redbox.stylesheetUri}/images/marker_counter_locker.svg`;
				}
				e.selectedPath = `${redbox.stylesheetUri}/images/marker_selected.svg`
				const marker = new mapkit.ImageAnnotation(new mapkit.Coordinate(e.location.lat, e.location.lng), {
					url: {
						1: e.icoPath
					},
					title: '',
					data: e,
					anchorOffset: new DOMPoint(0, -8)
				});
				map.addAnnotation(marker);
				setClickMarker(marker, e.id)
			})
			REDBOX_MAP = map
			setFindAreaMap(map);
		}
	}
	function reRenderMapWithPoint(lat, lng) {
		var htmlWaiting = '<div class="redbox-waiting-response"><i class="fa fa-spinner fa-spin"></i></div>'
		if (lat && lng) {
			var latLng = new mapkit.Coordinate(lat, lng)
			REDBOX_MAP.setCenterAnimated(latLng)
		}
		jQuery( "#wrap-area-choose-point" ).animate({
		    bottom: -400
		}, 500);
	}
	function setFindAreaMap(map) {
		let search = new mapkit.Search({region: map.region});
		let timeout;
		let delay = 250;
		jQuery('#pac-input').keyup(function () {
			console.log("dd")
			if (timeout) clearTimeout(timeout);
			timeout = setTimeout(function () {
				// Make sure it's not a zero length string
				if (jQuery('#pac-input').val().length > 0) {
					search.autocomplete(jQuery('#pac-input').val(), (error, data) => {
						if (error) {
							return;
						}
						// Unhide the result box
						jQuery('#results').show();
						var results = "";
						// Loop through the results a build
						data.results.forEach(function (result) {
							if (result.coordinate) {
								// Builds the HTML it'll display in the results. This includes the data in the attributes so it can be used later
								results = results + '<div class="mapSearchResultsItem" data-title="' + result.displayLines[0] + '" data-latitude="' + result.coordinate.latitude + '" data-longitude="' + result.coordinate.longitude + '" data-address="' + result.displayLines[1] + '"><b>' + result.displayLines[0] + '</b> ' + (result.displayLines[1] ? result.displayLines[1] : '') + '</div>';
							}
						});
						// Display the results
						jQuery('#results').html(results);
						// List for a click on an item we've just displayed
						jQuery('.mapSearchResultsItem').click(function () {
							jQuery('#pac-input').val(jQuery(this).data('title'));
							// Get all the data - you might want to write this into form fields on your page to capture the data if this map is part of a form.
							var latitude = jQuery(this).data('latitude');
							var longitude = jQuery(this).data('longitude');
							var myRegion = new mapkit.CoordinateRegion(
								new mapkit.Coordinate(latitude, longitude),
								new mapkit.CoordinateSpan(0.05, 0.05)
							);
							map.region = myRegion;
							jQuery('#results').hide();
							acceptChooseLastSelectedPoint = null
							getPoint(latitude, longitude)
						});
					});
				} else {
					jQuery('#results').hide();
				}
			}, delay);
		});
	}
	function setClickMarker(marker, id) {
		marker.addEventListener("select", function(event) {
			jQuery( "#wrap-area-choose-point" ).animate({
			    bottom: 0
			}, 500, function() {
			    // Animation complete.
			});
			var point = REDBOX_LIST_POINT.find(e => e.id == id)
			if (point) {
				let htmlPoint = renderListPoint(point)
				jQuery( "#wrap-area-choose-point .list-point" ).remove()
				jQuery( "#wrap-area-choose-point" ).append(htmlPoint)
				activeLabelPoint()
				setClickCloseChoosePoint()
				activeInputConfirm()
			}
		});
	}

	function loadListPoint() {
		if (!REDBOX_LIST_POINT.length) {
			let LAT = 24.7135517
			let LNG = 46.6752957
			jQuery.ajax({
				url: ajax_url,
				type:'GET',
				data:`action=getlispoint&lat=${LAT}&lng=${LNG}&distance=100000000`,
				success : function(response) {
	                let data = JSON.parse(response)
	                if (data.success) {
		                REDBOX_LIST_POINT = data.points
		            } else {
		            	alert(data.msg)
		            }
				},
				error: function(e) {
					alert('Error occured');
				}
			});
		}
	}

	jQuery('#area-point-selected .bt-change-point').click(function(){
		openModalRedbox()
	});
	jQuery( document.body ).on( 'updated_checkout', function(){
		const elementRedboxPickup = jQuery('#shipping_method input[value=redbox_pickup_delivery]')
		if (elementRedboxPickup && elementRedboxPickup.attr('type') == 'hidden') {
			REDBOX_METHOD_SELECTED = true
			jQuery("#area-point-selected").show()
		} else {
			if (jQuery('#shipping_method input:checked').val() == "redbox_pickup_delivery") {
				if (!REDBOX_METHOD_SELECTED) {
					openModalRedbox()
					REDBOX_METHOD_SELECTED = true
					jQuery("#area-point-selected").show()
				}
			} else {
				REDBOX_METHOD_SELECTED = false
				jQuery("#area-point-selected").hide()
				if (!REDBOX_LIST_POINT.length) {
					loadListPoint()
				}
			}
		}
	});
});
