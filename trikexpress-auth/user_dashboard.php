<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "user") {
    header("Location: index.html");
    exit();
}

$userId = $_SESSION["user_id"];
$fullName = isset($_SESSION["full_name"]) ? htmlspecialchars($_SESSION["full_name"]) : "User";
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
    <link rel="stylesheet" href="dashboard.css?v=<?php echo time(); ?>"> <!-- Force latest CSS -->

    <!-- ✅ Google Maps API -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBEXe6peKpjp8CBQdylRhzFCvpi2S4522o&callback=initMap&libraries=places"></script>
</head>
<body>
<div class="wrapper">
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

    <!-- ✅ Main Content Wrapper -->
    <div class="content-wrapper">
    <div class="main-content">
        <h2>Welcome, <?php echo isset($fullName) ? htmlspecialchars($fullName) : 'User'; ?>!</h2>

        <div class="ride-booking">
            <h3>Book a Ride</h3>

            <!-- ✅ Buttons for Map & Clearing Pins -->
            <button class="btn btn-info w-100" onclick="toggleMap()">📍 Open Map to Pin Locations</button>
            <button class="btn btn-danger w-100 mt-2" onclick="clearPins()">Clear Pins</button>

            <!-- ✅ Pickup Location Input -->
            <div class="input-group mb-2">
                <input id="pickup" type="text" class="form-control" placeholder="Enter pickup location" required>
                <button class="btn btn-secondary" onclick="useCurrentLocation()">📍 Use My Location</button>
            </div>

            <!-- ✅ Drop-off Location Input -->
            <div class="input-group mb-3">
                <input id="destination" type="text" class="form-control" placeholder="Enter drop-off location" required>
            </div>

            <!-- ✅ Calculate Fare Button -->
            <button class="btn btn-primary w-100" onclick="calculateDistance()">Calculate Fare</button>

            <!-- ✅ Distance & Fare Display -->
            <div id="rideDetails" class="ride-details mt-3" style="display: none;">
                <p><strong>Distance:</strong> <span id="distance"></span></p>
                <p><strong>Fare:</strong> ₱<span id="fare"></span></p>
                <button class="btn btn-success w-100" onclick="placeRide()">Place Ride</button>
            </div>

            <!-- ✅ Ride Status -->
            <div id="ride-status-bar" class="alert alert-info mt-3" style="display: none;">
                <p id="ride-status">Pending...</p>
                <button id="cancel-ride-btn" class="btn btn-warning w-100" onclick="cancelRide()">Cancel Ride</button>
            </div>
        </div>

        <!-- ✅ Pending Ride Section -->
        <div class="pending-ride-section">
            <h3>Pending Ride Request</h3>
            <p id="pending-ride-message" style="display: none;">No pending ride requests.</p>

            <table id="pending-ride-table" class="table table-striped" style="display: none;">
                <thead>
                    <tr>
                        <th>Pickup</th>
                        <th>Destination</th>
                        <th>Fare</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="pending-rides-body">
                    <!-- ✅ Dynamic ride requests will be inserted here -->
                </tbody>
            </table>
        </div>

        <!-- 🌍 Google Map (Initially Hidden) -->
        <div id="mapContainer" style="display: none;">
            <h3>Pin Your Pickup & Drop-off Locations</h3>
            <div id="map"></div>
        </div>
    </div>
</div>


<!-- ✅ JavaScript -->
<script>
// ✅ Global Variables
let map, pickupMarker, dropoffMarker, directionsService, directionsRenderer;
let pickupLat, pickupLng, dropoffLat, dropoffLng, distanceKm, fareAmount = 0;
let pinningPickup = true; // Tracks whether the user is pinning pickup or drop-off
let rideId = null; // Stores the ride ID for cancellations

// ✅ Toggle Sidebar
function toggleSidebar() {
    document.querySelector(".sidebar").classList.toggle("active");
    document.querySelector(".content-wrapper").classList.toggle("active");
}

// ✅ Toggle Map Visibility
function toggleMap() {
    let mapContainer = document.getElementById("mapContainer");
    mapContainer.style.display = mapContainer.style.display === "none" ? "block" : "none";
}

// ✅ Initialize Google Map
// ✅ Initialize Google Map (Fixed)
function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 15,
        center: { lat: 15.3632, lng: 120.9730 } // Default location
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer();
    directionsRenderer.setMap(map);

    new google.maps.places.Autocomplete(document.getElementById("pickup"));
    new google.maps.places.Autocomplete(document.getElementById("destination"));

    map.addListener("click", function (event) {
        if (pinningPickup) {
            setPickupLocation(event.latLng);
        } else {
            setDropoffLocation(event.latLng);
        }
    });
}

// ✅ Ensure it's globally accessible
window.initMap = initMap;

// ✅ Convert LatLng to Address
function getAddressFromLatLng(lat, lng, callback) {
    const geocoder = new google.maps.Geocoder();
    const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };

    geocoder.geocode({ location: latlng }, function (results, status) {
        if (status === "OK" && results[0]) {
            callback(results[0].formatted_address);
        } else {
            console.error("Geocoder failed: " + status);
            callback("Unknown Location");
        }
    });
}

// ✅ Set Pickup Location
function setPickupLocation(latLng) {
    if (pickupMarker) pickupMarker.setMap(null);

    pickupMarker = new google.maps.Marker({
        position: latLng,
        map: map,
        label: "P"
    });

    pickupLat = latLng.lat();
    pickupLng = latLng.lng();

    getAddressFromLatLng(pickupLat, pickupLng, function (address) {
        document.getElementById("pickup").value = address;
    });

    pinningPickup = false; // Now user can pin drop-off
}

// ✅ Set Drop-off Location
function setDropoffLocation(latLng) {
    if (dropoffMarker) dropoffMarker.setMap(null);

    dropoffMarker = new google.maps.Marker({
        position: latLng,
        map: map,
        label: "D"
    });

    dropoffLat = latLng.lat();
    dropoffLng = latLng.lng();

    getAddressFromLatLng(dropoffLat, dropoffLng, function (address) {
        document.getElementById("destination").value = address;
    });

    pinningPickup = true; // Resets to allow re-pinning pickup if needed
}

// 📍 Clear Pinned Locations
function clearPins() {
    if (pickupMarker) pickupMarker.setMap(null);
    if (dropoffMarker) dropoffMarker.setMap(null);
    
    document.getElementById("pickup").value = "";
    document.getElementById("destination").value = "";

    pickupLat = pickupLng = dropoffLat = dropoffLng = null;
    showNotification("📍 Pins Cleared!", "info");
}

// 📌 Use Current Location for Pickup
function useCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            let latLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            setPickupLocation(latLng);
        }, () => {
            showNotification("❌ Unable to get location!", "error");
        });
    } else {
        showNotification("❌ Geolocation not supported!", "error");
    }
}

// 🚗 Calculate Distance & Fare
function calculateDistance() {
    if (!pickupLat || !dropoffLat) {
        showNotification("❌ Please pin both pickup and drop-off locations!", "error");
        return;
    }

    const service = new google.maps.DistanceMatrixService();
    const origin = new google.maps.LatLng(pickupLat, pickupLng);
    const destination = new google.maps.LatLng(dropoffLat, dropoffLng);

    service.getDistanceMatrix(
        {
            origins: [origin],
            destinations: [destination],
            travelMode: "DRIVING",
        },
        function (response, status) {
            if (status !== "OK") {
                showNotification("❌ Error calculating distance!", "error");
                return;
            }

            const result = response.rows[0].elements[0];
            if (!result || !result.distance) {
                showNotification("❌ Could not retrieve distance!", "error");
                return;
            }

            distanceKm = result.distance.value / 1000;
            fareAmount = distanceKm * 15; // Fare rate: ₱15 per KM

            document.getElementById("distance").innerText = distanceKm.toFixed(2) + " km";
            document.getElementById("fare").innerText = fareAmount.toFixed(2);
            document.getElementById("rideDetails").style.display = "block";
        }
    );
}


let rideIds = []; // ✅ Store multiple ride requests

// ✅ Place a Ride Request
function placeRide() {
    if (!pickupLat || !dropoffLat) {
        showNotification("❌ Please pin both pickup and drop-off locations!", "error");
        return;
    }

    let pickup = document.getElementById("pickup").value.trim();
    let destination = document.getElementById("destination").value.trim();
    
    if (!pickup || !destination) {
        showNotification("❌ Pickup and destination cannot be empty!", "error");
        return;
    }

    if (typeof fareAmount === "undefined" || fareAmount <= 0) {
        showNotification("❌ Invalid fare amount!", "error");
        return;
    }

    fetch("request_ride.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `pickup=${encodeURIComponent(pickup)}&pickup_lat=${pickupLat}&pickup_lng=${pickupLng}&destination=${encodeURIComponent(destination)}&dropoff_lat=${dropoffLat}&dropoff_lng=${dropoffLng}&fare=${fareAmount}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            rideId = data.ride_id;
            document.getElementById("ride-status").innerText = "🚕 Ride Requested";
            document.getElementById("ride-status-bar").style.display = "block";
            showNotification("🚀 Ride placed successfully!", "success");
        } else {
            // Show error if the user already has a pending ride
            if (data.message === "You already have a pending ride request.") {
                showNotification("❌ " + data.message, "error");
            } else {
                showNotification("❌ Ride request failed: " + data.message, "error");
            }
        }
    })
    .catch(error => {
        console.error("🔥 Error placing ride:", error);
        showNotification("❌ Error requesting ride! Please try again.", "error");
    });
}


// ✅ Fetch Pending Rides
function fetchPendingRides() {
    fetch("fetch_rides.php")
        .then(response => response.json())
        .then(data => {
            console.log("Fetched Ride Data:", data); // Debugging

            let tableBody = document.getElementById("pending-rides-body");
            tableBody.innerHTML = ""; // Clear previous rows

            if (data) {
                document.getElementById("pending-ride-message").style.display = "none";
                document.getElementById("pending-ride-table").style.display = "table";

                // data.forEach(ride => {
                    let row = document.createElement("tr");

                    row.innerHTML = `
                        <td>${data.pickup || "N/A"}</td>
                        <td>${data.destination || "N/A"}</td>
                        <td>₱${(parseFloat(data.fare) || 0).toFixed(2)}</td>
                        <td>
                            <button onclick="cancelRide(${data.ride_id})" class="cancel-ride-btn">Cancel</button>
                        </td>
                    `;

                    tableBody.appendChild(row);
                // });

                document.getElementById("ride-status-bar").style.display = "block";
                document.getElementById("ride-status").innerText = "🚕 You have pending rides";
            } else {
                console.log("No pending rides found!");
                document.getElementById("pending-ride-table").style.display = "none";
                document.getElementById("pending-ride-message").style.display = "block";
                document.getElementById("ride-status-bar").style.display = "none";
            }
        })
        .catch(error => {
            console.error("🔥 Error fetching pending rides:", error);
            document.getElementById("pending-ride-message").innerText = "Error loading rides.";
            document.getElementById("pending-ride-message").style.display = "block";
        });
}

// ✅ Cancel a Ride
function cancelRide(rideIdToCancel) {
    if (rideIds.length === 0) {
        showNotification("❌ No active ride to cancel!", "error");
        return;
    }

    let rideIndex = rideIds.indexOf(rideIdToCancel);
    if (rideIndex === -1) {
        showNotification("❌ Ride ID not found!", "error");
        return;
    }

    fetch("cancel_ride.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `ride_id=${rideIdToCancel}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            showNotification("🚫 Ride canceled successfully!", "info");
            rideIds.splice(rideIndex, 1); // ✅ Remove ride from the array
            fetchPendingRides(); // ✅ Refresh ride list
        } else {
            showNotification("❌ Ride cancellation failed!", "error");
        }
    })
    .catch(error => {
        showNotification("❌ Error canceling ride!", "error");
    });
}

// ✅ Load Pending Rides on Page Load
window.onload = function () {
    initMap();
    fetchPendingRides();
};




// ✅ Polling for Ride Status Updates
setInterval(() => {
    fetch("fetch_rides.php")
    .then(response => response.json())
    .then(data => {
        if (data.status === "accepted") {
            document.getElementById("ride-status").innerText = "✅ Ride Accepted";
        } else if (data.status === "canceled") {
            document.getElementById("ride-status-bar").style.display = "none";
            rideId = null;
            showNotification("🚫 Your ride was canceled!", "warning");
        }
    })
    .catch(error => console.error("🔥 Error fetching ride status:", error));
}, 5000);

// ✅ Show Notifications
function showNotification(message, type) {
    let bgColor = type === "success" ? "green" : type === "error" ? "red" : "blue";
    let notification = document.createElement("div");
    notification.innerText = message;
    notification.style.backgroundColor = bgColor;
    notification.classList.add("notification");
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}



    </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
