let currentRideId = null;
let map, directionsService, directionsRenderer;

// ✅ Toggle Sidebar Visibility
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    sidebar.classList.toggle("active");
}

// ✅ Initialize Google Map
window.initMap = function () {
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 14,
        center: { lat: 15.3632, lng: 120.9730 }, // Default location
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer();
    directionsRenderer.setMap(map);
};

// ✅ Show Available Rides
function showAvailableRides() {
    document.getElementById("ridesContainer").style.display = "block";
    loadRides();
}

// ✅ Fetch Available Rides
async function loadRides() {
    try {
        const response = await fetch("fetch_rides.php");
        const data = await response.json();
        const rideList = document.getElementById("rideList");
        rideList.innerHTML = ""; // Clear previous list

        if (data.status !== "success" || !data.rides || data.rides.length === 0) {
            rideList.innerHTML = '<tr><td colspan="6" class="text-center">No available rides.</td></tr>';
            return;
        }

        data.rides.forEach(ride => {
            let row = `
                <tr>
                    <td>${ride.user_id}</td>
                    <td>${ride.pickup}</td>
                    <td>${ride.destination}</td>
                    <td>₱${parseFloat(ride.fare).toFixed(2)}</td>
                    <td>${ride.pickup_lat}, ${ride.pickup_lng}</td>
                    <td>
                        <button class="btn btn-success" onclick="acceptRide(${ride.id}, '${ride.pickup}', '${ride.destination}')">
                            Accept
                        </button>
                    </td>
                </tr>
            `;
            rideList.innerHTML += row;
        });
    } catch (error) {
        console.error("Error loading rides:", error);
        alert("🚨 Failed to load rides. Please try again.");
    }
}

// ✅ Accept Ride
async function acceptRide(rideId, pickup, destination) {
    try {
        const response = await fetch("accept_ride.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `ride_id=${rideId}`
        });

        const data = await response.json();
        if (data.status === "success") {
            alert("✅ Ride accepted!");
            currentRideId = rideId;
            document.getElementById("ridesContainer").style.display = "none";
            document.getElementById("mapContainer").style.display = "block";
            updateRideUI(pickup, destination);
            checkForRideCancellation();
        } else {
            alert("❌ Ride acceptance failed: " + data.message);
        }
    } catch (error) {
        console.error("Error accepting ride:", error);
        alert("❌ Failed to accept ride. Please try again.");
    }
}

// ✅ Update Ride UI
function updateRideUI(pickup, destination) {
    document.getElementById("pickup-location").innerText = pickup;
    document.getElementById("dropoff-location").innerText = destination;
    document.getElementById("ride-request").style.display = "block";
}

// ✅ Start Ride (Navigation)
function startRide() {
    document.getElementById("ride-info").innerText = "🚗 Ride started. Follow the route on the map!";
    calculateRoute();
}

// ✅ Calculate Route (Google Maps)
function calculateRoute() {
    const pickup = document.getElementById("pickup-location").innerText;
    const destination = document.getElementById("dropoff-location").innerText;

    directionsService.route(
        { origin: pickup, destination: destination, travelMode: "DRIVING" },
        function (result, status) {
            if (status === "OK") {
                directionsRenderer.setDirections(result);
            } else {
                alert("Could not get directions.");
            }
        }
    );
}

// ✅ Check for Ride Cancellation
function checkForRideCancellation() {
    setInterval(async () => {
        try {
            const response = await fetch("fetch_rides.php");
            const data = await response.json();

            if (data.status === "success" && data.ride_status === "canceled") {
                alert("❌ The ride has been canceled.");
                document.getElementById("mapContainer").style.display = "none";
                document.getElementById("ridesContainer").style.display = "block";
            }
        } catch (error) {
            console.error("Error checking ride cancellation:", error);
        }
    }, 5000); // Check every 5 seconds
}
// ✅ Fetch and display the driver's ongoing ride
async function loadOngoingRide() {
    try {
        const response = await fetch("get_driver_ride.php");
        const data = await response.json();

        const rideInfo = document.getElementById("ongoingRide");
        if (data.status === "success") {
            rideInfo.innerHTML = `
                <div class="alert alert-info">
                    <strong>Ongoing Ride:</strong> ${data.pickup} ➡ ${data.destination} <br>
                    <strong>Fare:</strong> ₱${data.fare.toFixed(2)}
                    <button class="btn btn-danger btn-sm" onclick="completeRide(${data.ride_id})">Complete Ride</button>
                </div>
            `;
        } else {
            rideInfo.innerHTML = `<div class="alert alert-warning">No ongoing ride.</div>`;
        }
    } catch (error) {
        console.error("Error fetching ongoing ride:", error);
    }
}

// ✅ Complete an ongoing ride
async function completeRide(rideId) {
    try {
        const response = await fetch("complete_ride.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `ride_id=${rideId}`
        });

        const data = await response.json();
        if (data.status === "success") {
            alert("✅ Ride completed!");
            loadOngoingRide(); // Refresh ride status
            loadRides(); // Refresh available rides
        } else {
            alert("❌ Failed to complete ride: " + data.message);
        }
    } catch (error) {
        alert("❌ Error completing ride.");
    }
}

// ✅ Cancel a pending ride (driver's assigned ride)
async function cancelPendingRide(rideId) {
    try {
        const response = await fetch("cancel_ride.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `ride_id=${rideId}`
        });

        const data = await response.json();
        if (data.status === "success") {
            alert("❌ Pending ride canceled.");
            loadOngoingRide(); // Refresh ride status
            loadRides(); // Refresh available rides
        } else {
            alert("❌ Failed to cancel ride: " + data.message);
        }
    } catch (error) {
        alert("❌ Error canceling ride.");
    }
}

// ✅ Load rides on page load
window.onload = function () {
    initMap();
    showAvailableRides();
    loadOngoingRide();
};

