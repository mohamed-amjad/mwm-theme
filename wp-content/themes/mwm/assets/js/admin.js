(function () {
	'use strict';

	var tabs = document.querySelectorAll('.mwm-settings__tabs .nav-tab');
	var panels = document.querySelectorAll('.mwm-settings__panel');

	function show(id) {
		tabs.forEach(function (t) {
			t.classList.toggle('nav-tab-active', t.dataset.tab === id);
		});
		panels.forEach(function (p) {
			p.hidden = p.id !== 'mwm-tab-' + id;
		});
	}

	tabs.forEach(function (tab) {
		tab.addEventListener('click', function (e) {
			e.preventDefault();
			show(tab.dataset.tab);
			history.replaceState(null, '', '#' + tab.dataset.tab);
		});
	});

	if (location.hash && document.getElementById('mwm-tab-' + location.hash.slice(1))) {
		show(location.hash.slice(1));
	}

	var form = document.querySelector('.mwm-settings form');
	if (form) {
		form.addEventListener('submit', function () {
			var active = document.querySelector('.mwm-settings__tabs .nav-tab-active');
			var ref = form.querySelector('input[name="_wp_http_referer"]');
			if (active && ref) {
				ref.value = ref.value.split('#')[0] + '#' + active.dataset.tab;
			}
		});
	}

	document.querySelectorAll('.mwm-media').forEach(function (box) {
		var input = box.querySelector('input');
		var name = box.querySelector('.mwm-media__name');
		var clear = box.querySelector('.mwm-media__clear');
		var frame;

		box.querySelector('.mwm-media__pick').addEventListener('click', function () {
			if (!frame) {
				frame = wp.media({
					title: mwmAdmin.choose,
					library: { type: box.dataset.type },
					multiple: false
				});
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					input.value = att.id;
					name.textContent = att.filename || att.title;
					clear.hidden = false;
				});
			}
			frame.open();
		});

		clear.addEventListener('click', function () {
			input.value = '';
			name.textContent = '';
			clear.hidden = true;
		});
	});
})();
