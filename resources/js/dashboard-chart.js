/**
 * Biểu đồ Tổng quan (Admin), trong [data-revenue-chart]:
 * - data-chart: dữ liệu 7 ngày render sẵn từ RevenueChartResource {days, labels, dates, revenue, orders, totals, changes};
 * - [data-chart-metric] đổi chỉ số Doanh thu / Số đơn (không gọi lại server, dữ liệu có sẵn cả hai);
 * - [data-chart-range] đổi 7 / 30 ngày: gọi data-url?range=… (JSON), giữ số liệu cũ nếu lỗi;
 * - bảng sr-only [data-chart-table] luôn khớp với biểu đồ cho trình đọc màn hình.
 * Chart.js chỉ được tải (import động) khi trang có biểu đồ.
 */
const cssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

const fill = (template, values) => Object.entries(values).reduce((text, [key, value]) => text.replaceAll(`:${key}`, value), template);

const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Ghi số liệu ngay trên đầu cột khi ít cột (7 ngày); 30 ngày thì để tooltip lo.
 */
const valueLabels = {
    id: 'lahoaValueLabels',
    afterDatasetsDraw(chart, _args, options) {
        if (!options.enabled) {
            return;
        }

        const { ctx } = chart;
        const meta = chart.getDatasetMeta(0);

        ctx.save();
        ctx.fillStyle = options.color;
        ctx.font = options.font;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';
        meta.data.forEach((bar, index) => {
            ctx.fillText(options.format(chart.data.datasets[0].data[index]), bar.x, bar.y - 6);
        });
        ctx.restore();
    },
};

const setupChart = async (root) => {
    const { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip } = await import('chart.js');
    Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

    const strings = JSON.parse(root.dataset.strings);
    const numbers = new Intl.NumberFormat(strings.locale);
    const millions = new Intl.NumberFormat(strings.locale, { maximumFractionDigits: 2 });
    const percent = new Intl.NumberFormat(strings.locale, { minimumFractionDigits: 1, maximumFractionDigits: 1 });
    const money = (amount) => `${numbers.format(amount)} ${strings.currency}`;
    const orders = (count) => fill(strings.orders, { count: numbers.format(count) });

    const state = { data: JSON.parse(root.dataset.chart), metric: 'revenue' };
    const formats = {
        revenue: { axis: (value) => millions.format(value / 1_000_000), label: (value) => millions.format(value / 1_000_000), full: money },
        orders: { axis: (value) => numbers.format(value), label: (value) => numbers.format(value), full: orders },
    };

    const colors = {
        today: cssVar('--color-lh-primary'),
        past: cssVar('--color-lh-sage-200'),
        muted: cssVar('--color-lh-muted'),
        grid: cssVar('--color-lh-border'),
    };
    const font = getComputedStyle(document.body).fontFamily;

    const frame = root.querySelector('[data-chart-frame]');
    const status = root.querySelector('[data-chart-status]');
    const barColors = () => state.data.labels.map((_label, index) => (index === state.data.labels.length - 1 ? colors.today : colors.past));

    const chart = new Chart(root.querySelector('[data-chart-canvas]'), {
        type: 'bar',
        data: {
            labels: state.data.labels,
            datasets: [{ data: state.data.revenue, backgroundColor: barColors(), borderRadius: { topLeft: 4, topRight: 4 }, borderSkipped: 'bottom', maxBarThickness: 58 }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: reducedMotion() ? false : { duration: 300 },
            layout: { padding: { top: 24 } },
            scales: {
                x: { grid: { display: false }, border: { color: colors.grid }, ticks: { color: colors.muted, font: { family: font, size: 12 }, autoSkip: true, maxRotation: 0 } },
                y: {
                    beginAtZero: true,
                    grace: '8%',
                    border: { display: false },
                    grid: { color: colors.grid },
                    ticks: { color: colors.muted, font: { family: font, size: 12 }, maxTicksLimit: 5, precision: 0, callback: (value) => formats[state.metric].axis(value) },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    displayColors: false,
                    callbacks: {
                        title: (items) => state.data.dates[items[0].dataIndex],
                        label: (item) => formats[state.metric].full(item.raw),
                    },
                },
                lahoaValueLabels: { enabled: true, color: colors.muted, font: `12px ${font}`, format: (value) => formats[state.metric].label(value) },
            },
        },
        plugins: [valueLabels],
    });

    const renderSummary = () => {
        const { data, metric } = state;
        const change = data.changes[metric];
        const badge = root.querySelector('[data-chart-change]');
        const isUp = change === null || change >= 0;
        const changeText = change === null ? '' : percent.format(Math.abs(change));

        root.querySelector('[data-chart-title]').textContent = fill(strings.title[metric], { days: data.days });
        root.querySelector('[data-chart-caption]').textContent = fill(strings.title[metric], { days: data.days });
        root.querySelector('[data-chart-unit]').textContent = strings.unit[metric];
        root.querySelector('[data-chart-total]').textContent = formats[metric].full(data.totals[metric]);

        badge.hidden = change === null;
        badge.classList.toggle('bg-lh-sage-50', isUp);
        badge.classList.toggle('text-lh-primary', isUp);
        badge.classList.toggle('bg-lh-warning-bg', !isUp);
        badge.classList.toggle('text-lh-amber', !isUp);
        badge.querySelector('[data-change-up]').classList.toggle('hidden', !isUp);
        badge.querySelector('[data-change-down]').classList.toggle('hidden', isUp);
        badge.querySelector('[data-change-value]').textContent = `${changeText}%`;
        badge.querySelector('[data-change-label]').textContent = change === null ? '' : fill(isUp ? strings.up : strings.down, { change: changeText, days: data.days });
    };

    const renderTable = () => {
        const rows = state.data.dates.map((date, index) => {
            const row = document.createElement('tr');
            const head = document.createElement('th');
            head.scope = 'row';
            head.textContent = index === state.data.dates.length - 1 ? `${date} (${strings.today})` : date;
            const revenue = document.createElement('td');
            revenue.textContent = money(state.data.revenue[index]);
            const count = document.createElement('td');
            count.textContent = numbers.format(state.data.orders[index]);
            row.append(head, revenue, count);

            return row;
        });

        root.querySelector('[data-chart-table]').replaceChildren(...rows);
    };

    const renderChart = () => {
        chart.data.labels = state.data.labels;
        chart.data.datasets[0].data = state.data[state.metric];
        chart.data.datasets[0].backgroundColor = barColors();
        chart.options.plugins.lahoaValueLabels.enabled = state.data.days <= 7;
        chart.update();
    };

    const setPressed = (selector, value) => {
        root.querySelectorAll(selector).forEach((button) => {
            const key = selector === '[data-chart-metric]' ? button.dataset.chartMetric : button.dataset.chartRange;
            button.setAttribute('aria-pressed', String(key === String(value)));
        });
    };

    root.querySelectorAll('[data-chart-metric]').forEach((button) => {
        button.addEventListener('click', () => {
            state.metric = button.dataset.chartMetric;
            setPressed('[data-chart-metric]', state.metric);
            renderSummary();
            renderChart();
        });
    });

    let request = null;

    root.querySelectorAll('[data-chart-range]').forEach((button) => {
        button.addEventListener('click', async () => {
            const days = Number(button.dataset.chartRange);

            if (days === state.data.days) {
                return;
            }

            request?.abort();
            request = new AbortController();
            frame.setAttribute('aria-busy', 'true');
            status.textContent = strings.loading;

            try {
                const url = new URL(root.dataset.url, window.location.origin);
                url.searchParams.set('range', String(days));
                const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: request.signal });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                state.data = (await response.json()).data;
                setPressed('[data-chart-range]', days);
                renderSummary();
                renderChart();
                renderTable();
                status.textContent = '';
            } catch (error) {
                if (error.name !== 'AbortError') {
                    status.textContent = strings.error;
                }
            } finally {
                frame.removeAttribute('aria-busy');
            }
        });
    });
};

document.querySelectorAll('[data-revenue-chart]').forEach((root) => {
    setupChart(root);
});
