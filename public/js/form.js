$(function () {

	/**
	 * Basic form init
	 */

	var $urlInput = $('input[name*="Url"]');
	var fetchUrl = $urlInput.closest('form').data('fetch-url');
	console.info(fetchUrl);

	$urlInput.on('paste', function () {
		setTimeout(fetchRecipeDataFromUrl, 0);
	});

	function fetchRecipeDataFromUrl() {
		$urlInput.closest('.input').addClass('loading');
		$.get(fetchUrl, {url: $urlInput.val()}, function (response) {
			console.info(response);
			$('#form_label').val(response.title);
			$('#form_description').val(response.description);
			$('#form_effort').val(response.effort);

			clearIngredients();
			response.ingredients.forEach(function (ingredient) {
				addIngredient(ingredient.amount + ' ' + ingredient.label);
			});

			response.images.forEach(function (imgUrl) {
				if (!imgUrl) {
					return;
				}

				var input = '<input type="hidden" name="images[]" value="' + imgUrl + '" disabled>';
				var toggleIcon = '<i class="white check circle icon">';
				$('.rec-images.grid').append('<div class="column"><img src="' + imgUrl + '" />' + input + toggleIcon + '</div>');
			});

			$urlInput.closest('.input').removeClass('loading');
		});
	}

	$('#form_tagsString').selectize({
		delimiter: ',',
		persist: false,
		options: $('#form_tagsString').data('tags-json'),
		create: function (input) {
			return {
				value: input,
				text: input
			}
		}
	});

	$('body').on('click', '.rec-images.grid img', function () {
		var $input = $(this).closest('.column').find('input');
		$(this).closest('.column').toggleClass('active');

		if ($input.attr('disabled'))
			$input.removeAttr('disabled');
		else
			$input.attr('disabled', 'disabled');
	});

	$('#form_files').on('change', function () {
		imagesPreview(this, 'div.gallery');
	});

	// Multiple images preview in browser
	var imagesPreview = function (input, placeToInsertImagePreview) {
		if (input.files) {
			var filesAmount = input.files.length;
			for (var i = 0; i < filesAmount; i++) {
				var reader = new FileReader();
				reader.onload = function (event) {
					$('<div class="column"><img src="' + event.target.result + '"></div>').appendTo(placeToInsertImagePreview);
				};
				reader.readAsDataURL(input.files[i]);
			}
		}
	};

	/**
	 * Ingredient handling
	 */

	function clearIngredients() {
		$('.ingredients [data-prototype]').empty();
	}

	function addIngredient($list, value) {
		var counter = $list.data('widget-counter') | $list.children().length;
		if (!counter) {
			counter = $list.children().length;
		}

		var newWidget = $list.attr('data-prototype');
		newWidget = newWidget.replace(/__ingredientcounter__/g, counter);
		counter++;
		$list.data(' widget-counter', counter);

		var $newElem = $(newWidget);
		$newElem.find('input').val(value);
		$newElem.appendTo($list);
	}

	function addIngredientGroup(value) {
		var $list = $('#form_ingredientGroups');
		var counter = $list.data('widget-counter') | $list.children().length;
		if (!counter) {
			counter = $list.children().length;
		}

		var newWidget = $list.attr('data-prototype');
		newWidget = newWidget.replace(/__groupcounter__/g, counter);
		counter++;
		$list.data('widget-counter', counter);

		var $newIngredientGroup = $(newWidget);
		$newIngredientGroup.find('input').val(value);
		$newIngredientGroup.appendTo($list);
		addIngredient($newIngredientGroup.find('.ingredientList'));
	}

	$('body').on('keydown', '.ingredients input', function () {
		console.info($(this).closest('.ingredient'));
		if ($(this).closest('.ingredient').is(':last-child')) {
			addIngredient($(this).closest('.ingredientList'));
		}
	});
	$('body').on('keydown', '.groupLabelInput input', function () {
		if ($(this).closest('.ingredientgroup').is(':last-child')) {
			addIngredientGroup();
		}
	});

	$('body').on('click', '.remove-row', function (e) {
		e.preventDefault();
		$(this).closest('.removable').slideUp(function () {
			$(this).remove();
		});
	});

	$('.ingredientList').each(function (index, element) {
		new Sortable(element, {
			group: "sortable-ingredients",
			sort: true,
			animation: 150,
			handle: ".bars",
			draggable: ".ingredient",
			onSort: resetIngredientIds
		});
	});

	new Sortable(document.getElementsByClassName('ingredientGroupList')[0], {
		// sort: true,
		handle: ".ingredientGroupDragHandle",
		animation: 150,
		draggable: ".ingredientgroup",
		onSort: resetIngredientIds
	});

	/**
	 * Loops over all ingredientGroups and ingredients and sets new ids in the names of the inputs.
	 */
	function resetIngredientIds() {
		$('.ingredientgroup').each(function (groupIndex, groupElement) {
			console.info('replacing group:', groupElement);
			var groupPrefix = 'form[ingredientGroups][' + groupIndex + ']';
			$(groupElement).find('input[name^="form[ingredientGroups]"]').each(function (inputIndex, input) {
				var newName = $(input).attr('name').replace(/form\[ingredientGroups\]\[\d+\]/, groupPrefix);
				$(input).attr('name', newName);
			});

			$(groupElement).find('input[name*="[ingredients]"]').each(function (ingredientIndex, input) {
				var newName = $(input).attr('name').replace(/\[ingredients\]\[\d+\]/, '[ingredients][' + ingredientIndex + ']');
				console.info('replacing ingredient:', ingredientIndex, newName);
				$(input).attr('name', newName);
			});
		});
	}

	// add empty inputs for easier adding
	$('.ingredientList').each(function (index, elem) {
		addIngredient($(elem));
	});
	addIngredientGroup();
});