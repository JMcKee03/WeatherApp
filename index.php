<?php
// index.php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rain Check App - Dashboard</title>
  <!-- FontAwesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Chart.js for the hourly trends chart -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- External CSS -->
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="app-header">
    <h1>Rain Check App</h1>
    <a href="logout.php">Logout</a>
  </header>
  
  <div>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
  </div>
  
  <main>
    <section class="home-screen">
      <!-- Weather Card with current weather details -->
      <div class="weather-card">
        <div class="weather-icon">
          <i class="fas fa-cloud-sun"></i>
        </div>
        <div class="weather-details">
          <h2 id="cityName">Your City</h2>
          <p id="temperature">--°F</p>
          <p id="weatherDescription">Current weather details...</p>
        </div>
        <!-- Button to save the current location -->
        <button id="saveLocationButton">Save Location</button>
      </div>
      <!-- Search Area -->
      <div class="search-area">
        <input type="text" id="cityInput" placeholder="Enter city name" />
        <button id="searchButton">Get Weather</button>
      </div>
    </section>
    
    <!-- Saved Locations Section -->
    <section class="saved-locations">
      <h3>Saved Locations</h3>
      <ul id="savedLocationsList">
        <?php
        // Pull saved locations from the database for the logged in user
        $stmt = $pdo->prepare("SELECT location FROM saved_locations WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        while ($row = $stmt->fetch()) {
            echo "<li>" . htmlspecialchars($row['location']) . "</li>";
        }
        ?>
      </ul>
    </section>
    
    <!-- Hourly Weather Trends Section -->
    <section class="hourly-trends">
      <h3>Hourly Weather Trends</h3>
      <label for="daySelect">View Forecast:</label>
      <select id="daySelect">
        <option value="next12">Next 12 Hours</option>
        <!-- Additional day options will be added dynamically -->
      </select>
      <canvas id="hourlyChart"></canvas>
    </section>
    
    <!-- Weather Alerts Section -->
    <section class="weather-alerts">
      <h3>Weather Alerts</h3>
      <div id="alerts">
        <!-- Alerts will be displayed here -->
      </div>
    </section>
  </main>

  <script>
    // Replace with your actual Google API Key
    const GOOGLE_API_KEY = 'AIzaSyAl5I8D_lIHMIt3YLExF3JdAMugd0ayd8k';

    let hourlyChart = null;
    let forecastPeriods = [];

    async function getCoordinates(city) {
      const geocodingUrl = `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(city)}&key=${GOOGLE_API_KEY}`;
      const response = await fetch(geocodingUrl);
      if (!response.ok) throw new Error('Failed to fetch geocoding data');
      const data = await response.json();
      if (data.status !== "OK" || data.results.length === 0) throw new Error('City not found');
      const location = data.results[0].geometry.location;
      return { latitude: location.lat, longitude: location.lng };
    }

    function updateHourlyChart(labels, temps) {
      const ctx = document.getElementById('hourlyChart').getContext('2d');
      if (hourlyChart) {
        hourlyChart.data.labels = labels;
        hourlyChart.data.datasets[0].data = temps;
        hourlyChart.update();
      } else {
        hourlyChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [
              {
                label: 'Temperature (°F)',
                data: temps,
                borderColor: '#ffdd57',
                backgroundColor: 'rgba(255,221,87,0.3)',
              }
            ]
          },
          options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
          }
        });
      }
    }

    function getFilteredPeriods() {
      const selection = document.getElementById('daySelect').value;
      const now = new Date();
      if (selection === 'next12') {
        return forecastPeriods.filter(period => {
          const periodTime = new Date(period.startTime);
          return periodTime >= now && (periodTime - now) <= 12 * 60 * 60 * 1000;
        });
      } else {
        return forecastPeriods.filter(period => {
          const periodTime = new Date(period.startTime);
          const periodDate = periodTime.toISOString().split('T')[0];
          return periodDate === selection;
        });
      }
    }

    function updateChartForSelection() {
      const filteredPeriods = getFilteredPeriods();
      if (filteredPeriods.length > 0) {
        const labels = filteredPeriods.map(period => {
          const date = new Date(period.startTime);
          return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        });
        const temps = filteredPeriods.map(period => period.temperature);
        updateHourlyChart(labels, temps);
      }
    }

    function populateDaySelect(periods) {
      const daySelect = document.getElementById('daySelect');
      daySelect.innerHTML = '<option value="next12">Next 12 Hours</option>';
      const now = new Date();
      const dayMap = {};
      periods.forEach(period => {
        const periodTime = new Date(period.startTime);
        if (periodTime >= now) {
          const date = periodTime.toISOString().split('T')[0];
          if (!dayMap[date]) dayMap[date] = [];
          dayMap[date].push(period);
        }
      });
      for (const date in dayMap) {
        const options = { weekday: 'short', month: 'short', day: 'numeric' };
        const formattedDate = new Date(date).toLocaleDateString('en-US', options);
        const option = document.createElement('option');
        option.value = date;
        option.textContent = formattedDate;
        daySelect.appendChild(option);
      }
    }

    function displayAlerts(alerts) {
      const alertsContainer = document.getElementById('alerts');
      alertsContainer.innerHTML = '';
      if (alerts.length === 0) {
        alertsContainer.textContent = 'No active weather alerts.';
      } else {
        alerts.forEach(alert => {
          const alertDiv = document.createElement('div');
          alertDiv.className = 'alert';
          alertDiv.innerHTML = `<strong>${alert.properties.event}</strong> - ${alert.properties.severity}<br>${alert.properties.description.substring(0, 200)}...`;
          alertsContainer.appendChild(alertDiv);
        });
      }
    }

    async function getWeatherAlerts(latitude, longitude) {
      try {
        const alertsUrl = `https://api.weather.gov/alerts/active?point=${latitude},${longitude}`;
        const alertsResponse = await fetch(alertsUrl, {
          headers: { "User-Agent": "RainCheckApp (yeaimterry@gmail.com)" }
        });
        if (!alertsResponse.ok) throw new Error('Failed to fetch alerts');
        const alertsData = await alertsResponse.json();
        displayAlerts(alertsData.features);
      } catch (error) {
        console.error('Error fetching alerts:', error);
        document.getElementById('alerts').textContent = 'Error fetching weather alerts.';
      }
    }

    async function getWeatherForCity(city) {
      try {
        document.getElementById('temperature').textContent = 'Loading...';
        const { latitude, longitude } = await getCoordinates(city);
        const pointsUrl = `https://api.weather.gov/points/${latitude},${longitude}`;
        const pointsResponse = await fetch(pointsUrl, {
          headers: { "User-Agent": "RainCheckApp (yeaimterry@gmail.com)" }
        });
        if (!pointsResponse.ok) throw new Error('Failed to fetch point data');
        const pointsData = await pointsResponse.json();
        const forecastHourlyUrl = pointsData.properties.forecastHourly;
        const forecastResponse = await fetch(forecastHourlyUrl, {
          headers: { "User-Agent": "RainCheckApp (yeaimterry@gmail.com)" }
        });
        if (!forecastResponse.ok) throw new Error('Failed to fetch forecast data');
        const forecastData = await forecastResponse.json();
        const now = new Date();
        forecastPeriods = forecastData.properties.periods.filter(period => new Date(period.startTime) >= now);
        if (forecastPeriods.length > 0) {
          const currentPeriod = forecastPeriods[0];
          document.getElementById('cityName').textContent = city;
          document.getElementById('temperature').textContent = `${currentPeriod.temperature}°${currentPeriod.temperatureUnit}`;
          document.getElementById('weatherDescription').textContent = currentPeriod.shortForecast;
          populateDaySelect(forecastPeriods);
          updateChartForSelection();
          getWeatherAlerts(latitude, longitude);
        }
      } catch (error) {
        console.error('Error:', error);
        document.getElementById('temperature').textContent = `Error: ${error.message}`;
      }
    }

    document.getElementById('searchButton').addEventListener('click', function() {
      const city = document.getElementById('cityInput').value;
      if (city.trim() !== '') {
        getWeatherForCity(city);
      } else {
        document.getElementById('temperature').textContent = 'Please enter a city name.';
      }
    });

    document.getElementById('daySelect').addEventListener('change', updateChartForSelection);

    // Save location via AJAX to save_location.php
    document.getElementById('saveLocationButton').addEventListener('click', function() {
      const city = document.getElementById('cityName').textContent;
      if (city && city !== 'Your City') {
        fetch('save_location.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'location=' + encodeURIComponent(city)
        })
        .then(response => response.text())
        .then(data => {
          // Optionally refresh the page to show the updated saved locations list
          location.reload();
        })
        .catch(error => console.error('Error saving location:', error));
      }
    });
  </script>
</body>
</html>
