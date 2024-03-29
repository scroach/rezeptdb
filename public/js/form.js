$(function () {

	/**
	 * Basic form init
	 */

	let $urlInput = $('input[name*="Url"]');
	let fetchUrl = $urlInput.closest('form').data('fetch-url');
	let $body = $('body');
	let $formTagsString = $('#form_tagsString');
	let $ingredientList = $('.ingredientList');

	let descriptionMDE = null;
	// disable markdown editor on mobile since its broken :( https://github.com/sparksuite/simplemde-markdown-editor/issues/802
	if (!/Mobi|Android/i.test(navigator.userAgent)) {
		// init simple markdown editor for description
		descriptionMDE = new SimpleMDE({
			element: $("#form_description")[0],
			spellChecker: false,
			status: false,
			toolbar: ["bold", "italic", "|", "heading-1", "heading-2", "heading-3", "|", "unordered-list", "ordered-list", "|", "link", "image", "|", "guide"],
			forceSync: true
		});
	}

	$urlInput.on('paste', function () {
		setTimeout(fetchRecipeDataFromUrl, 0);
	});
	// if we're adding a new recipe and a URL is preset autofetch data
	if(window.location.pathname.endsWith('/recipes/add') && $urlInput.val()) {
		setTimeout(fetchRecipeDataFromUrl, 0);
	}

	function fetchRecipeDataFromUrl() {
		$urlInput.closest('.input').addClass('loading');
		/**
		 * @param {{effort:string, label:string, description:string, ingredientGroups:array, images:array}} response
		 */
		$.get(fetchUrl, {url: $urlInput.val()})
			.then(function (response) {
				$('#form_label').val(response.label);
				$('#form_effort').val(response.effort);
				if(descriptionMDE) {
					descriptionMDE.value(response.description);
				} else {
					$('#form_description').val(response.description);
				}

				if(response.ingredientGroups && response.ingredientGroups.length > 0) {
					clearIngredients();
					response.ingredientGroups.forEach(function (ingredientGroup) {
						addIngredientGroup(ingredientGroup.label, false);
						ingredientGroup.ingredients.forEach(function (ingredient) {
							addIngredient($('.ingredientList:last'), ingredient);
						});
					});
				}

				response.images.forEach(function (imgUrl) {
					if (!imgUrl) {
						return;
					}

					let input = '<input type="hidden" name="images[]" value="' + imgUrl + '" disabled>';
					let toggleIcon = '<i class="white check circle icon">';
					$('.remote-images').append('<div class="column"><img src="' + imgUrl + '" />' + input + toggleIcon + '</div>');
				});

			})
			.fail(function () {
				alert('Bei der Abfrage ist leider ein Fehler aufgetreten!');
			})
			.always(function () {
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
			let groupPrefix = 'form[ingredientGroups][' + groupIndex + ']';
			$(groupElement).find('input[name^="form[ingredientGroups]"]').each(function (inputIndex, input) {
				let newName = $(input).attr('name').replace(/form\[ingredientGroups]\[\d+]/, groupPrefix);
				$(input).attr('name', newName);
			});

			$(groupElement).find('input[name*="[ingredients]"]').each(function (ingredientIndex, input) {
				let newName = $(input).attr('name').replace(/\[ingredients]\[\d+]/, '[ingredients][' + ingredientIndex + ']');
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