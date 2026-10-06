(function ($) {
	'use strict';

	$('.mwm-gallery-box').each(function () {
		var box = $(this);
		var list = box.find('.mwm-gallery-box__list');
		var input = box.find('input[name="mwm_gallery"]');
		var frame;

		function sync() {
			input.val(list.children().map(function () {
				return $(this).data('id');
			}).get().join(','));
		}

		list.sortable({ update: sync });

		list.on('click', 'button', function () {
			$(this).closest('li').remove();
			sync();
		});

		box.find('.mwm-gallery-box__add').on('click', function () {
			if (!frame) {
				frame = wp.media({ title: mwmGallery.title, library: { type: 'image' }, multiple: 'add' });
				frame.on('select', function () {
					frame.state().get('selection').each(function (att) {
						var a = att.toJSON();
						if (list.find('[data-id="' + a.id + '"]').length) {
							return;
						}
						var src = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
						var li = $('<li>').attr('data-id', a.id).data('id', a.id);
						li.append($('<img>').attr({ src: src, alt: '' }));
						li.append($('<button type="button">').html('&times;'));
						list.append(li);
					});
					sync();
				});
			}
			frame.open();
		});
	});
})(jQuery);
