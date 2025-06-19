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

        <!-- Enhanced search functionality -->
        <div class="search-section">
            <div class="search-container">
                <input type="text" id="search-input" placeholder="Rechercher par capteur, date, ou valeur..." />
                <button id="search-btn" type="button">🔍 Rechercher</button>
                <button id="clear-search-btn" type="button">✖️ Effacer</button>
            </div>
            <div class="search-results-info" id="search-results-info" style="display: none;">
                <span id="results-count">0</span> résultat(s) trouvé(s)
            </div>
        </div>

        <!-- View toggle buttons -->
        <div class="view-toggle">
            <button id="table-view-btn" class="active">📊 Vue Tableau</button>
            <button id="chart-view-btn">📈 Vue Graphique</button>
        </div>

        <!-- Table view -->
        <div id="table-view" class="view-container">
            <div class="table-container">
                <table id="history-table" class="historique-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Capteur</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Moyenne</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        
        <!-- Chart view -->
        <div id="chart-view" class="view-container" style="display: none;">
            <div class="chart-container">
                <canvas id="history-chart"></canvas>
            </div>
            <div class="chart-legend" id="chart-legend"></div>
        </div>

        <!-- Export functionality -->
        <div class="export-section">
            <button id="export-csv">📄 Exporter CSV</button>
            <button id="export-pdf">📋 Exporter PDF</button>
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
                <div class="stat-card">
                    <h4>Données manquantes</h4>
                    <span id="missing-data">0</span>
                </div>
            </div>
        </div>
    </div>
    </main>

    <div id="footer-placeholder"></div>

    <script>
    let currentData = [];
    let filteredData = [];
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
        // Show loading state
        const tbody = document.querySelector('#history-table tbody');
        tbody.innerHTML = '<tr><td colspan="6" class="loading">⏳ Chargement des données...</td></tr>';
        
        const params = new URLSearchParams();
        const dStart = document.getElementById('date-start').value;
        const dEnd = document.getElementById('date-end').value;
        if (dStart) params.set('start', dStart);
        if (dEnd) params.set('end', dEnd);
        const caps = Array.from(document.querySelectorAll('.capteur:checked')).map(c => c.value);
        if (caps.length) params.set('capteurs', caps.join(','));
        
        const res = await fetch('get_history.php?' + params.toString());
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        const response = await res.json();
        
        if (response.error) {
            throw new Error(response.error + ': ' + response.message);
        }
        
        currentData = response.data || response; // Handle both new and old format
        filteredData = [...currentData]; // Initialize filtered data
        
        updateTable(filteredData);
        updateChart(filteredData);
        updateStats(filteredData, response.metadata);
        
    } catch(err) {
        console.error('Erreur chargement historique', err);
        const tbody = document.querySelector('#history-table tbody');
        tbody.innerHTML = `<tr><td colspan="6" class="error">❌ Erreur: ${err.message}</td></tr>`;
    }
}

    function updateTable(data) {
        const tbody = document.querySelector('#history-table tbody');
        tbody.innerHTML = '';
        
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="no-data">Aucune donnée disponible</td></tr>';
            return;
        }
        
        data.forEach(row => {
            const tr = document.createElement('tr');
            
            // Determine status based on data availability
            let status = '';
            let statusClass = '';
            if (row.min === null || row.max === null || row.moyenne === null) {
                status = '❌ Pas de données';
                statusClass = 'status-no-data';
            } else {
                status = '✅ Données OK';
                statusClass = 'status-ok';
            }
            
            tr.innerHTML = `
                <td>${row.jour}</td>
                <td>${row.capteur}</td>
                <td>${row.min !== null ? row.min : 'N/A'}</td>
                <td>${row.max !== null ? row.max : 'N/A'}</td>
                <td>${row.moyenne !== null ? row.moyenne : 'N/A'}</td>
                <td class="${statusClass}">${status}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function updateChart(data) {
    const ctx = document.getElementById('history-chart').getContext('2d');
    
    if (chart) {
        chart.destroy();
    }

    // Filter out null values for chart display
    const validData = data.filter(row => row.moyenne !== null);
    
    if (validData.length === 0) {
        ctx.fillText('Aucune donnée à afficher', 10, 50);
        return;
    }

    // Group data by sensor and date
    const sensorData = {};
    const allDates = [...new Set(validData.map(row => row.jour))].sort();
    
    validData.forEach(row => {
        if (!sensorData[row.capteur]) {
            sensorData[row.capteur] = {};
        }
        sensorData[row.capteur][row.jour] = parseFloat(row.moyenne);
    });

    const datasets = Object.keys(sensorData).map(sensor => {
        const sensorValues = allDates.map(date => sensorData[sensor][date] || null);
        
        return {
            label: sensor,
            data: sensorValues,
            borderColor: getColorForSensor(sensor),
            backgroundColor: getColorForSensor(sensor) + '20',
            borderWidth: 2,
            fill: false,
            tension: 0.1,
            spanGaps: true,
            pointRadius: 4,
            pointHoverRadius: 6
        };
    });

    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: allDates,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Évolution des mesures des capteurs',
                    font: {
                        size: 16,
                        weight: 'bold'
                    }
                },
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.y;
                            if (value === null) return context.dataset.label + ': Pas de données';
                            return context.dataset.label + ': ' + value.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Valeur'
                    },
                    beginAtZero: true
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

    function getColorForSensor(sensor) {
        const colors = {
            'Température': '#e74c3c',
            'Humidité': '#3498db',
            'Luminosité': '#f39c12',
            'Distance': '#2ecc71',
            'Sonore': '#9b59b6'
        };
        return colors[sensor] || '#95a5a6';
    }

    function updateStats(data, metadata) {
    const totalRecords = data.length;
    const validRecords = data.filter(row => row.moyenne !== null).length;
    const missingRecords = totalRecords - validRecords;
    
    document.getElementById('total-records').textContent = validRecords;
    document.getElementById('missing-data').textContent = missingRecords;
    
    const activeSensors = new Set(data.filter(row => row.moyenne !== null).map(row => row.capteur)).size;
    document.getElementById('active-sensors').textContent = activeSensors;
    
    if (metadata) {
        const startDate = new Date(metadata.start_date);
        const endDate = new Date(metadata.end_date);
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        document.getElementById('period-analyzed').textContent = `${diffDays} jour(s)`;
    } else if (data.length > 0) {
        const dates = data.map(row => new Date(row.jour));
        const minDate = new Date(Math.min(...dates));
        const maxDate = new Date(Math.max(...dates));
        const diffTime = Math.abs(maxDate - minDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        document.getElementById('period-analyzed').textContent = `${diffDays} jour(s)`;
    }
}

    // Enhanced search functionality
    function searchTable() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase().trim();
        const resultsInfo = document.getElementById('search-results-info');
        const resultsCount = document.getElementById('results-count');
        
        if (searchTerm === '') {
            filteredData = [...currentData];
            resultsInfo.style.display = 'none';
        } else {
            filteredData = currentData.filter(row => 
                row.jour.toLowerCase().includes(searchTerm) ||
                row.capteur.toLowerCase().includes(searchTerm) ||
                (row.min !== null && row.min.toString().includes(searchTerm)) ||
                (row.max !== null && row.max.toString().includes(searchTerm)) ||
                (row.moyenne !== null && row.moyenne.toString().includes(searchTerm))
            );
            
            resultsCount.textContent = filteredData.length;
            resultsInfo.style.display = 'block';
        }
        
        updateTable(filteredData);
        updateChart(filteredData);
    }
    
    function clearSearch() {
        document.getElementById('search-input').value = '';
        filteredData = [...currentData];
        document.getElementById('search-results-info').style.display = 'none';
        updateTable(filteredData);
        updateChart(filteredData);
    }

    function exportToCSV() {
        const headers = ['Date', 'Capteur', 'Min', 'Max', 'Moyenne', 'Statut'];
        const csvContent = [
            headers.join(','),
            ...filteredData.map(row => {
                const status = (row.min === null || row.max === null || row.moyenne === null) ? 'Pas de données' : 'Données OK';
                return [
                    row.jour, 
                    row.capteur, 
                    row.min || 'N/A', 
                    row.max || 'N/A', 
                    row.moyenne || 'N/A',
                    status
                ].join(',');
            })
        ].join('\n');
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `historique_capteurs_${new Date().toISOString().split('T')[0]}.csv`;
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
    document.getElementById('clear-search-btn').addEventListener('click', clearSearch);
    document.getElementById('search-input').addEventListener('keyup', e => {
        if (e.key === 'Enter') {
            searchTable();
        }
    });

    // Real-time search as user types (debounced)
    let searchTimeout;
    document.getElementById('search-input').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(searchTable, 300); // Wait 300ms after user stops typing
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
