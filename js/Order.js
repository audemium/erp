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
		$('#customPopup2 ul').eq(0).children(':not(:first)').remove();
		$('#customPopup2 .invalid').qtip('destroy', true);
		$('#customPopup2 .invalid').removeClass('invalid');
		resetRecurringOptions('#customPopup2');
		
		$('#customPopup2 [name=itemType]').change(function() {
			var $select = $(this);
			var $ul = $select.closest('ul');
			var itemType = $select.val();
			if (itemType == '') {
				$ul.children(':not(:first)').remove();
				$('#customPopup2 ul').eq(1).hide();
			}
			else {
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
					var html = '<li><label for="itemID">Item</label><select name="itemID"><option value=""></option>';
					$.each(data.options, function(index, value) {
						html += '<option value="' + value.value + '">' + value.text + '</option>';
					});
					html += '</select></li>';
					if ($ul.children().length > 1) {
						$ul.children(':not(:first)').remove();
						resetRecurringOptions('#customPopup2');
					}
					//TODO: find out why these inputs don't line up with the first one
					$ul.append(html);
					if (itemType == 'preDiscount') {
						$('#customPopup2 [name=itemID]').change(function() {
							$.ajax({
								url: 'ajax.php',
								type: 'POST',
								data: {
									'action': 'customAjax',
									'type': type,
									'id': id,
									'subAction': 'list',
									'itemType': 'discount',
									'itemID':  $(this).val()
								}
							}).done(function(data) {
								var html = '<li><label for="discountID">Discount</label><select name="discountID"><option value=""></option>';
								$.each(data.options, function(index, value) {
									html += '<option value="' + value.value + '">' + value.text + '</option>';
								});
								html += '</select></li>';
								if ($ul.children().length > 2) {
									$ul.children().eq(2).remove();
									resetRecurringOptions('#customPopup2');
								}
								$ul.append(html);
							});
						});
					}
					else {
						$ul.append('<li><label for="quantity">Quantity</label><input type="text" name="quantity" autocomplete="off" value="1"></li>');
						$ul.append('<li><label for="recurring">Recurring</label><select name="recurring"><option value="no">No</option><option value="yes">Yes</option></select></li>');
					}
				});
			}
		});
		
		$('#customPopup2').on('change', '[name=recurring]', function() {
			if ($(this).val() == 'yes') {
				$('#customPopup2 ul').eq(1).show();
			}
			else {
				resetRecurringOptions('#customPopup2');
			}
		});
		
		$('#customBtn2').click(function() {
			var ajaxData = $('#customPopup2 input[type!="checkbox"], #customPopup2 select[name!=itemType]').serializeArray();
			var itemType = $('#customPopup2 [name=itemType]').val();
			if (itemType == 'preDiscount') {
				itemType = 'discount';
				$.each(ajaxData, function(index, item) {
					if (item.name == 'dayOfMonth' || item.name == 'interval' || item.name == 'startDate' || item.name == 'endDate') {
						delete ajaxData[index];      
					}
				});
			}
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
			ajaxData = [
				{'name': 'action', 'value': 'customAjax'},
				{'name': 'type', 'value': type},
				{'name': 'id', 'value': id},
				{'name': 'subAction', 'value': 'delete'},
				{'name': 'subType', 'value': $button.data('type')},
				{'name': 'subID', 'value': $button.data('id')}
			];
			if ($button.data('type') == 'discount') {
				ajaxData.push(
					{'name': 'appliesToType', 'value': $button.data('appliestotype')},
					{'name': 'appliesToID', 'value': $button.data('appliestoid')}
				);
			}
			$.ajax({
				url: 'ajax.php',
				type: 'POST',
				data: ajaxData
			}).done(function(data) {
				location.reload();
			});
		}
		event.preventDefault();
	});
});