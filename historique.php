<?php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des capteurs</title>
    <link rel="stylesheet" href="Vue/style.css">
    <link rel="stylesheet" href="Vue/historique.css">
</head>
<body>
    <div id="header-placeholder"></div>

    <main class="historique" id="main-content">
    <div class="container">
        <h2>Historique des capteurs</h2>

        <form id="filter-form" class="filters">
        <label>Du <input type="date" id="date-start"></label>
        <label>au <input type="date" id="date-end"></label>
        <div class="capteurs">
            <label><input type="checkbox" class="capteur" value="1" checked> Température</label>
            <label><input type="checkbox" class="capteur" value="2" checked> Humidité</label>
            <label><input type="checkbox" class="capteur" value="3" checked> Luminosité</label>
            <label><input type="checkbox" class="capteur" value="4" checked> Distance</label>
            <label><input type="checkbox" class="capteur" value="5" checked> Sonore</label>
        </div>
        <button type="submit">Appliquer</button>
        </form>

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
    </main>

    <div id="footer-placeholder"></div>

    <script>
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
        const tbody = document.querySelector('#history-table tbody');
        tbody.innerHTML = '';
        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${row.jour}</td><td>${row.capteur}</td><td>${row.min}</td><td>${row.max}</td><td>${row.moyenne}</td>`;
            tbody.appendChild(tr);
        });
        } catch(err) {
        console.error('Erreur chargement historique', err);
        }
    }
    defaultDates();
    document.getElementById('filter-form').addEventListener('submit', e => {
        e.preventDefault();
        loadHistory();
    });
    loadHistory();
    </script>
</body>
</html>