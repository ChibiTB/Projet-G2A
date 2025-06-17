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
        <h2>Historique des capteurs (7 derniers jours)</h2>
        <table id="history-table" class="historique-table">
        <thead>
            <tr>
            <th>Date</th>a
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

    async function loadHistory() {
        try {
        const res = await fetch('get_history.php');
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
    loadHistory();
    </script>
</body>
</html>