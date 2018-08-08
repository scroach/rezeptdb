$(function () {

	/**
	 * Basic form init
	 */

	let $urlInput = $('input[name*="Url"]');
	let fetchUrl = $urlInput.closest('form').data('fetch-url');
	let $body = $('body');
	let $formTagsString = $('#form_tagsString');
	let $ingredientList = $('.ingredientList');

	$urlInput.on('paste', function () {
		setTimeout(fetchRecipeDataFromUrl, 0);
	});

	function fetchRecipeDataFromUrl() {
		$urlInput.closest('.input').addClass('loading');
		/**
		 * @param {{effort:string, title:string, description:string, ingredients:array, images:array}} response
		 */
		$.get(fetchUrl, {url: $urlInput.val()}, function (response) {
			console.info(response);
			$('#form_label').val(response.title);
			$('#form_description').val(response.description);
			$('#form_effort').val(response.effort);

			clearIngredients();
			response.ingredients.forEach(function (ingredient) {
				addIngredient($('.ingredientList:first'), ingredient.amount + ' ' + ingredient.label);
			});

			response.images.forEach(function (imgUrl) {
				if (!imgUrl) {
					return;
				}

				let input = '<input type="hidden" name="images[]" value="' + imgUrl + '" disabled>';
				let toggleIcon = '<i class="white check circle icon">';
				$('.rec-images.grid').append('<div class="column"><img src="' + imgUrl + '" />' + input + toggleIcon + '</div>');
			});

			$urlInput.closest('.input').removeClass('loading');
		});
	}

	$formTagsString.selectize({
		delimiter: ',',
		persist: false,
		options: $formTagsString.data('tags-json'),
		create: function (input) {
			return {
				value: input,
				text: input
			}
		}
	});

	$body.on('click', '.rec-images.grid img', function () {
		let $input = $(this).closest('.column').find('input');
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
	let imagesPreview = function (input, placeToInsertImagePreview) {
		if (input.files) {
			let filesAmount = input.files.length;
			for (let i = 0; i < filesAmount; i++) {
				let reader = new FileReader();
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
		addIngredientGroup(null, false);
	}

	function addIngredient($list, value) {
		let counter = $list.data('widget-counter') | $list.children().length;
		if (!counter) {
			counter = $list.children().length;
		}

		let newWidget = $list.attr('data-prototype');
		newWidget = newWidget.replace(/__ingredientcounter__/g, counter);
		counter++;
		$list.data(' widget-counter', counter);

		let $newElem = $(newWidget);
		$newElem.find('input').val(value);
		$newElem.appendTo($list);
	}

	function addIngredientGroup(value, addEmptyIngredient = true) {
		let $list = $('#form_ingredientGroups');
		let counter = $list.data('widget-counter') | $list.children().length;
		if (!counter) {
			counter = $list.children().length;
		}

		let newWidget = $list.attr('data-prototype');
		newWidget = newWidget.replace(/__groupcounter__/g, counter);
		counter++;
		$list.data('widget-counter', counter);

		let $newIngredientGroup = $(newWidget);
		$newIngredientGroup.find('input').val(value);
		$newIngredientGroup.appendTo($list);

		if(addEmptyIngredient) {
			addIngredient($newIngredientGroup.find('.ingredientList'));
		}
	}

	$body.on('keydown', '.ingredients input', function () {
		console.info($(this).closest('.ingredient'));
		if ($(this).closest('.ingredient').is(':last-child')) {
			addIngredient($(this).closest('.ingredientList'));
		}
	});
	$body.on('keydown', '.groupLabelInput input', function () {
		if ($(this).closest('.ingredientgroup').is(':last-child')) {
			addIngredientGroup();
		}
	});

	$body.on('click', '.remove-row', function (e) {
		e.preventDefault();
		$(this).closest('.removable').slideUp(function () {
			$(this).remove();
		});
	});

	$ingredientList.each(function (index, element) {
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
			let groupPrefix = 'form[ingredientGroups][' + groupIndex + ']';
			$(groupElement).find('input[name^="form[ingredientGroups]"]').each(function (inputIndex, input) {
				let newName = $(input).attr('name').replace(/form\[ingredientGroups]\[\d+]/, groupPrefix);
				$(input).attr('name', newName);
			});

			$(groupElement).find('input[name*="[ingredients]"]').each(function (ingredientIndex, input) {
				let newName = $(input).attr('name').replace(/\[ingredients]\[\d+]/, '[ingredients][' + ingredientIndex + ']');
				console.info('replacing ingredient:', ingredientIndex, newName);
				$(input).attr('name', newName);
			});
		});
	}

	// add empty inputs for easier adding
	$ingredientList.each(function (index, elem) {
		addIngredient($(elem));
	});
	addIngredientGroup();
});