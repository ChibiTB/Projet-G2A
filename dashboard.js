import { Chart } from "@/components/ui/chart"
// Initialisation des charts avec une configuration améliorée
const chartConfig = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: "index",
    intersect: false,
  },
  plugins: {
    tooltip: {
      backgroundColor: "rgba(0, 0, 0, 0.8)",
      padding: 10,
      titleFont: { size: 14, weight: "bold" },
      bodyFont: { size: 13 },
      borderColor: "rgba(255, 255, 255, 0.2)",
      borderWidth: 1,
      displayColors: true,
      callbacks: {
        label: (context) => {
          let label = context.dataset.label || ""
          if (label) {
            label += ": "
          }
          if (context.parsed.y !== null) {
            label += context.parsed.y.toFixed(1)
          }
          return label
        },
      },
    },
    legend: {
      position: "top",
      labels: {
        padding: 15,
        usePointStyle: true,
        font: { size: 12 },
      },
    },
    title: {
      display: true,
      font: { size: 18, weight: "bold" },
    },
  },
  scales: {
    x: {
      grid: {
        color: "rgba(0, 0, 0, 0.05)",
        drawBorder: false,
      },
      ticks: {
        maxRotation: 0,
        font: { size: 11 },
      },
    },
    y: {
      beginAtZero: false,
      grid: {
        color: "rgba(0, 0, 0, 0.05)",
        drawBorder: false,
      },
      ticks: {
        font: { size: 11 },
      },
    },
  },
  animations: {
    tension: {
      duration: 1000,
      easing: "linear",
    },
  },
}

// Apply the base config to all charts
const chartTempHum = new Chart(document.getElementById("graphTempHum").getContext("2d"), {
  type: "line",
  data: {
    labels: [],
    datasets: [
      {
        label: "Température (°C)",
        data: [],
        borderColor: "rgba(255, 99, 132, 1)",
        backgroundColor: "rgba(255, 99, 132, 0.1)",
        tension: 0.3,
        fill: true,
        pointRadius: 3,
        pointHoverRadius: 5,
      },
      {
        label: "Humidité (%)",
        data: [],
        borderColor: "rgba(54, 162, 235, 1)",
        backgroundColor: "rgba(54, 162, 235, 0.1)",
        tension: 0.3,
        fill: true,
        pointRadius: 3,
        pointHoverRadius: 5,
      },
    ],
  },
  options: {
    ...chartConfig,
    plugins: {
      ...chartConfig.plugins,
      title: {
        ...chartConfig.plugins.title,
        text: "Évolution de la température et de l'humidité",
      },
    },
    scales: {
      ...chartConfig.scales,
      y: {
        ...chartConfig.scales.y,
        title: { display: true, text: "Valeur" },
      },
    },
  },
})

const chartSonore = new Chart(document.getElementById("graphSonore").getContext("2d"), {
  type: "line",
  data: {
    labels: [],
    datasets: [
      {
        label: "Niveau Sonore (dB)",
        data: [],
        borderColor: "rgba(217, 2, 250, 1)",
        backgroundColor: "rgba(217, 2, 250, 0.1)",
        tension: 0.3,
        fill: true,
        pointRadius: 3,
        pointHoverRadius: 5,
      },
    ],
  },
  options: {
    ...chartConfig,
    plugins: {
      ...chartConfig.plugins,
      title: {
        ...chartConfig.plugins.title,
        text: "Évolution Niveau Sonore",
      },
    },
    scales: {
      ...chartConfig.scales,
      y: {
        ...chartConfig.scales.y,
        title: { display: true, text: "dB" },
      },
    },
  },
})

const chartDistance = new Chart(document.getElementById("graphDistance").getContext("2d"), {
  type: "line",
  data: {
    labels: [],
    datasets: [
      {
        label: "Distance (cm)",
        data: [],
        borderColor: "rgba(75, 192, 192, 1)",
        backgroundColor: "rgba(75, 192, 192, 0.1)",
        tension: 0.3,
        fill: true,
        pointRadius: 3,
        pointHoverRadius: 5,
      },
    ],
  },
  options: {
    ...chartConfig,
    plugins: {
      ...chartConfig.plugins,
      title: {
        ...chartConfig.plugins.title,
        text: "Évolution Distance",
      },
    },
    scales: {
      ...chartConfig.scales,
      y: {
        ...chartConfig.scales.y,
        title: { display: true, text: "Distance (cm)" },
      },
    },
  },
})

const chartLum = new Chart(document.getElementById("graphLuminosite").getContext("2d"), {
  type: "line",
  data: {
    labels: [],
    datasets: [
      {
        label: "Luminosité (Lux)",
        data: [],
        borderColor: "rgba(255, 206, 86, 1)",
        backgroundColor: "rgba(255, 206, 86, 0.2)",
        fill: true,
        tension: 0.3,
        pointRadius: 3,
        pointHoverRadius: 5,
      },
    ],
  },
  options: {
    ...chartConfig,
    scales: {
      ...chartConfig.scales,
      y: {
        ...chartConfig.scales.y,
        beginAtZero: false,
        title: { display: true, text: "Lux" },
      },
    },
    plugins: {
      ...chartConfig.plugins,
      title: {
        ...chartConfig.plugins.title,
        text: "Évolution Luminosité",
      },
    },
  },
})

// Add loading indicators
function showLoading(chartId) {
  const container = document.getElementById(chartId).parentElement
  const loader = document.createElement("div")
  loader.className = "chart-loader"
  loader.innerHTML = '<div class="spinner"></div><p>Chargement des données...</p>'
  container.appendChild(loader)
}

function hideLoading(chartId) {
  const container = document.getElementById(chartId).parentElement
  const loader = container.querySelector(".chart-loader")
  if (loader) container.removeChild(loader)
}

// Enhanced update functions with error handling
async function updateTempHum() {
  showLoading("graphTempHum")
  try {
    const resp = await fetch("get_measures.php")
    if (!resp.ok) throw new Error(`HTTP error! status: ${resp.status}`)

    const data = await resp.json()
    const filtered = data.filter((e) => e.temperature !== undefined && e.humidite !== undefined)

    if (filtered.length === 0) {
      console.warn("No temperature/humidity data available")
      return
    }

    chartTempHum.data.labels = filtered.map((e) => new Date(e.date).toLocaleTimeString("fr-FR", { hour12: false }))
    chartTempHum.data.datasets[0].data = filtered.map((e) => e.temperature)
    chartTempHum.data.datasets[1].data = filtered.map((e) => e.humidite)
    chartTempHum.update()
  } catch (err) {
    console.error("Erreur TempHum:", err)
    showChartError("graphTempHum", "Impossible de charger les données")
  } finally {
    hideLoading("graphTempHum")
  }
}

// Add error display function
function showChartError(chartId, message) {
  const container = document.getElementById(chartId).parentElement
  const errorDiv = document.createElement("div")
  errorDiv.className = "chart-error"
  errorDiv.innerHTML = `<p>⚠️ ${message}</p>`
  container.appendChild(errorDiv)
  setTimeout(() => {
    if (errorDiv.parentNode === container) {
      container.removeChild(errorDiv)
    }
  }, 5000)
}

async function updateSonore() {
  showLoading("graphSonore")
  try {
    const resp = await fetch("get_sonore.php")
    if (!resp.ok) throw new Error(`HTTP error! status: ${resp.status}`)
    const data = await resp.json()
    const filtered = data.filter((e) => e.sonore !== undefined)
    if (filtered.length === 0) {
      console.warn("No sonore data available")
      return
    }
    chartSonore.data.labels = filtered.map((e) => new Date(e.date).toLocaleTimeString("fr-FR", { hour12: false }))
    chartSonore.data.datasets[0].data = filtered.map((e) => e.sonore)
    chartSonore.update()
  } catch (err) {
    console.error("Erreur Sonore:", err)
    showChartError("graphSonore", "Impossible de charger les données")
  } finally {
    hideLoading("graphSonore")
  }
}

async function updateDistance() {
  showLoading("graphDistance")
  try {
    const resp = await fetch("get_distance.php")
    if (!resp.ok) throw new Error(`HTTP error! status: ${resp.status}`)
    const data = await resp.json()
    const filtered = data.filter((e) => e.distance !== undefined)
    if (filtered.length === 0) {
      console.warn("No distance data available")
      return
    }
    chartDistance.data.labels = filtered.map((e) => new Date(e.date).toLocaleTimeString("fr-FR", { hour12: false }))
    chartDistance.data.datasets[0].data = filtered.map((e) => e.distance)
    chartDistance.update()
  } catch (err) {
    console.error("Erreur Distance:", err)
    showChartError("graphDistance", "Impossible de charger les données")
  } finally {
    hideLoading("graphDistance")
  }
}

async function updateLuminosite() {
  showLoading("graphLuminosite")
  try {
    const resp = await fetch("get_luminosite.php")
    if (!resp.ok) throw new Error(`HTTP error! status: ${resp.status}`)
    const data = await resp.json()
    const filtered = data.filter((e) => e.valeur_mesure !== undefined)
    if (filtered.length === 0) {
      console.warn("No luminosite data available")
      return
    }
    chartLum.data.labels = filtered.map((e) => new Date(e.date_mesure).toLocaleTimeString("fr-FR", { hour12: false }))
    chartLum.data.datasets[0].data = filtered.map((e) => e.valeur_mesure)
    chartLum.update()
  } catch (err) {
    console.error("Erreur Luminosité:", err)
    showChartError("graphLuminosite", "Impossible de charger les données")
  } finally {
    hideLoading("graphLuminosite")
  }
}

async function updateAll() {
  await Promise.all([updateTempHum(), updateSonore(), updateDistance(), updateLuminosite()])
}

updateAll()
setInterval(updateAll, 500)
