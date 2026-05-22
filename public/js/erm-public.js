/**
 * Education Resources Manager — public shortcode (REST + filters).
 */
(function () {
	'use strict';

	const state = {
		currentPage: 1,
		perPage: 10,
		filters: {
			search: '',
			type: '',
			difficulty: '',
			category: '',
		},
		isLoading: false,
		totalPages: 1,
	};

	const container = document.getElementById('erm-resources-container');
	const grid = document.getElementById('erm-resources-grid');
	const pagination = document.getElementById('erm-pagination');
	const loadingEl = document.getElementById('erm-loading');
	const searchInput = document.getElementById('erm-search');
	const typeSelect = document.getElementById('erm-type');
	const diffSelect = document.getElementById('erm-difficulty');
	const clearBtn = document.getElementById('erm-clear-filters');

	const typeLabels = {
		course: 'Curso',
		tutorial: 'Tutorial',
		ebook: 'Ebook',
		video: 'Video',
	};

	const diffLabels = {
		beginner: 'Principiante',
		intermediate: 'Intermedio',
		advanced: 'Avanzado',
	};

	async function fetchResources() {
		if (!container || !grid || typeof ermPublic === 'undefined') {
			return;
		}

		if (state.isLoading) {
			return;
		}

		state.isLoading = true;
		showLoading(true);

		const params = new URLSearchParams({
			page: String(state.currentPage),
			per_page: String(state.perPage),
			search: state.filters.search,
			type: state.filters.type,
			difficulty: state.filters.difficulty,
			category: state.filters.category,
		});

		params.forEach(function (value, key) {
			if (!value && key !== 'page' && key !== 'per_page') {
				params.delete(key);
			}
		});

		try {
			const response = await fetch(
				ermPublic.apiUrl + '/resources?' + params.toString(),
				{
					headers: {
						'X-WP-Nonce': ermPublic.nonce,
					},
				}
			);

			if (!response.ok) {
				throw new Error('HTTP ' + response.status);
			}

			const data = await response.json();

			if (data.success) {
				renderResources(data.data.resources);
				renderPagination(data.data.pagination);
				state.totalPages = data.data.pagination.total_pages;
			} else {
				renderError(ermPublic.i18n.error);
			}
		} catch (err) {
			renderError(ermPublic.i18n.error);
			console.error('[ERM] Error al cargar recursos:', err);
		} finally {
			state.isLoading = false;
			showLoading(false);
		}
	}

	async function trackResource(resourceId, actionType) {
		if (typeof ermPublic === 'undefined') {
			return;
		}

		actionType = actionType || 'view';

		try {
			await fetch(ermPublic.apiUrl + '/resources/' + resourceId + '/track', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': ermPublic.nonce,
				},
				body: JSON.stringify({ action_type: actionType }),
			});
		} catch (err) {
			console.warn('[ERM] Error al registrar tracking:', err);
		}
	}

	function renderResources(resources) {
		if (!grid) {
			return;
		}

		if (!resources || !resources.length) {
			grid.innerHTML =
				'<p class="erm-no-results">' + escapeHTML(ermPublic.i18n.no_results) + '</p>';
			return;
		}

		grid.innerHTML = resources.map(createCardHTML).join('');

		grid.querySelectorAll('.erm-card__btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				const resourceId = this.dataset.resourceId;
				const resourceUrl = this.dataset.resourceUrl;
				trackResource(resourceId, 'view');
				if (resourceUrl) {
					window.open(resourceUrl, '_blank', 'noopener,noreferrer');
				}
			});
		});
	}

	function createCardHTML(resource) {
		const price =
			resource.price === 0 || resource.price === '0'
				? '<span class="erm-price erm-price--free">' +
				  escapeHTML(ermPublic.i18n.free) +
				  '</span>'
				: '<span class="erm-price">$' +
				  parseFloat(resource.price).toFixed(2) +
				  '</span>';

		const duration = resource.duration_minutes
			? '<span class="erm-card__duration">⏱ ' +
			  escapeHTML(String(resource.duration_minutes)) +
			  ' min</span>'
			: '';

		const image = resource.featured_image
			? '<div class="erm-card__image"><img src="' +
			  escapeHTML(resource.featured_image) +
			  '" alt="' +
			  escapeHTML(resource.title) +
			  '" loading="lazy"></div>'
			: '<div class="erm-card__image erm-card__image--placeholder" aria-hidden="true"></div>';

		const typeLabel = typeLabels[resource.type] || resource.type;
		const diffLabel = diffLabels[resource.difficulty] || resource.difficulty;
		const resourceUrl = resource.url || resource.permalink || '';

		return (
			'<article class="erm-card" role="listitem">' +
			image +
			'<div class="erm-card__body">' +
			'<div class="erm-card__meta">' +
			'<span class="erm-badge erm-badge--' +
			escapeHTML(resource.type) +
			'">' +
			escapeHTML(typeLabel) +
			'</span>' +
			'<span class="erm-badge erm-badge--' +
			escapeHTML(resource.difficulty) +
			'">' +
			escapeHTML(diffLabel) +
			'</span>' +
			'</div>' +
			'<h3 class="erm-card__title">' +
			escapeHTML(resource.title) +
			'</h3>' +
			'<p class="erm-card__excerpt">' +
			escapeHTML(resource.excerpt) +
			'</p>' +
			'<div class="erm-card__info">' +
			duration +
			price +
			'<span class="erm-card__views">👁 ' +
			escapeHTML(String(resource.views || 0)) +
			'</span>' +
			'</div>' +
			'</div>' +
			'<div class="erm-card__footer">' +
			'<button type="button" class="erm-btn erm-btn-primary erm-card__btn" ' +
			'data-resource-id="' +
			escapeHTML(String(resource.id)) +
			'" ' +
			'data-resource-url="' +
			escapeHTML(resourceUrl) +
			'" ' +
			'aria-label="' +
			escapeHTML(ermPublic.i18n.view + ': ' + resource.title) +
			'">' +
			escapeHTML(ermPublic.i18n.view) +
			' →</button>' +
			'</div>' +
			'</article>'
		);
	}

	function renderPagination(paginationData) {
		if (!pagination) {
			return;
		}

		if (!paginationData || paginationData.total_pages <= 1) {
			pagination.innerHTML = '';
			return;
		}

		let html = '';
		let i;

		for (i = 1; i <= paginationData.total_pages; i++) {
			const active =
				i === paginationData.current_page ? ' erm-pagination__btn--active' : '';
			const current =
				i === paginationData.current_page ? ' aria-current="page"' : '';
			html +=
				'<button type="button" class="erm-pagination__btn' +
				active +
				'" data-page="' +
				i +
				'" aria-label="Página ' +
				i +
				'"' +
				current +
				'>' +
				i +
				'</button>';
		}

		pagination.innerHTML = html;

		pagination.querySelectorAll('.erm-pagination__btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				state.currentPage = parseInt(this.dataset.page, 10);
				fetchResources();
				if (container) {
					container.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
			});
		});
	}

	function renderError(message) {
		if (grid) {
			grid.innerHTML = '<p class="erm-error" role="alert">' + escapeHTML(message) + '</p>';
		}
	}

	function showLoading(show) {
		if (loadingEl) {
			loadingEl.style.display = show ? 'flex' : 'none';
		}
		if (grid) {
			grid.style.opacity = show ? '0.5' : '1';
		}
	}

	function escapeHTML(str) {
		if (!str && str !== 0) {
			return '';
		}
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function debounce(fn, delay) {
		let timer;
		return function () {
			const args = arguments;
			const self = this;
			clearTimeout(timer);
			timer = setTimeout(function () {
				fn.apply(self, args);
			}, delay);
		};
	}

	function initEventListeners() {
		if (!container) {
			return;
		}

		state.perPage = parseInt(container.dataset.perPage, 10) || ermPublic.perPage || 10;
		state.filters.type = container.dataset.type || '';
		state.filters.difficulty = container.dataset.difficulty || '';
		state.filters.category = container.dataset.category || '';

		if (typeSelect && state.filters.type) {
			typeSelect.value = state.filters.type;
		}
		if (diffSelect && state.filters.difficulty) {
			diffSelect.value = state.filters.difficulty;
		}

		if (searchInput) {
			searchInput.addEventListener(
				'input',
				debounce(function () {
					state.filters.search = this.value.trim();
					state.currentPage = 1;
					fetchResources();
				}, 400)
			);
		}

		if (typeSelect) {
			typeSelect.addEventListener('change', function () {
				state.filters.type = this.value;
				state.currentPage = 1;
				fetchResources();
			});
		}

		if (diffSelect) {
			diffSelect.addEventListener('change', function () {
				state.filters.difficulty = this.value;
				state.currentPage = 1;
				fetchResources();
			});
		}

		if (clearBtn) {
			clearBtn.addEventListener('click', function () {
				state.filters = {
					search: '',
					type: '',
					difficulty: '',
					category: container.dataset.category || '',
				};
				state.currentPage = 1;
				if (searchInput) {
					searchInput.value = '';
				}
				if (typeSelect) {
					typeSelect.value = '';
				}
				if (diffSelect) {
					diffSelect.value = '';
				}
				fetchResources();
			});
		}
	}

	function init() {
		if (!container || typeof ermPublic === 'undefined') {
			return;
		}
		initEventListeners();
		fetchResources();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
