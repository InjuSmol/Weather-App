<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Weather App</title>
    <style>
       
        h1 {
            font-family: "Dancing Script", cursive;
        }
    </style>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <h1>WEATHER APP</h1>
        <div class="tab-container">
            <p class="tab current-tab" data-userWeather>Your Weather</p>
            <p class="tab" data-searchWeather>Search Weather</p>
        </div>
        <div class="weather-container">
            <div class="sub-container grant-location-container active">
                <p>Grant Location Access</p>
                <button class="btn" data-grantAccess>Grant Access</button>
            </div>
            <form class="form-container" data-searchForm>
                <input type="text" placeholder="Search for city.." data-searchInput>
                <button class="btn" type="submit">Search</button>
            </form>
            <div class="sub-container loading-container">
                <p>Loading</p>
            </div>
            <div class="sub-container user-info-container">
                <div class="name">
                    <p data-cityName></p>
                    <img data-countryIcon>
                </div>
                <p data-weatherDesc></p>
                <img data-weatherIcon>
                <p data-temp></p>
                <div class="para-container">
                    <div class="parameter">
                        <p>Wind Speed</p>
                        <p data-windspeed></p>
                    </div>
                    <div class="parameter">
                        <p>Humidity</p>
                        <p data-humidity></p>
                    </div>
                    <div class="parameter">
                        <p>Cloudiness</p>
                        <p data-cloud></p>
                    </div>
                </div>
            </div>
            <div class="sub-container noData">
                <p data-notFound></p>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const userTab = document.querySelector("[data-userWeather]");
            const searchTab = document.querySelector("[data-searchWeather]");
            const userContainer = document.querySelector(".weather-container");
            const grantAccessContainer = document.querySelector(".grant-location-container");
            const searchForm = document.querySelector("[data-searchForm]");
            const loadingScreen = document.querySelector(".loading-container");
            const userInfoContainer = document.querySelector(".user-info-container");
            const noDataAvailable = document.querySelector(".noData");

            let oldTab = userTab;

            userTab.addEventListener("click", () => switchTab(userTab));
            searchTab.addEventListener("click", () => switchTab(searchTab));

            const API_URL = "api.php";

            function switchTab(newTab) {
                if (newTab != oldTab) {
                    oldTab.classList.remove("current-tab");
                    oldTab = newTab;
                    oldTab.classList.add("current-tab");

                    if (!searchForm.classList.contains("active")) {
                        noDataAvailable.classList.remove("active");
                        userInfoContainer.classList.remove("active");
                        grantAccessContainer.classList.remove("active");
                        searchForm.classList.add("active");
                    } else {
                        noDataAvailable.classList.remove("active");
                        searchForm.classList.remove("active");
                        userInfoContainer.classList.remove("active");
                        getfromSessionStorage();
                    }
                }
            }

            function getfromSessionStorage() {
                const localCoordinates = sessionStorage.getItem("user-coordinates");
                if (!localCoordinates) {
                    grantAccessContainer.classList.add("active");
                } else {
                    const coordinates = JSON.parse(localCoordinates);
                    fetchUserWeatherInfo(coordinates);
                }
            }

            async function fetchUserWeatherInfo(coordinates) {
                const { lat, lon } = coordinates;
                grantAccessContainer.classList.remove("active");
                loadingScreen.classList.add("active");

                try {
                    const response = await fetch(`${API_URL}?lat=${lat}&lon=${lon}`);
                    const data = await response.json();
                    loadingScreen.classList.remove("active");
                    if (data.error) {
                        noDataAvailable.classList.add("active");
                        const mess = document.querySelector("[data-notFound]");
                        mess.innerText = data.error;
                    } else {
                        userInfoContainer.classList.add("active");
                        renderWeatherInfo(data);
                    }
                } catch (err) {
                    loadingScreen.classList.remove("active");
                    alert("Error fetching weather data");
                }
            }

            function renderWeatherInfo(weatherInfo) {
                const cityName = document.querySelector("[data-cityName]");
                const countryIcon = document.querySelector("[data-countryIcon]");
                const desc = document.querySelector("[data-weatherDesc]");
                const weatherIcon = document.querySelector("[data-weatherIcon]");
                const temp = document.querySelector("[data-temp]");
                const windspeed = document.querySelector("[data-windspeed]");
                const humidity = document.querySelector("[data-humidity]");
                const cloudiness = document.querySelector("[data-cloud]");

                cityName.innerText = weatherInfo.name;
                countryIcon.src = `https://flagcdn.com/144x108/${weatherInfo.sys.country.toLowerCase()}.png`;
                desc.innerText = weatherInfo.weather[0].description;
                weatherIcon.src = `http://openweathermap.org/img/w/${weatherInfo.weather[0].icon}.png`;
                temp.innerText = `${weatherInfo.main.temp} °C`;
                windspeed.innerText = `${weatherInfo.wind.speed} m/s`;
                humidity.innerText = `${weatherInfo.main.humidity}%`;
                cloudiness.innerText = `${weatherInfo.clouds.all}%`;
            }

            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showPosition);
                } else {
                    alert("Geolocation is not supported by this browser.");
                }
            }

            function showPosition(position) {
                const userCoordinates = {
                    lat: position.coords.latitude,
                    lon: position.coords.longitude,
                };
                sessionStorage.setItem("user-coordinates", JSON.stringify(userCoordinates));
                fetchUserWeatherInfo(userCoordinates);
            }

            const grantAccessButton = document.querySelector("[data-grantAccess]");
            grantAccessButton.addEventListener("click", getLocation);

            searchForm.addEventListener("submit", (e) => {
                e.preventDefault();
                const cityName = document.querySelector("[data-searchInput]").value;
                if (cityName === "") return;
                fetchSearchWeatherInfo(cityName);
            });

            async function fetchSearchWeatherInfo(city) {
                loadingScreen.classList.add("active");
                userInfoContainer.classList.remove("active");
                grantAccessContainer.classList.remove("active");

                try {
                    const response = await fetch(`${API_URL}?city=${city}`);
                    const data = await response.json();
                    loadingScreen.classList.remove("active");
                    if (data.error) {
                        noDataAvailable.classList.add("active");
                        const mess = document.querySelector("[data-notFound]");
                        mess.innerText = data.error;
                    } else {
                        noDataAvailable.classList.remove("active");
                        userInfoContainer.classList.add("active");
                        renderWeatherInfo(data);
                    }
                } catch (err) {
                    loadingScreen.classList.remove("active");
                    alert("Error fetching weather data");
                }
            }
        });
    </script>
</body>
</html>
