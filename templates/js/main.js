/**
 * Main JS
 *
 * @copyright 2021 NB Communication Ltd
 *
 */

const main = {

	storage: {},

	init: () => {

		nb.profilerStart('main.init');

		// workaround for Safari's private browsing mode and accessing sessionStorage in Blink
		try {
			main.storage = window.sessionStorage || {};
			var key = 'a';
			main.storage[key] = 1;
			delete main.storage[key];
		} catch (e) {
			main.storage = {};
		}

		// Top alert
		const alert = uk.$('#wrapper > .uk-alert');
		if (alert && 'sessionStorage' in window) {
			const alertKey = 'alertSeen';
			if (!(alertKey in main.storage)) {
				uk.removeAttr(alert, 'hidden');
				if (alert.seenOnce) {
					main.storage[alertKey] = true;
				} else {
					uk.on(alert, 'click', () => main.storage[alertKey] = true);
				}
			}
		}

		// Content
		const blocks = uk.$$('.content');
		if (blocks.length) {

			blocks.forEach((block) => {

				// Apply UIkit table component
				uk.$$('table', block).forEach((el) => {
					uk.addClass(el, 'uk-table');
					uk.wrapAll(el, '<div class="uk-overflow-auto">');
				});

				// Inline Images UIkit Lightbox/Scrollspy
				(uk.$$('a[href]', block).filter((a) => {
					return uk.attr(a, 'href').match(/\.(jpg|jpeg|png|gif|webp)/i);
				})).forEach((a) => {

					let figure = a.parentElement;
					if (figure.nodeName !== 'FIGURE') {
						uk.wrapAll(a, '<figure>');
						figure = a.parentElement;
					}

					const img = uk.$('img', a);
					if (uk.hasAttr(img, 'class')) {
						uk.addClass(figure, uk.attr(img, 'class'));
						uk.removeAttr(img, 'class');
					}

					const caption = uk.$('figcaption', figure);

					// uk-lightbox
					uk.attr(figure, 'data-uk-lightbox', 'animation: fade');
					if (caption) uk.attr(a, 'data-caption', nb.wrap(uk.html(caption), 'div'));
				});
			});
		}

		const widget = uk.$('.sidebar .widget');
		if (widget && window.outerWidth > 1260) {
			widget.parentElement.style.top = `${uk.$('.banner').offsetHeight + 150}px`;
			if (widget.offsetHeight > (window.outerHeight / 2)) {
				uk.$('.main-body').style.minHeight = `${widget.offsetHeight}px`;
			};
		}

		main.buttons();
		main.mmenu();
		main.search();

		nb.profilerStop('main.init');
	},

	buttons: () => {

		const attr = (e, selector) => {
			const el = e.target;
			const rect = el.getBoundingClientRect();
			uk.attr(
				uk.$(selector, el),
				'style',
				'top:' +
					(e.pageY - (rect.top + (window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0))) + 'px' + '; ' +
				'left:' +
					(e.pageX - (rect.left + (window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft || 0))) + 'px'
			);
		}

		uk.$$('.uk-button-primary, .uk-button-secondary, .uk-button-default').forEach(button => {
			uk.on(button, 'mouseenter', e => attr(e, '> *:last-child'));
			uk.on(button, 'mouseout', e => attr(e, '> *:last-child'));
		});

		uk.$$('.entry-hex .entry-body').forEach(button => {
			uk.on(button, 'mouseenter', e => attr(e, '.circle'));
			uk.on(button, 'mouseout', e => attr(e, '.circle'));
		});
	},

	mmenu: () => {

		const el = uk.$('#mmenu');
		if (!el) return;

		const menu = new Mmenu(el,
			{
				navbars: [
					{
						'position': 'bottom',
						'content': [
							'<a href="#" class="uk-button uk-button-primary">Become a member</a>',
						]
					}
				],
				offCanvas: false,
				extensions: [
					'border-full',
					'fullscreen'
				],
			},
			{
				classNames: {
					selected: 'uk-active'
				},
				transitionDuration: 256
			}
		);

		uk.removeClass(uk.$('.mmenu-overlay'), 'uk-hidden');

		const toggle = uk.$('.uk-navbar-toggle');
		const search_toggle = uk.$('.search-toggler');
		const html = uk.$('html');

		if (toggle) {

			const toggler = (open) => {
				uk[`${open ? 'add' : 'remove'}Class`](toggle, 'uk-open');
				uk[`${open ? 'add' : 'remove'}Class`](html, 'show-menu');
			};

			menu.API.bind('close:start', () => toggler(false));
			uk.on(toggle, 'click', () => {

				toggler(!uk.hasClass(toggle, 'uk-open'));

				if (uk.hasClass(html, 'show-search')) {
					// close menu
					uk.removeClass(html, 'show-search');
					uk.removeClass(search_toggle, 'uk-open');
				}
			});
		}

		uk.$$('.mm-navbar__title').forEach(item => item.innerText = 'Back');
	},

	search: () => uk.on('#modal-search', 'shown', function() {
		uk.$('input', this).focus()
	})

};

uk.ready(() => main.init());
