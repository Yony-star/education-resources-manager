/**
 * Education Resources Manager — admin scripts (monthly chart).
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var canvas = document.getElementById('erm-monthly-chart');
		if (!canvas || typeof ermMonthlyData === 'undefined') {
			return;
		}

		var ctx = canvas.getContext('2d');
		var data = ermMonthlyData;

		if (!data || !data.length) {
			ctx.font = '16px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
			ctx.fillStyle = '#646970';
			ctx.textAlign = 'center';
			ctx.fillText('No hay datos disponibles', canvas.width / 2, canvas.height / 2);
			return;
		}

		var padding = { top: 30, right: 20, bottom: 50, left: 50 };
		var chartW = canvas.width - padding.left - padding.right;
		var chartH = canvas.height - padding.top - padding.bottom;
		var maxVal =
			Math.max.apply(
				null,
				data.map(function (d) {
					return parseInt(d.total, 10);
				})
			) || 1;
		var barW = (chartW / data.length) * 0.7;
		var barGap = (chartW / data.length) * 0.3;
		var steps = 5;
		var i;

		ctx.fillStyle = '#fff';
		ctx.fillRect(0, 0, canvas.width, canvas.height);

		ctx.strokeStyle = '#f0f0f1';
		ctx.lineWidth = 1;

		for (i = 0; i <= steps; i++) {
			var y = padding.top + chartH - (chartH / steps) * i;
			ctx.beginPath();
			ctx.moveTo(padding.left, y);
			ctx.lineTo(padding.left + chartW, y);
			ctx.stroke();

			ctx.fillStyle = '#646970';
			ctx.font = '12px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
			ctx.textAlign = 'right';
			ctx.fillText(Math.round((maxVal / steps) * i), padding.left - 8, y + 4);
		}

		data.forEach(function (d, index) {
			var val = parseInt(d.total, 10);
			var barHeight = (val / maxVal) * chartH;
			var x = padding.left + index * (barW + barGap) + barGap / 2;
			var y = padding.top + chartH - barHeight;

			ctx.fillStyle = '#2271b1';
			ctx.fillRect(x, y, barW, barHeight);

			ctx.fillStyle = '#3c434a';
			ctx.font = 'bold 13px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
			ctx.textAlign = 'center';
			ctx.fillText(val, x + barW / 2, y - 6);

			ctx.fillStyle = '#646970';
			ctx.font = '11px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
			ctx.fillText(d.month, x + barW / 2, padding.top + chartH + 20);
		});

		ctx.strokeStyle = '#c3c4c7';
		ctx.lineWidth = 2;
		ctx.beginPath();
		ctx.moveTo(padding.left, padding.top + chartH);
		ctx.lineTo(padding.left + chartW, padding.top + chartH);
		ctx.stroke();
	});
})();
