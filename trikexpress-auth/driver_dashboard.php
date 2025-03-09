<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "drivers") {
    header("Location: driver_login.php");
    exit();
}

$driverId = $_SESSION["user_id"];
$driverName = htmlspecialchars($_SESSION["full_name"] ?? "Driver"); // Prevent XSS
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard | TrikExpress</title>

    <!-- ✅ Bootstrap & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="driver.css">

    <!-- ✅ Google Maps API -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBEXe6peKpjp8CBQdylRhzFCvpi2S4522o&libraries=places,directions"></script>
</head>
<body>

    <!-- ✅ Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="branding"><i class="fas fa-motorcycle"></i> TrikExpress</div>
        <div class="sidebar-links">
            <a href="driver_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- ✅ Burger Menu -->
    <div class="burger-menu" onclick="toggleSidebar()">☰</div>

    <!-- ✅ Main Content -->
    <div class="content">
        <h2 class="text-center text-white">Welcome, <?php echo $driverName; ?>!</h2>

        <!-- ✅ Available Rides -->
        <div class="rides-container">
            <h3 class="text-white">Available Rides</h3>
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>Passenger</th>
                            <th>Pickup</th>
                            <th>Destination</th>
                            <th>Fare</th>
                            <th>Coordinates</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="rideList">
                        <tr>
                            <td colspan="6" class="text-center">Loading rides...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ✅ Google Maps Navigation -->
        <div class="container map-container">
            <h3 class="text-white">Navigation</h3>
            <div class="controls">
                <input id="pickup" type="text" class="form-control mb-2" placeholder="Pickup Location" readonly />
                <input id="destination" type="text" class="form-control mb-2" placeholder="Destination" readonly />
                <button class="btn btn-primary w-100" onclick="calculateRoute()">Get Directions</button>
            </div>
            <p id="pickup-coordinates" class="info text-white"></p>
            <div id="map"></div>
        </div>
    </div>

    <!-- ✅ Bootstrap & JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            loadRides();
        });

        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }

        async function loadRides() {
            try {
                const response = await fetch('fetch_rides.php');
                const data = await response.json();

                const rideList = document.getElementById("rideList");
                if (!rideList) return;

                rideList.innerHTML = "";

                if (data.length === 0) {
                    rideList.innerHTML = '<tr><td colspan="6" class="text-center">No available rides.</td></tr>';
                    return;
                }

                data.forEach(ride => {
                    let row = `
                        <tr>
                            <td>${ride.user_id}</td>
                            <td>${ride.pickup}</td>
                            <td>${ride.destination}</td>
                            <td>${ride.fare}</td>
                            <td>${ride.pickup_lat}, ${ride.pickup_lng}</td>
                            <td>
                                <button class="btn btn-success" onclick="acceptRide(${ride.id}, '${ride.pickup}', '${ride.destination}', ${ride.pickup_lat}, ${ride.pickup_lng})">
                                    Accept
                                </button>
                            </td>
                        </tr>
                    `;
                    rideList.innerHTML += row;
                });
            } catch (error) {
                console.error("Error loading rides:", error);
                alert("Failed to load rides. Please try again.");
            }
        }

        async function acceptRide(rideId, pickup, destination, pickupLat, pickupLng) {
            try {
                const response = await fetch('accept_ride.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `ride_id=${rideId}`
                });

                const data = await response.json();
                alert(data.message);

                if (data.status === "success") {
                    updateRideUI(pickup, destination, pickupLat, pickupLng);
                    calculateRoute(pickupLat, pickupLng, destination);
                }
            } catch (error) {
                console.error("Error accepting ride:", error);
                alert("Failed to accept ride. Please try again.");
            }
        }

        function updateRideUI(pickup, destination, pickupLat, pickupLng) {
            document.getElementById("pickup").value = pickup;
            document.getElementById("destination").value = destination;
            document.getElementById("pickup-coordinates").innerText = `Latitude: ${pickupLat}, Longitude: ${pickupLng}`;
        }

        function calculateRoute(pickupLat, pickupLng, destination) {
            if (!pickupLat || !pickupLng) {
                alert("Pickup coordinates not found!");
                return;
            }

            const mapElement = document.getElementById("map");
            if (!mapElement) return;

            const map = new google.maps.Map(mapElement, {
                zoom: 15,
                center: { lat: parseFloat(pickupLat), lng: parseFloat(pickupLng) }
            });

            const directionsService = new google.maps.DirectionsService();
            const directionsRenderer = new google.maps.DirectionsRenderer();
            directionsRenderer.setMap(map);

            const request = {
                origin: { lat: parseFloat(pickupLat), lng: parseFloat(pickupLng) },
                destination: destination,
                travelMode: "DRIVING"
            };

            directionsService.route(request, (result, status) => {
                if (status === "OK") {
                    directionsRenderer.setDirections(result);
                } else {
                    alert("Could not get directions.");
                }
            });
        }
    </script>

</body>
</html>
