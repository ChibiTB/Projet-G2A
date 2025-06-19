// Initialisation des charts
const ctx = document.getElementById('graphTempHum').getContext('2d');

const chart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: [],
    datasets: [{
      label: 'Humidité (%)',
      data: [],
      borderColor: 'rgba(55, 20, 255, 1)',
      backgroundColor: 'rgba(55, 20, 255, 0.2)',
      fill: true,
      tension: 0.3
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        suggestedMin: 0,
        suggestedMax: 100,
        title: {
          display: true,
          text: 'Humidité (%)'
        }
      },
      x: {
        title: {
          display: true,
          text: 'Heure'
        }
      }
    },
    plugins: {
      title: {
        display: true,
        text: 'Évolution de l’humidité',
        font: {
          size: 20
        }
      }
    }
  }
});

const chartSonore = new Chart(document.getElementById('graphSonore').getContext('2d'), {
  type: 'line',
  data: {
    labels: [],
    datasets: [{
      label: 'Niveau Sonore (dB)',
      data: [],
      borderColor: 'rgba(217, 2, 250, 1)',
      backgroundColor: 'rgba(217, 2, 250, 0.1)',
      tension: 0.3,
      fill: true
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: false, title: { display: true, text: 'dB' } },
      x: { title: { display: true, text: 'Heure' } }
    },
    plugins: {
      legend: { position: 'top' },
      title: {
        display: true,
        text: 'Évolution Niveau Sonore',
        font: { size: 20 }
      }
    }
  }
});

const chartDistance = new Chart(document.getElementById('graphDistance').getContext('2d'), {
  type: 'line',
  data: {
    labels: [],
    datasets: [{
      label: 'Distance (m)',
      data: [],
      borderColor: 'rgba(75, 192, 192, 1)',
      backgroundColor: 'rgba(75, 192, 192, 0.1)',
      tension: 0.3,
      fill: true
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: false, title: { display: true, text: 'Distance (m)' } },
      x: { title: { display: true, text: 'Heure' } }
    },
    plugins: {
      legend: { position: 'top' },
      title: {
        display: true,
        text: 'Évolution Distance',
        font: { size: 20 }
      }
    }
  }
});

const chartLum = new Chart(document.getElementById('graphLuminosite').getContext('2d'), {
  type: 'line',
  data: {
    labels: [],
    datasets: [{
      label: 'Luminosité (Lux)',
      data: [],
      borderColor: 'rgba(255, 206, 86, 1)',
      backgroundColor: 'rgba(255, 206, 86, 0.2)',
      fill: true,
      tension: 0.3
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { suggestedMin: 0, suggestedMax: 1000, title: { display: true, text: 'Lux' } },
      x: { title: { display: true, text: 'Heure' } }
    },
    plugins: {
      title: {
        display: true,
        text: 'Évolution Luminosité',
        font: { size: 20 }
      }
    }
  }
});

// Fonction de mise à jour pour l'humidité
async function updateGraph() {
  try {
    const response = await fetch('get_measures.php');
    const data = await response.json();

    const labels = data.map(point =>
      new Date(point.date_mesure).toLocaleTimeString('fr-FR', { hour12: false })
    );
    const valeurs = data.map(point => point.valeur_mesure);

    chart.data.labels = labels;
    chart.data.datasets[0].data = valeurs;
    chart.update();
  } catch (err) {
    console.error('Erreur Humidité :', err);
  }
}

// Autres fonctions de mise à jour
async function updateSonore() {
  try {
    const resp = await fetch('get_sonore.php');
    const data = await resp.json();
    const filtered = data.filter(e => e.sonore !== undefined);
    chartSonore.data.labels = filtered.map(e => new Date(e.date).toLocaleTimeString('fr-FR', { hour12: false }));
    chartSonore.data.datasets[0].data = filtered.map(e => e.sonore);
    chartSonore.update();
  } catch (err) {
    console.error('Erreur Sonore:', err);
  }
}

async function updateDistance() {
  try {
    const resp = await fetch('get_distance.php');
    const data = await resp.json();
    const filtered = data.filter(e => e.distance !== undefined);
    chartDistance.data.labels = filtered.map(e => new Date(e.date).toLocaleTimeString('fr-FR', { hour12: false }));
    chartDistance.data.datasets[0].data = filtered.map(e => e.distance);
    chartDistance.update();
  } catch (err) {
    console.error('Erreur Distance:', err);
  }
}

async function updateLuminosite() {
  try {
    const resp = await fetch('get_luminosite.php');
    const data = await resp.json();
    const filtered = data.filter(e => e.valeur_mesure !== undefined);
    chartLum.data.labels = filtered.map(e => new Date(e.date_mesure).toLocaleTimeString('fr-FR', { hour12: false }));
    chartLum.data.datasets[0].data = filtered.map(e => e.valeur_mesure);
    chartLum.update();
  } catch (err) {
    console.error('Erreur Luminosité:', err);
  }
}

// Lancement global
async function updateAll() {
  await Promise.all([
    updateGraph(),        
    updateSonore(),
    updateDistance(),
    updateLuminosite()
  ]);
}

updateAll();
setInterval(updateAll, 500);
