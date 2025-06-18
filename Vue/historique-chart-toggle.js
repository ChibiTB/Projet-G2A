// Vue/historique-chart-toggle.js — version avec spanGaps pour les trous

let graphData = [];
let chartInstance = null;

function renderTable(data) {
    const tbody = document.querySelector("#history-table tbody");
    tbody.innerHTML = "";
    data.forEach(entry => {
        tbody.innerHTML += `
        <tr>
        <td>${entry.jour}</td>
        <td>${entry.capteur}</td>
        <td>${entry.min ?? '-'}</td>
        <td>${entry.max ?? '-'}</td>
        <td>${entry.moyenne ?? '-'}</td>
        </tr>`;
    });
}

function renderChart(data) {
    const chartCanvas = document.getElementById("historique-chart");

    const grouped = {};
    data.forEach(d => {
        const key = d.capteur.trim();
        if (!grouped[key]) grouped[key] = { labels: [], data: [] };
        grouped[key].labels.push(d.jour);
        grouped[key].data.push(d.moyenne !== null ? d.moyenne : null);
    });

    console.log("Groupement pour Chart.js :", grouped);

    const datasets = Object.keys(grouped).map((key, i) => ({
        label: key,
        data: grouped[key].data,
        borderWidth: 2,
        fill: false,
        tension: 0.3,
        spanGaps: true
    }));

    if (chartInstance) chartInstance.destroy();
    chartInstance = new Chart(chartCanvas, {
        type: "line",
        data: {
            labels: grouped[Object.keys(grouped)[0]].labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: "Évolution moyenne des capteurs"
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: 100
                }
            }
        }
    });
}

function applyFilters() {
    const start = document.getElementById("date-start").value;
    const end = document.getElementById("date-end").value;
    const capteurs = Array.from(document.querySelectorAll(".capteur:checked"))
        .map(c => c.value)
        .join(",");

    const url = `get_history.php?start=${start}&end=${end}&capteurs=${capteurs}`;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            graphData = data;
            const uniques = [...new Set(data.map(d => d.capteur.trim()))];
            console.log("Capteurs présents :", uniques);
            renderTable(graphData);
            if (document.getElementById("chart-view").style.display === "block") {
                renderChart(graphData);
            }
        });
}

document.addEventListener("DOMContentLoaded", () => {
    const tableView = document.getElementById("table-view");
    const chartView = document.getElementById("chart-view");
    const btnTable = document.getElementById("table-view-btn");
    const btnChart = document.getElementById("chart-view-btn");
    const form = document.getElementById("filter-form");

    btnTable.addEventListener("click", () => {
        tableView.style.display = "block";
        chartView.style.display = "none";
        btnTable.classList.add("active");
        btnChart.classList.remove("active");
    });

    btnChart.addEventListener("click", () => {
        tableView.style.display = "none";
        chartView.style.display = "block";
        btnChart.classList.add("active");
        btnTable.classList.remove("active");
        renderChart(graphData);
    });

    form.addEventListener("submit", (e) => {
        e.preventDefault();
        applyFilters();
    });

    applyFilters();
});
