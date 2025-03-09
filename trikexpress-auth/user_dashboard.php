<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "users") {
    header("Location: index.html");
    exit();
}

$userId = $_SESSION["user_id"];
$fullName = htmlspecialchars($_SESSION["full_name"]); // Prevent XSS
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | TrikExpress</title>
    
    <!-- ✅ Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">

    <!-- ✅ Google Maps API -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBEXe6peKpjp8CBQdylRhzFCvpi2S4522o&libraries=places,directions"></script>
</head>
<body>

    <!-- ✅ Sidebar -->
    <div class="sidebar">
        <div class="branding"><i class="fas fa-motorcycle"></i> TrikExpress</div>
        <div class="burger-menu" onclick="toggleSidebar()">☰</div>
        <ul class="nav flex-column sidebar-links">
            <li><a href="user_dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php" class="nav-link logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- ✅ Main Content -->
    <div class="content">
        <h2>Welcome, <?php echo $fullName; ?>!</h2>

        <div class="ride-booking">
            <h3>Book a Ride</h3>
            <div class="input-group mb-2">
                <input id="pickup" type="text" class="form-control" placeholder="Enter pickup location" required>
                <button class="btn btn-secondary" onclick="useCurrentLocation()">📍 Use My Location</button>
            </div>
            <div class="input-group mb-3">
                <input id="destination" type="text" class="form-control" placeholder="Enter drop-off location" required>
            </div>
            <button class="btn btn-primary w-100" onclick="bookRide()">🚖 Request Ride</button>
        </div>

        <!-- 🌍 Google Map -->
        <div id="map"></div>
    </div>

    <!-- ✅ JavaScript -->
    <script>
        function toggleSidebar() {
            document.querySelector(".sidebar").classList.toggle("active");
        }

        function initMap() {
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 14,
                center: { lat: 14.5995, lng: 120.9842 } // Default center: Manila
            });

            new google.maps.places.Autocomplete(document.getElementById("pickup"));
            new google.maps.places.Autocomplete(document.getElementById("destination"));
        }

        window.onload = initMap;

        function useCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const geocoder = new google.maps.Geocoder();
                    const latLng = new google.maps.LatLng(lat, lng);

                    geocoder.geocode({ location: latLng }, (results, status) => {
                        if (status === "OK" && results[0]) {
                            document.getElementById("pickup").value = results[0].formatted_address;
                        } else {
                            alert("Unable to get location address.");
                        }
                    });
                }, () => {
                    alert("Location access denied.");
                });
            } else {
                alert("Geolocation is not supported by your browser.");
            }
        }

        function bookRide() {
            const pickup = document.getElementById("pickup").value.trim();
            const destination = document.getElementById("destination").value.trim();

            if (!pickup || !destination) {
                alert("Please enter both pickup and destination.");
                return;
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    const pickupLat = position.coords.latitude;
                    const pickupLng = position.coords.longitude;

                    fetch('request_ride.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `pickup=${encodeURIComponent(pickup)}&pickup_lat=${pickupLat}&pickup_lng=${pickupLng}&destination=${encodeURIComponent(destination)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                    })
                    .catch(error => {
                        console.error("Error booking ride:", error);
                        alert("Failed to send ride request.");
                    });
                }, () => {
                    alert("Location access denied. Cannot get coordinates.");
                });
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }
    </script>

</body>
</html>
