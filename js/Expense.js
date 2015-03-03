$(document).ready(function() {
	//add
	$('#customAdd1').click(function(event) {
		$('#customPopup1').show();
		$('#customPopup1 input[type!="checkbox"], #customPopup1 select').val('');
		$('#customPopup1 .invalid').qtip('destroy', true);
		$('#customPopup1 .invalid').removeClass('invalid');
		
		$('#customBtn1').click(function() {
			var ajaxData = $('#customPopup1 input[type!="checkbox"], #customPopup1 select').serializeArray();
			ajaxData.push(
				{'name': 'action', 'value': 'customAjax'},
				{'name': 'type', 'value': type},
				{'name': 'id', 'value': id},
				{'name': 'subAction', 'value': 'add'},
				{'name': 'subType', 'value': 'payment'}
			);
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: ajaxData
			}).done(function(data) {
				$('#customPopup1 .invalid').qtip('destroy', true);
				$('#customPopup1 .invalid').removeClass('invalid');
				if (data.status == 'success') {
					location.reload();
				}
				else {
					$.each(data, function(key, value) {
						if (key != 'status') {
							$('#customPopup1 [name=' + key + ']').addClass('invalid');
							$('#customPopup1 [name=' + key + ']').qtip({
								'content': value,
								'style': {'classes': 'qtip-tipsy-custom'},
								'position': {
									'my': 'bottom center',
									'at': 'top center'
								},
								show: {'event': 'focus'},
								hide: {'event': 'blur'}
							});
						}
					});
				}
			});
		});
		event.preventDefault();
	});
	
	$('#customAdd2').click(function(event) {
		$('#customPopup2').show();
		$('#customPopup2 [name=itemType]').val('');
		$('#customPopup2 ul').children(':not(:first)').remove();
		$('#customPopup2 .invalid').qtip('destroy', true);
		$('#customPopup2 .invalid').removeClass('invalid');
		
		$('#customPopup2 [name=itemType]').change(function() {
			var $select = $(this);
			var $ul = $select.closest('ul');
			var itemType = $select.val();
			if (itemType == '') {
				$ul.children(':not(:first)').remove();
			}
			else if (itemType == 'product') {
				$.ajax({
					url: 'ajax.php',
					type: 'POST',
					data: {
						'action': 'customAjax',
						'type': type,
						'id': id,
						'subAction': 'list',
						'itemType': itemType
					}
				}).done(function(data) {
					var html = '<li><label for="productID">Product</label><select name="productID"><option value=""></option>';
					$.each(data.products, function(index, value) {
						html += '<option value="' + value.value + '">' + value.text + '</option>';
					});
					html += '</select></li>';
					html += '<li><label for="locationID">Location</label><select name="locationID"><option value=""></option>';
					$.each(data.locations, function(index, value) {
						html += '<option value="' + value.value + '">' + value.text + '</option>';
					});
					html += '</select></li>';
					if ($ul.children().length > 1) {
						$ul.children(':not(:first)').remove();
					}
					//TODO: find out why these inputs don't line up with the first one
					$ul.append(html);
					$ul.append('<li><label for="unitPrice">Unit Price</label><input type="text" name="unitPrice" autocomplete="off"></li>');
					$ul.append('<li><label for="quantity">Quantity</label><input type="text" name="quantity" autocomplete="off" value="1"></li>');
				});
			}
			else {
				var html = '<li><label for="name">Name</label><input type="text" name="name" autocomplete="off"></li>';
				html += '<li><label for="unitPrice">Unit Price</label><input type="text" name="unitPrice" autocomplete="off"></li>';
				html += '<li><label for="quantity">Quantity</label><input type="text" name="quantity" autocomplete="off" value="1"></li>';
				if ($ul.children().length > 1) {
					$ul.children(':not(:first)').remove();
				}
				//TODO: find out why these inputs don't line up with the first one
				$ul.append(html);
			}
		});
		
		$('#customBtn2').click(function() {
			var ajaxData = $('#customPopup2 input[type!="checkbox"], #customPopup2 select[name!=itemType]').serializeArray();
			var itemType = $('#customPopup2 [name=itemType]').val();
			itemType = (itemType == 'preDiscount') ? 'discount' : itemType;
			ajaxData.push(
				{'name': 'action', 'value': 'customAjax'},
				{'name': 'type', 'value': type},
				{'name': 'id', 'value': id},
				{'name': 'subAction', 'value': 'add'},
				{'name': 'subType', 'value': itemType}
			);
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: ajaxData
			}).done(function(data) {
				$('#customPopup2 .invalid').qtip('destroy', true);
				$('#customPopup2 .invalid').removeClass('invalid');
				if (data.status == 'success') {
					location.reload();
				}
				else {
					$.each(data, function(key, value) {
						if (key != 'status') {
							$('#customPopup2 [name=' + key + ']').addClass('invalid');
							$('#customPopup2 [name=' + key + ']').qtip({
								'content': value,
								'style': {'classes': 'qtip-tipsy-custom'},
								'position': {
									'my': 'bottom center',
									'at': 'top center'
								},
								show: {'event': 'focus'},
								hide: {'event': 'blur'}
							});
						}
					});
				}
			});
		});
		event.preventDefault();
	});
	
	//edit
	$('.customTable .controlEdit').click(function(event) {
		var $button = $(this);
		if ($button.hasClass('editEnabled')) {
			$('#customPopup3').show();
			$('#customPopup3 [name=unitPrice]').val($button.data('unitprice'));
			$('#customPopup3 [name=quantity]').val($button.data('quantity'));
			
			$('#customBtn3').click(function() {
				$.ajax({
					url: 'ajax.php',
					type: 'POST',
					data: {
						'action': 'customAjax',
						'type': type,
						'id': id,
						'subAction': 'edit',
						'subType': $button.data('type'),
						'subID':  $button.data('id'),
						'unitPrice': $('#customPopup3 [name=unitPrice]').val(),
						'quantity': $('#customPopup3 [name=quantity]').val()
					}
				}).done(function(data) {
					location.reload();
				});
			});
		}
		event.preventDefault();
	});

	//delete
	$('.customTable .controlDelete').click(function(event) {
		var $button = $(this);
		if ($button.hasClass('deleteEnabled')) {
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: {
					'action': 'customAjax',
					'type': type,
					'id': id,
					'subAction': 'delete',
					'subType': $button.data('type'),
					'subID':  $button.data('id')
				}
			}).done(function(data) {
				location.reload();
			});
		}
		event.preventDefault();
	});
});