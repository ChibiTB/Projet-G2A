<?php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des capteurs</title>
    <link rel="stylesheet" href="Vue/style.css">
    <link rel="stylesheet" href="Vue/historique.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="Vue/historique-chart-toggle.js"></script>

</head>
<body>
    <div id="header-placeholder"></div>

    <main class="historique" id="main-content">
    <div class="container">
        <h2>Historique des capteurs</h2>

        <!-- Enhanced filter section -->
        <form id="filter-form" class="filters">
            <label>Du <input type="date" id="date-start"></label>
            <label>au <input type="date" id="date-end"></label>
            <div class="capteurs">
                <label><input type="checkbox" class="capteur" value="1" checked> 🌡️ Température</label>
                <label><input type="checkbox" class="capteur" value="2" checked> 💧 Humidité</label>
                <label><input type="checkbox" class="capteur" value="3" checked> 💡 Luminosité</label>
                <label><input type="checkbox" class="capteur" value="4" checked> 📏 Distance</label>
                <label><input type="checkbox" class="capteur" value="5" checked> 🔊 Sonore</label>
                
            </div>
            <button type="submit">Appliquer</button>
        </form>

        <!-- New search functionality -->
        <div class="search-section">
            <input type="text" id="search-input" placeholder="Rechercher dans l'historique..." />
            <button id="search-btn">Rechercher</button>
        </div>

        <!-- View toggle buttons -->
        <div class="view-toggle">
            <button id="table-view-btn" class="active">Vue Tableau</button>
            <button id="chart-view-btn">Vue Graphique</button>
        </div>

        <!-- Table view -->
        <div id="table-view" class="view-container">
            <table id="history-table" class="historique-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Capteur</th>
                        <th>Min</th>
                        <th>Max</th>
                        <th>Moyenne</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <!-- Graph view -->
        <div id="chart-view" class="view-container" style="display: none;">
            <canvas id="historique-chart" height="100"></canvas>
        </div>


        <!-- Chart view -->
        <div id="chart-view" class="view-container" style="display: none;">
            <canvas id="history-chart"></canvas>
        </div>

        <!-- Export functionality -->
        <div class="export-section">
            <button id="export-csv">Exporter CSV</button>
            <button id="export-pdf">Exporter PDF</button>
        </div>

        <!-- Statistics section -->
        <div class="stats-section">
            <h3>Statistiques</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total d'enregistrements</h4>
                    <span id="total-records">0</span>
                </div>
                <div class="stat-card">
                    <h4>Période analysée</h4>
                    <span id="period-analyzed">-</span>
                </div>
                <div class="stat-card">
                    <h4>Capteurs actifs</h4>
                    <span id="active-sensors">0</span>
                </div>
            </div>
        </div>
    </div>
    </main>

    <div id="footer-placeholder"></div>

    <script>
    let currentData = [];
    let chart = null;

    async function includeHTML(selector, url) {
        const el = document.querySelector(selector);
        const resp = await fetch(url);
        el.innerHTML = await resp.text();
        if (selector === "#header-placeholder") {
        setTimeout(() => {
            const header = el.querySelector('.header');
            const main = document.getElementById('main-content');
            if (header && main) main.style.paddingTop = (header.offsetHeight + 20) + 'px';
        }, 100);
        }
        if (selector === "#footer-placeholder") {
        setTimeout(() => {
            const footer = el.querySelector('footer');
            const main = document.getElementById('main-content');
            if (footer && main) main.style.paddingBottom = '20px';
        }, 100);
        }
    }
    includeHTML('#header-placeholder','header.html');
    includeHTML('#footer-placeholder','footer.html');

    function defaultDates() {
        const end = new Date();
        const start = new Date();
        start.setDate(end.getDate() - 6);
        document.getElementById('date-start').value = start.toISOString().split('T')[0];
        document.getElementById('date-end').value = end.toISOString().split('T')[0];
    }

    async function loadHistory() {
        try {
        const params = new URLSearchParams();
        const dStart = document.getElementById('date-start').value;
        const dEnd = document.getElementById('date-end').value;
        if (dStart) params.set('start', dStart);
        if (dEnd) params.set('end', dEnd);
        const caps = Array.from(document.querySelectorAll('.capteur:checked')).map(c => c.value);
        if (caps.length) params.set('capteurs', caps.join(','));
        const res = await fetch('get_history.php?' + params.toString());
        const data = await res.json();
        currentData = data;
        
        updateTable(data);
        updateChart(data);
        updateStats(data);
        } catch(err) {
        console.error('Erreur chargement historique', err);
        }
    }

    function updateTable(data) {
        const tbody = document.querySelector('#history-table tbody');
        tbody.innerHTML = '';
        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${row.jour}</td><td>${row.capteur}</td><td>${row.min}</td><td>${row.max}</td><td>${row.moyenne}</td>`;
            tbody.appendChild(tr);
        });
    }

    function updateChart(data) {
        const ctx = document.getElementById('history-chart').getContext('2d');
        
        if (chart) {
            chart.destroy();
        }

        const datasets = {};
        data.forEach(row => {
            if (!datasets[row.capteur]) {
                datasets[row.capteur] = {
                    label: row.capteur,
                    data: [],
                    borderColor: getColorForSensor(row.capteur),
                    backgroundColor: getColorForSensor(row.capteur) + '20',
                    fill: false
                };
            }
            datasets[row.capteur].data.push({
                x: row.jour,
                y: parseFloat(row.moyenne)
            });
        });

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: Object.values(datasets)
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            parser: 'YYYY-MM-DD',
                            displayFormats: {
                                day: 'DD/MM'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function getColorForSensor(sensor) {
        const colors = {
            'Température': '#ff6384',
            'Humidité': '#36a2eb',
            'Luminosité': '#ffce56',
            'Distance': '#4bc0c0',
            'Sonore': '#9966ff'
        };
        return colors[sensor] || '#999999';
    }

    function updateStats(data) {
        document.getElementById('total-records').textContent = data.length;
        document.getElementById('active-sensors').textContent = new Set(data.map(row => row.capteur)).size;
        
        if (data.length > 0) {
            const dates = data.map(row => new Date(row.jour));
            const minDate = new Date(Math.min(...dates));
            const maxDate = new Date(Math.max(...dates));
            const diffTime = Math.abs(maxDate - minDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            document.getElementById('period-analyzed').textContent = `${diffDays} jour(s)`;
        }
    }

    function searchTable() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const filteredData = currentData.filter(row => 
            row.jour.toLowerCase().includes(searchTerm) ||
            row.capteur.toLowerCase().includes(searchTerm) ||
            row.min.toString().includes(searchTerm) ||
            row.max.toString().includes(searchTerm) ||
            row.moyenne.toString().includes(searchTerm)
        );
        updateTable(filteredData);
    }

    function exportToCSV() {
        const headers = ['Date', 'Capteur', 'Min', 'Max', 'Moyenne'];
        const csvContent = [
            headers.join(','),
            ...currentData.map(row => [row.jour, row.capteur, row.min, row.max, row.moyenne].join(','))
        ].join('\n');
        
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'historique_capteurs.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }

    function exportToPDF() {
        window.print();
    }

    // Event listeners
    defaultDates();
    document.getElementById('filter-form').addEventListener('submit', e => {
        e.preventDefault();
        loadHistory();
    });

    document.getElementById('search-btn').addEventListener('click', searchTable);
    document.getElementById('search-input').addEventListener('keyup', e => {
        if (e.key === 'Enter') searchTable();
    });

    document.getElementById('table-view-btn').addEventListener('click', () => {
        document.getElementById('table-view').style.display = 'block';
        document.getElementById('chart-view').style.display = 'none';
        document.getElementById('table-view-btn').classList.add('active');
        document.getElementById('chart-view-btn').classList.remove('active');
    });

    document.getElementById('chart-view-btn').addEventListener('click', () => {
        document.getElementById('table-view').style.display = 'none';
        document.getElementById('chart-view').style.display = 'block';
        document.getElementById('chart-view-btn').classList.add('active');
        document.getElementById('table-view-btn').classList.remove('active');
    });

    document.getElementById('export-csv').addEventListener('click', exportToCSV);
    document.getElementById('export-pdf').addEventListener('click', exportToPDF);

    loadHistory();
    </script>
</body>
</html>

