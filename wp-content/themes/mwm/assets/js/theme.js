/* MWM theme script — vanilla, idempotent init(scope), re-run for Elementor widgets. */
(() => {
	'use strict';

	const doc = document;
	const data = window.mwmData || { i18n: {} };
	const t = (key) => (data.i18n && data.i18n[key]) || '';
	const isRtl = () => doc.documentElement.dir !== 'ltr';
	const once = (el, key) => {
		if (el.dataset[key]) {
			return false;
		}
		el.dataset[key] = '1';
		return true;
	};
	const push = (payload) => {
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push(payload);
	};
	const chevron = '<svg class="mwm-icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>';
	const cross = '<svg class="mwm-icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';

	/* ---------- Header state ---------- */
	function header() {
		const el = doc.querySelector('[data-mwm-header]');
		if (!el || !once(el, 'mwmBound')) {
			return;
		}
		let ticking = false;
		const update = () => {
			el.classList.toggle('is-scrolled', window.scrollY > 24);
			ticking = false;
		};
		window.addEventListener('scroll', () => {
			if (!ticking) {
				ticking = true;
				requestAnimationFrame(update);
			}
		}, { passive: true });
		// Reading scrollY during boot would force a synchronous first layout.
		requestAnimationFrame(update);
	}

	/* ---------- Mobile drawer (<dialog>) ---------- */
	function drawer() {
		const dlg = doc.getElementById('mwm-drawer');
		if (!dlg || typeof dlg.showModal !== 'function' || !once(dlg, 'mwmBound')) {
			return;
		}
		const openers = doc.querySelectorAll('[data-mwm-drawer-open]');
		const setExpanded = (value) => openers.forEach((b) => b.setAttribute('aria-expanded', value));
		openers.forEach((b) => b.addEventListener('click', () => {
			dlg.showModal();
			setExpanded('true');
		}));
		dlg.querySelectorAll('[data-mwm-drawer-close]').forEach((b) => b.addEventListener('click', () => dlg.close()));
		dlg.addEventListener('click', (e) => {
			if (e.target === dlg) {
				dlg.close();
			}
		});
		dlg.addEventListener('close', () => setExpanded('false'));
		dlg.querySelectorAll('a[href]').forEach((a) => a.addEventListener('click', () => dlg.close()));
	}

	/* ---------- Reveal on scroll ---------- */
	let observer = null;
	function reveal(scope) {
		const items = scope.querySelectorAll('[data-mwm-reveal]:not(.is-in)');
		if (!items.length) {
			return;
		}
		if (!('IntersectionObserver' in window)) {
			items.forEach((el) => el.classList.add('is-in'));
			return;
		}
		if (!observer) {
			observer = new IntersectionObserver((entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-in');
						observer.unobserve(entry.target);
					}
				});
			}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
		}
		items.forEach((el) => {
			if (!once(el, 'mwmReveal')) {
				return;
			}
			const siblings = el.parentElement ? Array.from(el.parentElement.children) : [];
			const index = Math.max(0, siblings.indexOf(el));
			el.style.setProperty('--mwm-delay', `${(index % 4) * 0.08}s`);
			observer.observe(el);
		});
	}

	/* ---------- Gallery: filter tabs + lightbox ---------- */
	let lightbox = null;
	function getLightbox() {
		if (lightbox) {
			return lightbox;
		}
		const dlg = doc.createElement('dialog');
		dlg.className = 'mwm-lightbox';
		dlg.innerHTML = `
			<span class="mwm-lightbox__count" aria-live="polite"></span>
			<button type="button" class="mwm-lightbox__btn mwm-lightbox__close" aria-label="${t('close')}">${cross}</button>
			<button type="button" class="mwm-lightbox__btn mwm-lightbox__prev" aria-label="${t('prev')}">${chevron}</button>
			<button type="button" class="mwm-lightbox__btn mwm-lightbox__next" aria-label="${t('next')}">${chevron}</button>
			<figure class="mwm-lightbox__figure"><img class="mwm-lightbox__img" alt="" decoding="async"><figcaption class="mwm-lightbox__caption"></figcaption></figure>`;
		doc.body.appendChild(dlg);

		const img = dlg.querySelector('.mwm-lightbox__img');
		const caption = dlg.querySelector('.mwm-lightbox__caption');
		const count = dlg.querySelector('.mwm-lightbox__count');
		const state = { list: [], index: 0, opener: null };

		const show = (i) => {
			const total = state.list.length;
			state.index = (i + total) % total;
			const btn = state.list[state.index];
			img.src = btn.dataset.full;
			img.alt = btn.dataset.caption || '';
			caption.textContent = btn.dataset.caption || '';
			count.textContent = total > 1 ? `${state.index + 1} / ${total}` : '';
			dlg.querySelector('.mwm-lightbox__prev').hidden = total < 2;
			dlg.querySelector('.mwm-lightbox__next').hidden = total < 2;
		};

		dlg.querySelector('.mwm-lightbox__close').addEventListener('click', () => dlg.close());
		dlg.querySelector('.mwm-lightbox__prev').addEventListener('click', () => show(state.index - 1));
		dlg.querySelector('.mwm-lightbox__next').addEventListener('click', () => show(state.index + 1));
		dlg.addEventListener('click', (e) => {
			if (e.target === dlg || e.target.classList.contains('mwm-lightbox__figure')) {
				dlg.close();
			}
		});
		dlg.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowLeft') {
				show(state.index + (isRtl() ? 1 : -1));
			} else if (e.key === 'ArrowRight') {
				show(state.index + (isRtl() ? -1 : 1));
			}
		});
		dlg.addEventListener('close', () => {
			img.removeAttribute('src');
			if (state.opener) {
				state.opener.focus();
			}
		});

		lightbox = {
			open(list, index, opener) {
				state.list = list;
				state.opener = opener;
				show(index);
				dlg.showModal();
			},
		};
		return lightbox;
	}

	function gallery(scope) {
		scope.querySelectorAll('[data-mwm-gallery]').forEach((root) => {
			if (!once(root, 'mwmBound')) {
				return;
			}
			const tabs = root.querySelectorAll('.mwm-tab');
			const items = root.querySelectorAll('.mwm-gallery__item');
			const apply = (filter) => {
				items.forEach((item) => {
					item.hidden = filter !== '*' && item.dataset.group !== filter;
				});
			};
			tabs.forEach((tab) => tab.addEventListener('click', () => {
				tabs.forEach((other) => other.setAttribute('aria-pressed', other === tab ? 'true' : 'false'));
				apply(tab.dataset.filter);
				const row = tab.parentElement;
				if (row && row.scrollWidth > row.clientWidth) {
					row.scrollBy({ left: tab.getBoundingClientRect().left - row.getBoundingClientRect().left - (row.clientWidth - tab.offsetWidth) / 2, behavior: 'smooth' });
				}
			}));
			const pressed = root.querySelector('.mwm-tab[aria-pressed="true"]');
			if (pressed) {
				apply(pressed.dataset.filter);
			}

			root.addEventListener('click', (e) => {
				const btn = e.target.closest('.mwm-gallery__btn');
				if (!btn || typeof HTMLDialogElement === 'undefined') {
					return;
				}
				const list = Array.from(root.querySelectorAll('.mwm-gallery__item:not([hidden]) .mwm-gallery__btn'));
				getLightbox().open(list, Math.max(0, list.indexOf(btn)), btn);
			});
		});
	}

	/* ---------- Hero slider: crossfade; the progress bar's animationend is the timer ---------- */
	function sliders(scope) {
		scope.querySelectorAll('[data-mwm-slider]').forEach((root) => {
			const slides = Array.from(root.querySelectorAll('.mwm-hero__slide'));
			if (slides.length < 2 || !once(root, 'mwmBound')) {
				return;
			}
			const dots = Array.from(root.querySelectorAll('[data-mwm-dot]'));
			const current = root.querySelector('[data-mwm-current]');
			const auto = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			const state = { index: 0, hover: false, focus: false, visible: true };

			const sync = () => {
				root.classList.toggle('is-running', auto && !state.hover && !state.focus && state.visible && !doc.hidden);
			};
			const go = (i) => {
				state.index = (i + slides.length) % slides.length;
				slides.forEach((slide, n) => {
					const on = n === state.index;
					slide.classList.toggle('is-active', on);
					slide.setAttribute('aria-hidden', on ? 'false' : 'true');
				});
				dots.forEach((dot, n) => {
					if (n === state.index) {
						dot.setAttribute('aria-current', 'true');
					} else {
						dot.removeAttribute('aria-current');
					}
				});
				if (current) {
					current.textContent = String(state.index + 1).padStart(2, '0');
				}
				const upcoming = slides[(state.index + 1) % slides.length].querySelector('img');
				if (upcoming) {
					upcoming.loading = 'eager';
				}
			};

			slides.forEach((slide, n) => {
				if (n) {
					slide.setAttribute('aria-hidden', 'true');
				}
			});
			dots.forEach((dot, n) => dot.addEventListener('click', () => go(n)));
			const prev = root.querySelector('[data-mwm-prev]');
			const next = root.querySelector('[data-mwm-next]');
			if (prev) {
				prev.addEventListener('click', () => go(state.index - 1));
			}
			if (next) {
				next.addEventListener('click', () => go(state.index + 1));
			}

			let startX = null;
			root.addEventListener('pointerdown', (e) => {
				startX = e.pointerType === 'mouse' || e.target.closest('button') ? null : e.clientX;
			});
			root.addEventListener('pointerup', (e) => {
				if (startX === null) {
					return;
				}
				const dx = e.clientX - startX;
				startX = null;
				if (Math.abs(dx) > 40) {
					go(state.index + ((dx > 0) === isRtl() ? 1 : -1));
				}
			});

			if (auto) {
				root.classList.add('is-auto');
				root.addEventListener('animationend', (e) => {
					if (e.animationName === 'mwm-progress') {
						go(state.index + 1);
					}
				});
				root.addEventListener('pointerenter', (e) => {
					if (e.pointerType === 'mouse') {
						state.hover = true;
						sync();
					}
				});
				root.addEventListener('pointerleave', () => {
					state.hover = false;
					sync();
				});
				root.addEventListener('focusin', (e) => {
					if (e.target.matches(':focus-visible')) {
						state.focus = true;
						sync();
					}
				});
				root.addEventListener('focusout', (e) => {
					if (!root.contains(e.relatedTarget)) {
						state.focus = false;
						sync();
					}
				});
				doc.addEventListener('visibilitychange', sync);
				if ('IntersectionObserver' in window) {
					new IntersectionObserver((entries) => {
						state.visible = entries[0].isIntersecting;
						sync();
					}).observe(root);
				}
			}
			sync();
		});
	}

	/* ---------- Maps load on demand ---------- */
	function maps(scope) {
		scope.querySelectorAll('[data-mwm-map]').forEach((box) => {
			if (!once(box, 'mwmBound')) {
				return;
			}
			const btn = box.querySelector('button');
			if (!btn) {
				return;
			}
			btn.addEventListener('click', () => {
				const frame = doc.createElement('iframe');
				frame.src = box.dataset.mwmMap;
				frame.title = box.dataset.title || '';
				frame.loading = 'lazy';
				frame.referrerPolicy = 'no-referrer-when-downgrade';
				frame.allowFullscreen = true;
				box.replaceChildren(frame);
			});
		});
	}

	/* ---------- Mobile contact bar hides while typing ---------- */
	function contactBar() {
		const bar = doc.querySelector('[data-mwm-bar]');
		if (!bar || !once(bar, 'mwmBound')) {
			return;
		}
		const isField = (el) => el && el.matches && el.matches('input:not([type="checkbox"]):not([type="radio"]), textarea, select');
		doc.addEventListener('focusin', (e) => {
			if (isField(e.target)) {
				bar.classList.add('is-hidden');
			}
		});
		doc.addEventListener('focusout', (e) => {
			if (isField(e.target)) {
				bar.classList.remove('is-hidden');
			}
		});
	}

	/* ---------- Contact click tracking (dataLayer only when present) ---------- */
	function tracking() {
		if (!once(doc.documentElement, 'mwmTrack')) {
			return;
		}
		doc.addEventListener('click', (e) => {
			const el = e.target.closest('[data-mwm-track]');
			if (el) {
				push({ event: 'mwm_contact', mwm_method: el.dataset.mwmTrack, mwm_page: location.pathname });
			}
		});
	}

	/* ---------- Quote form ---------- */
	const toLatinDigits = (value) => value
		.replace(/[\u0660-\u0669]/g, (d) => String(d.charCodeAt(0) - 0x0660))
		.replace(/[\u06F0-\u06F9]/g, (d) => String(d.charCodeAt(0) - 0x06F0));
	const normalizePhone = (value) => toLatinDigits(value).replace(/[\s\-().]/g, '').replace(/^00/, '+');
	const saudiMobile = /^(?:\+?966|0)?5\d{8}$/;
	const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

	function attribution() {
		try {
			const params = new URLSearchParams(location.search);
			const utm = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content']
				.filter((k) => params.get(k))
				.map((k) => `${k}=${params.get(k)}`)
				.join('&');
			if (utm) {
				sessionStorage.setItem('mwm_utm', utm);
			}
			if (!sessionStorage.getItem('mwm_ref') && doc.referrer && !doc.referrer.startsWith(location.origin)) {
				sessionStorage.setItem('mwm_ref', doc.referrer);
			}
			return { utm: sessionStorage.getItem('mwm_utm') || '', ref: sessionStorage.getItem('mwm_ref') || '' };
		} catch (err) {
			return { utm: '', ref: '' };
		}
	}

	function setError(field, message) {
		if (!field) {
			return;
		}
		const id = (field.getAttribute('aria-describedby') || '').split(' ')[0];
		const holder = id ? doc.getElementById(id) : null;
		if (message) {
			field.setAttribute('aria-invalid', 'true');
		} else {
			field.removeAttribute('aria-invalid');
		}
		if (holder) {
			holder.textContent = message || '';
		}
	}

	function validate(form) {
		const errors = {};
		const name = form.elements.name;
		const phone = form.elements.phone;
		const email = form.elements.email;
		const consent = form.elements.consent;
		if (name && !name.value.trim()) {
			errors.name = t('required');
		}
		if (phone) {
			const value = normalizePhone(phone.value);
			if (!value) {
				errors.phone = t('required');
			} else if (!saudiMobile.test(value)) {
				errors.phone = t('phone');
			}
		}
		if (email && email.value.trim() && !emailRe.test(email.value.trim())) {
			errors.email = t('email');
		}
		if (consent && !consent.checked) {
			errors.consent = t('consent');
		}
		['name', 'phone', 'email', 'consent'].forEach((key) => setError(form.elements[key], errors[key]));
		return errors;
	}

	function forms(scope) {
		scope.querySelectorAll('[data-mwm-form]').forEach((form) => {
			if (!once(form, 'mwmBound')) {
				return;
			}
			const wrap = form.closest('[data-mwm-form-wrap]');
			const success = wrap ? wrap.querySelector('[data-mwm-success]') : null;
			const alert = form.querySelector('[data-mwm-alert]') || form.querySelector('.mwm-form__alert');
			const submit = form.querySelector('[type="submit"]');
			const meta = attribution();
			if (form.querySelector('[data-mwm-utm]')) {
				form.querySelector('[data-mwm-utm]').value = meta.utm;
			}
			if (form.querySelector('[data-mwm-ref]')) {
				form.querySelector('[data-mwm-ref]').value = meta.ref;
			}

			const phone = form.querySelector('[data-mwm-phone]');
			if (phone) {
				phone.addEventListener('input', () => {
					const latin = toLatinDigits(phone.value);
					if (latin !== phone.value) {
						phone.value = latin;
					}
				});
			}
			form.addEventListener('change', (e) => {
				if (e.target.getAttribute('aria-invalid') === 'true') {
					validate(form);
				}
			});

			form.addEventListener('submit', async (e) => {
				e.preventDefault();
				if (alert) {
					alert.hidden = true;
				}
				const errors = validate(form);
				const first = Object.keys(errors)[0];
				if (first) {
					form.elements[first].focus();
					return;
				}
				if (!window.fetch || !window.FormData) {
					form.submit();
					return;
				}
				const body = new FormData(form);
				body.set('mwm_ajax', '1');
				const label = submit ? submit.querySelector('span') : null;
				const original = label ? label.textContent : '';
				if (submit) {
					submit.setAttribute('aria-busy', 'true');
					if (label) {
						label.textContent = t('sending');
					}
				}
				try {
					// form.action is shadowed by the <input name="action"> WordPress needs.
					const res = await fetch(form.getAttribute('action'), { method: 'POST', body, credentials: 'same-origin' });
					const json = await res.json().catch(() => null);
					if (json && json.success) {
						push({ event: 'mwm_lead', mwm_service: body.get('service') || '', mwm_type: body.get('type') || '' });
						form.hidden = true;
						if (success) {
							success.hidden = false;
							success.focus();
						}
						return;
					}
					const payload = json && json.data ? json.data : {};
					if (payload.fields) {
						Object.keys(payload.fields).forEach((key) => setError(form.elements[key], t(payload.fields[key]) || t('required')));
					}
					if (alert) {
						alert.textContent = payload.message || t('error');
						alert.hidden = false;
						alert.setAttribute('tabindex', '-1');
						alert.focus();
					}
				} catch (err) {
					if (alert) {
						alert.textContent = t('error');
						alert.hidden = false;
					}
				} finally {
					if (submit) {
						submit.removeAttribute('aria-busy');
						if (label) {
							label.textContent = original;
						}
					}
				}
			});
		});
	}

	/* ---------- Boot ---------- */
	function init(scope) {
		const root = scope || doc;
		reveal(root);
		sliders(root);
		gallery(root);
		maps(root);
		forms(root);
	}

	function boot() {
		header();
		drawer();
		contactBar();
		tracking();
		init(doc);
	}

	if (doc.readyState === 'loading') {
		doc.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}

	window.addEventListener('elementor/frontend/init', () => {
		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			window.elementorFrontend.hooks.addAction('frontend/element_ready/global', ($scope) => {
				init($scope && $scope[0] ? $scope[0] : doc);
			});
		}
	});

	window.mwmInit = init;
})();
